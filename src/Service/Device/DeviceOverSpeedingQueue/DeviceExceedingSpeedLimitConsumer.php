<?php

namespace App\Service\Device\DeviceOverSpeedingQueue;

use App\Command\DeviceExceedingSpeedLimitCommand;
use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\Device\Consumer\MessageHelper;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\MapService\MapServiceInterface;
use App\Service\MapService\MapServiceResolver;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Notification\ScopeService;
use App\Service\Redis\MemoryDbService;
use App\Util\DateHelper;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleHttpClient;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * @see DeviceExceedingSpeedLimitCommand
 * Part with snapToRoads is in DeviceExceedingSpeedLimitConsumer
 */
class DeviceExceedingSpeedLimitConsumer implements ConsumerInterface
{
    use TrackerHistoryConsumerTrait;
    use CommandLoggerTrait;

    private EntityManager $em;
    private NotificationEventDispatcher $notificationDispatcher;
    private LoggerInterface $logger;
    private SlaveEntityManager $slaveEntityManager;
    private MapServiceInterface $mapService;
    private MemoryDbService $memoryDb;
    private ScopeService $scopeService;

    protected array $cacheData = [];
    protected string $redisKey;
    protected string $tomtomKey;
    public const string TAG_EXCEEDING_SPEED_LIMIT = 'exceeding_speed_limit';
    public const int MAX_TTL = 86400;
    public const int IGNORE_STOPS = 120;
    public const int TOMTOM_QPS = 5;
    public const int DAYS_DIFF_VALID = 2;

    public const QUEUES_NUMBER = 3; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'exceeding_speed_limit_device_'; // should be equal to `routing_keys` of queues

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        MapServiceResolver $mapServiceResolver,
        MemoryDbService $memoryDb,
        ScopeService $scopeService,
        string $tomtomKey
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->mapService = $mapServiceResolver->getInstance();
        $this->memoryDb = $memoryDb;
        $this->scopeService = $scopeService;
        $this->tomtomKey = $tomtomKey;
    }

    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }

            $deviceId = $message->device_id;
            $thId = $message->th_id;
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($thId);
            $th = $trackerHistory->toArray(MessageHelper::getTHFields());

            $trackerData = $th ? $this->formatTHToArray($trackerHistory) : [];
            $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);

            /** @var Vehicle|null $vehicle */
            $vehicle = $device && $device->isVehicle() ? $device->getVehicle() : null;
            if (!$device || !$vehicle) {
                $this->em->clear();
                return;
            }

            $addons = $vehicle->getTeam()->getSettingsByName(Setting::BILLABLE_ADDONS);

            if (!$addons
                || !in_array(Setting::BILLABLE_ADDONS_SIGN_POST_SPEED_DATA, $addons->getValue())
                || in_array(Setting::BILLABLE_ADDONS_SNAP_TO_ROADS, $addons->getValue())
            ) {
                return;
            }

            if (!$trackerHistory
                || DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > self::DAYS_DIFF_VALID
                || !$this->isValidToTriggerEvent($trackerData['ts'], $trackerData['createdAt'])
            ) {
                $this->em->clear();
                return;
            }

            $event = $this->slaveEntityManager->getRepository(Event::class)->getEventByName(Event::EXCEEDING_SPEED_LIMIT);

            $notifications = $this->em->getRepository(Notification::class)
                ->getNotificationsByListenerTeam($event, $device->getTeam(), $trackerHistory->getTs());
            if (!$notifications) {
                $this->em->clear();
                return;
            }

            // getting a list of notifications for the entity by received device
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
                if (is_null($trackerHistory->getSpeed()) || !$trackerHistory->getLat() || !$trackerHistory->getLng()) {
                    return;
                }
                sleep(1);

                $speedLimit = $this->getSpeedLimit($trackerHistory);

                /** @var Notification $ntf */
                foreach ($notifications as $ntf) {
                    $this->redisKey = self::TAG_EXCEEDING_SPEED_LIMIT . '-' . 'eventId-' . $event->getId()
                        . 'deviceId-' . $device->getId() . 'ntfId-' . $ntf->getId();

                    $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                    $status = $this->getStatus($trackerData, self::TAG_EXCEEDING_SPEED_LIMIT);
                    $this->cacheData = $this->updateCache(['status' => $status], self::TAG_EXCEEDING_SPEED_LIMIT);

                    if (is_null($speedLimit) || ($speedLimit + $ntf->getThresholdParam()) > $trackerHistory->getSpeed()) {
                        if ($this->memoryDb->deleteItem($this->redisKey)) {
                            $this->cacheData = [];
                        }
                        continue;
                    }

                    // add only if there is a trigger condition
                    if (!$this->cacheData) {
                        $this->memoryDb->setToJsonTtl(
                            $this->redisKey,
                            $this->setDataCache($ntf, $event, $device, $vehicle, $trackerData, $status),
                            self::TAG_EXCEEDING_SPEED_LIMIT,
                            self::MAX_TTL,
                        );

//                        $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
//                        continue;
                    }

                    //trigger just by speed
                    if (!$ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && !$this->cacheData) {
                        $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                        $distance = $this->getDistanceForContext($trackerData);
                        $duration = $this->getDurationForContext($trackerData);
                        $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);

                        continue;
                    }

                    if ($ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && $this->cacheData) {
                        $distance = $this->getDistanceForContext($trackerData);
                        $duration = $this->getDuration($ntf, $trackerData, $status);

                        if ($duration) {
                            $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);
                            continue;
                        }
                    }

                    if ($ntf->getDistanceParam() && !$ntf->getTimeDurationParam() && $this->cacheData) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        $duration = $this->getDurationForContext($trackerData);

                        if ($distance) {
                            $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);
                            continue;
                        }
                    }

                    if ($ntf->getDistanceParam() && $ntf->getTimeDurationParam() && $this->cacheData) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        $duration = $this->getDuration($ntf, $trackerData, $status);
                        if ($distance && $duration) {
                            $this->triggerOverspeedingNtf($trackerHistory, $ntf, $speedLimit, $duration, $distance);
                            continue;
                        }
                    }
                }
                $this->em->clear();
            } catch (\Throwable $e) {
                throw $e;
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            if ($device) {
                $this->logger->error('teamId: ' . $device->getTeamId());
            }
            $this->logException($e);
        }
    }

    /**
     * @see DeviceExceedingSpeedLimitCommand
     * @see DeviceExceedingSpeedLimitConsumer:102
     * part with snapToRoads has been moved to DeviceExceedingSpeedLimitCommand:getSpeedLimitBatch()
     */
    private function getSpeedLimit(TrackerHistory $trackerHistory): ?float
    {
        $response = (new GuzzleHttpClient())->get(
            'https://api.tomtom.com/search/2/reverseGeocode/' . $trackerHistory->getLat() . ',' . $trackerHistory->getLng()
            . '.json?key=' . $this->tomtomKey . '&returnSpeedLimit=true',
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );
        $body = json_decode($response->getBody()->getContents(), true);
        $speedLimit = $body['addresses'][0]['address']['speedLimit'] ?? null;

        return $speedLimit ? (float)$speedLimit : null;
    }
}
