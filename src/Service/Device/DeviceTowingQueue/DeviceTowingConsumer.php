<?php

namespace App\Service\Device\DeviceTowingQueue;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\Notification\ScopeService;
use App\Service\Redis\MemoryDbService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Psr\Log\LoggerInterface;

class DeviceTowingConsumer implements ConsumerInterface
{
    use TrackerHistoryConsumerTrait;
    use CommandLoggerTrait;

    private $notificationDispatcher;
    private $logger;
    private $slaveEntityManager;
    private ScopeService $scopeService;
    private MemoryDbService $memoryDb;
    private ?Event $event;

    private const TAG_VEHICLE_TOWING = 'vehicleTowing';
    private const MAX_TTL = 86400;
    private const IGNORE_STOPS = 120;

    public const QUEUES_NUMBER = 2; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'towing_device_'; // should be equal to `routing_keys` of queues

    protected array $cacheData = [];
    protected string $redisKey;

    public function __construct(
        private readonly EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        ScopeService $scopeService,
        MemoryDbService $memoryDb
    ) {
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->scopeService = $scopeService;
        $this->memoryDb = $memoryDb;
        $this->event = $this->em->getRepository(Event::class)
            ->getEventByName(Event::VEHICLE_TOWING_EVENT);
    }

    public function execute(AMQPMessage $msg): void
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }

            $deviceId = $message->device_id;
            $trackerHistoryId = $message->trackerHistoryData?->id;
            $trackerData = property_exists($message, 'trackerHistoryData')
                ? $this->formatTHObjectsArray([$message->trackerHistoryData])[0]
                : [];

            $device = $this->em->getRepository(Device::class)->find($deviceId);
            /** @var Vehicle|null $vehicle */
            $vehicle = $device && $device->isVehicle() ? $device->getVehicle() : null;

            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);

            if (!$device || !$device->getTeam() || !$device->getVehicle()
//                || DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > 2
//                || !$this->isValidToTriggerEvent($trackerData['ts'], $trackerData['createdAt'])
            ) {
                $this->em->clear();
                return;
            }

            /** @var Event $event */
            $event = $this->event ?? $this->em->getRepository(Event::class)
                ->getEventByName(Event::VEHICLE_TOWING_EVENT);
            if (!$event) {
                return;
            }

            $notifications = $this->em->getRepository(Notification::class)
                ->getNotificationsByListenerTeam($event, $device->getTeam(), $trackerHistory->getTs());
            if (!$notifications) {
                $this->em->clear();
                return;
            }

            // filter the list of notifications for an object by the received device
            $notifications = $this->scopeService->filterNotifications(
                $notifications,
                $trackerHistory,
                [
                    EventLog::LAT => $trackerHistory->getLat(),
                    EventLog::LNG => $trackerHistory->getLng()
                ]
            );
            if (!$notifications) {
                $this->em->clear();
                return;
            }

            try {
                /** @var Notification $ntf */
                foreach ($notifications as $ntf) {
                    $this->setRedisKey(self::TAG_VEHICLE_TOWING, $event, $device, $ntf);
                    $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                    $status = $this->getStatus($trackerData, self::TAG_VEHICLE_TOWING);
                    $this->cacheData = $this->updateCache(['status' => $status], self::TAG_VEHICLE_TOWING);

                    // add only if there is a trigger condition
                    if (!$this->cacheData) {
                        $this->memoryDb->setToJsonTtl(
                            $this->redisKey,
                            $this->setDataCache($ntf, $event, $device, $vehicle, $trackerData, $status),
                            self::TAG_VEHICLE_TOWING,
                            self::MAX_TTL,
                        );

//                        $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
                        $this->em->clear();
                        continue;
                    }

                    if ($ntf->isTimeDuration() && !$ntf->isDistance()) {
                        $duration = $this->getDuration($ntf, $trackerData, $status, Device::STATUS_TOWING);
                        if (!is_null($duration)) {
                            $this->triggerTowingNtf($trackerHistory, $ntf, $duration);
                            continue;
                        }
                    }

                    if ($ntf->isDistance() && !$ntf->isTimeDuration()) {
                        $distance = $this->getDistance($ntf, $trackerData, $status, Device::STATUS_TOWING);
                        if (!is_null($distance)) {
                            $this->triggerTowingNtf($trackerHistory, $ntf, null, $distance);
                            continue;
                        }
                    }

                    if ($ntf->isDistance() && $ntf->isTimeDuration()) {
                        $distance = $this->getDistance($ntf, $trackerData, $status, Device::STATUS_TOWING);
                        $duration = $this->getDuration($ntf, $trackerData, $status, Device::STATUS_TOWING);

                        if ($ntf->getExpressionOperator() === Notification::OPERATOR_AND) {
                            if (!is_null($distance) && !is_null($duration)) {
                                $this->triggerTowingNtf($trackerHistory, $ntf, $duration, $distance);
                                continue;
                            }
                        } elseif ($ntf->getExpressionOperator() === Notification::OPERATOR_OR) {
                            if (!is_null($distance) || !is_null($duration)) {
                                $this->triggerTowingNtf($trackerHistory, $ntf, $duration, $distance);
                                continue;
                            }
                        }
                    }
                }
                $this->em->clear();
            } catch (\Throwable $e) {
                throw $e;
            }
        } catch (\Throwable $e) {
            $this->logException($e);
        }
    }

    private function triggerTowingNtf(
        TrackerHistory $trackerHistory,
        Notification $ntf,
        $duration = null,
        $distance = null
    ) {
        $lat = $trackerHistory->getLat() ?? null;
        $lng = $trackerHistory->getLng() ?? null;

//        if ($lat && $lng) {
//            $address = $this->mapService->getLocationByCoordinates($lat, $lng);
//        } else {
//            $address = null;
//        }
        $address = null;
        $context = [
            EventLog::ADDRESS => $address,
            EventLog::LAT => $lat,
            EventLog::LNG => $lng,
            EventLog::DURATION => $duration,
            EventLog::DISTANCE => $distance,
            'notificationId' => $ntf->getId(),
            'prevTHId' => $this->cacheData['thId'],
            'cacheData' => $this->cacheData
        ];
        // update the trigger value in the cache to not send notifications until the flag is cleared
        $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_VEHICLE_TOWING);

