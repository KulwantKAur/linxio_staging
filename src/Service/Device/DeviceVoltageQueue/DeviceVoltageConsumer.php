<?php

namespace App\Service\Device\DeviceVoltageQueue;

use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\EntityManager\SlaveEntityManager;
use App\Service\Device\Consumer\MessageHelper;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\Notification\ScopeService;
use App\Service\Redis\MemoryDbService;
use App\Util\DateHelper;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Psr\Log\LoggerInterface;

class DeviceVoltageConsumer implements ConsumerInterface
{
    use TrackerHistoryConsumerTrait;

    private $em;
    private $notificationDispatcher;
    private $logger;
    private $slaveEntityManager;
    private MemoryDbService $memoryDb;
    private const TAG_VOLTAGE = 'voltage';
    private const MAX_TTL = 86400;
    private const IGNORE_STOPS = 120;
    protected array $cacheData = [];
    protected string $redisKey;
    protected ScopeService $scopeService;

    public const QUEUES_NUMBER = 3; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'voltage_device_'; // should be equal to `routing_keys` of queues

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        MemoryDbService $memoryDb,
        ScopeService $scopeService
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->memoryDb = $memoryDb;
        $this->scopeService = $scopeService;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }

            $deviceId = $message->device_id;
            $trackerHistoryId = $message->tracker_history_id;

            $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);
            if (!$device) {
                $this->em->clear();
                return;
            }

            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);

            /** @var Event $event */
            $event = $this->slaveEntityManager->getRepository(Event::class)->getEventByName(Event::TRACKER_VOLTAGE);

            if (!$trackerHistory || !$event || DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > 2) {
                $this->em->clear();
                return;
            }

            $notifications = $this->em->getRepository(Notification::class)
                ->getNotificationsByListenerTeam($event, $device->getTeam(), $trackerHistory->getTs());

            if (!count($notifications)) {
                $this->em->clear();
                return;
            }

            try {
                $trackerData = $this->makeTrackerHistoryData($trackerHistory);
                /** @var Notification $ntf */
                foreach ($notifications as $ntf) {
                    $this->redisKey = self::TAG_VOLTAGE . '-' . 'eventId-' . $event->getId()
                        . 'deviceId-' . $device->getId() . 'ntfId-' . $ntf->getId();

                    if (!$this->scopeService->filterNotifications([$ntf], $trackerHistory)) {
                        if ($this->memoryDb->deleteItem($this->redisKey)) {
                            $this->cacheData = [];
                        }
                        continue;
                    }

                    if (is_null($trackerHistory->getExternalVoltageVolts())
                        || $trackerHistory->getExternalVoltageVolts() > $ntf->getVoltageParam()) {
                        if ($this->memoryDb->deleteItem($this->redisKey)) {
                            $this->cacheData = [];
                        }
                        continue;
                    }

//                    $this->logger->info('cache', ['cache' => $this->memoryDb->getFromJson($this->redisKey)]);
                    $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                    if (!$this->cacheData) {
                        $this->memoryDb->setToJsonTtl(
                            $this->redisKey,
                            $this->setDataCache($ntf, $event, $device, $device->getVehicle(), $trackerData, null),
                            self::TAG_VOLTAGE,
                            self::MAX_TTL,
                        );
//                        $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
//                        continue;
                    }
                    if (!$ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && !$this->cacheData) {
                        if ($trackerHistory->getExternalVoltageVolts() < $ntf->getVoltageParam()) {
                            $this->cacheData = $this->memoryDb->getFromJson($this->redisKey);
                            $this->triggerVoltageNtf($trackerHistory, $ntf);
                        }
                        continue;
                    }

                    $status = $this->getStatus($trackerData, self::TAG_VOLTAGE);
//                    $this->cacheData = $this->updateCache(['status' => $status], self::TAG_VOLTAGE);
                    if (!$this->cacheData) {
                        continue;
                    }

                    if ($ntf->getTimeDurationParam() && !$ntf->getDistanceParam()) {
                        $duration = $this->getDuration($ntf, $trackerData, $status);
                        if (!is_null($duration)) {
                            $this->triggerVoltageNtf($trackerHistory, $ntf, $duration, 0);
                            continue;
                        }
                    }

                    if ($ntf->getDistanceParam() && !$ntf->getTimeDurationParam()) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        if (!is_null($distance)) {
                            $this->triggerVoltageNtf($trackerHistory, $ntf, 0, $distance);
                            continue;
                        }
                    }

                    if ($ntf->getDistanceParam() && $ntf->getTimeDurationParam()) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        $duration = $this->getDuration($ntf, $trackerData, $status);

                        if ($ntf->getExpressionOperator() === Notification::OPERATOR_AND) {
                            if (!is_null($distance) && !is_null($duration)) {
                                $this->triggerVoltageNtf($trackerHistory, $ntf, $duration, $distance);
                                continue;
                            }
                        } elseif ($ntf->getExpressionOperator() === Notification::OPERATOR_OR) {
                            if (!is_null($distance) || !is_null($duration)) {
                                $this->triggerVoltageNtf($trackerHistory, $ntf, $duration, $distance);
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
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    private function triggerVoltageNtf(
        TrackerHistory $trackerHistory,
        Notification $ntf,
        $duration = null,
        $distance = null
    ) {
        $context = [
            EventLog::DURATION => $duration ?? 0,
            EventLog::DISTANCE => $distance ?? 0,
            EventLog::LAT => $trackerHistory->getLat() ?? null,
            EventLog::LNG => $trackerHistory->getLng() ?? null,
            'notificationId' => $ntf->getId(),
            'THId' => $trackerHistory->getId(),
            'cacheData' => $this->cacheData
        ];
        // update the trigger value in the cache to not send notifications until the flag is cleared
        $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_VOLTAGE);
        $this->notificationDispatcher->dispatch(
            Event::TRACKER_VOLTAGE,
            $trackerHistory,
            $trackerHistory->getTs(),
            $context
        );
    }

    private function makeTrackerHistoryData(TrackerHistory $th): array
    {
        $trackerData = $th->toArray(MessageHelper::getTHFields());
        $trackerData['odometer'] = $trackerData['mileageFromTracker'];
        $trackerData['ts'] = $trackerData['tsISO8601'];

        return $trackerData;
    }

    /**
     * @return bool
     */
    public function isActionIgnoreStatus(): bool
    {
        return !$this->isActionIgnoreStatus;
    }
}