//        file_put_contents('towing.txt', '3 - cache - ' . json_encode($this->cacheData) . PHP_EOL, FILE_APPEND);
        $this->notificationDispatcher->dispatch(
            Event::VEHICLE_TOWING_EVENT,
            $trackerHistory,
            $trackerHistory->getTs(),
            $context
        );
    }

    private function getStatus(array $trackerData, string $tag): string
    {
        $status = self::getDeviceStatusForTowing(
            $trackerData['ignition'],
            $trackerData['movement']
        );

        switch ($status) {
            case Device::STATUS_DRIVING:
                $this->updateCache(['stopTime' => null], $tag);
                return $status;
            case Device::STATUS_TOWING:
                if ($this->cacheData['status'] !== Device::STATUS_TOWING) {
                    if ($this->memoryDb->deleteItem($this->redisKey)) {
                        $this->cacheData = [];
                    }
                }
                if ($this->cacheData) {
                    if (!empty($this->cacheData['stopTime']) && ($this->getStopDuration(
                                $trackerData['ts'], $this->cacheData['stopTime']) > self::IGNORE_STOPS
                        )) {
                        // delete the cache to start counting again for notifications
                        if ($this->memoryDb->deleteItem($this->redisKey)) {
                            $this->cacheData = [];
                        }

                        return Device::STATUS_STOPPED;
                    }

                    $this->updateCache(['stopTime' => null], $tag);
                }

                return Device::STATUS_TOWING;
            case Device::STATUS_STOPPED:
                // save the time of the first stop in order to calculate the duration of the stop in the future
                if ($this->cacheData && empty($this->cacheData['stopTime'])) {
                    $this->updateCache(['stopTime' => $trackerData['ts']], $tag);
                }

                if ($this->cacheData
                    && !empty($this->cacheData['stopTime'])
                    && ($this->getStopDuration($trackerData['ts'], $this->cacheData['stopTime']) < self::IGNORE_STOPS)
                    //handle the same and wrong ts
                    && ($this->getStopDuration($trackerData['ts'], $this->cacheData['stopTime']) > 0)
                ) {
                    return Device::STATUS_DRIVING;
                }

                return Device::STATUS_STOPPED;
            default:
                return $status;
        }
    }

    public static function getDeviceStatusForTowing($ignition = null, $movement = null): string
    {
        if (!is_null($movement) && !is_null($ignition)) {
            if ($movement == 1 && $ignition == 1) {
                $status = Device::STATUS_DRIVING;
            } else {
                $status = ($movement == 1) ? Device::STATUS_TOWING : Device::STATUS_STOPPED;
            }
        }

        return $status ?? Device::STATUS_STOPPED;
    }
}
