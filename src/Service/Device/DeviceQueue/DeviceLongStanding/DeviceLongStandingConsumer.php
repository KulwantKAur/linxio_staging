<?php

namespace App\Service\Device\DeviceQueue\DeviceLongStanding;

use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\BaseService;
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
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class DeviceLongStandingConsumer implements ConsumerInterface
{
    use TrackerHistoryConsumerTrait;

    private EntityManager $em;
    private NotificationEventDispatcher $notificationDispatcher;
    private LoggerInterface $logger;
    private SlaveEntityManager $slaveEntityManager;
    private MapServiceInterface $mapService;
    private MemoryDbService $memoryDb;
    private ScopeService $scopeService;
    private const TAG_LONG_STANDING = 'longStanding';
    private const MAX_TTL = 86400;
    private const IGNORE_STOPS = 0;
    protected array $cacheData = [];
    protected string $redisKey;

    public const QUEUES_NUMBER = 2; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'long_standing_device_'; // should be equal to `routing_keys` of queues

    /**
     * @param EntityManager $em
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param LoggerInterface $logger
     * @param SlaveEntityManager $slaveEntityManager
     * @param MapServiceResolver $mapServiceResolver
     * @param MemoryDbService $memoryDb
     * @param ScopeService $scopeService
     * @throws \Exception
     */
    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        MapServiceResolver $mapServiceResolver,
        MemoryDbService $memoryDb,
        ScopeService $scopeService
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->mapService = $mapServiceResolver->getInstance();
        $this->memoryDb = $memoryDb;
        $this->scopeService = $scopeService;
    }

    /**
     * @param AMQPMessage $msg
     * @return false|void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }
            $deviceId = $message->device_id;
//            $trackerHistoryId = $message->tracker_history_id;

            $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);
            /** @var Vehicle|null $vehicle */
            $vehicle = $device && $device->isVehicle() ? $device->getVehicle() : null;
            if (!$device || !$vehicle) {
                $this->em->clear();
                return;
            }

            /** @var Event $event */
            $event = $this->slaveEntityManager->getRepository(Event::class)
                ->getEventByName(Event::VEHICLE_LONG_STANDING);

//            $trackerData = $this->makeTrackerHistoryData($trackerHistory);
            $trackerHistoriesArray = property_exists($message, 'tracker_history_data')
                ? $this->formatTHObjectsArray($message->tracker_history_data)
                : null;
            if (!$trackerHistoriesArray) {
                return;
            }

            foreach ($trackerHistoriesArray as $trackerData) {
//                $this->logger->info('checking data', ['trackerData' => $trackerData]);
                if (!isset($trackerData['id']) || !isset($trackerData['ts'])) {
                    continue;
                }

                if (!$event || !$this->isValidToTriggerEvent($trackerData['ts'], $trackerData['createdAt'])
                ) {
                    $this->em->clear();
                    continue;
                }

                $notifications = $this->em->getRepository(Notification::class)
                    ->getNotificationsByListenerTeam(
                        $event,
                        $device->getTeam(),
                        BaseService::parseDateToUTC($trackerData['ts'])
                    );
                if (!$notifications) {
                    $this->em->clear();
                    continue;
                }

                /** @var TrackerHistory $trackerHistory */
                $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerData['id']);
                if (!$trackerHistory) {
                    $this->em->clear();
                    continue;
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
                    continue;
                }

//                $this->logger->info(
//                    'Found notification by team',
//                    ['message' => $message, 'count' => count($notifications), 'team_id' => $device->getTeamId()]
//                );

                /** @var Notification $ntf */
                foreach ($notifications as $ntf) {
                    $this->setRedisKey(self::TAG_LONG_STANDING, $event, $device, $ntf);
//
//                    $this->logger->info('checking ntf to scopes', ['message' => $message, 'ntf' => $ntf->getId()]);
//                    if (!$this->scopeService->filterNotifications(
//                        [$ntf],
//                        $trackerHistory,
//                        [
//                            EventLog::LAT => $trackerHistory->getLat(),
//                            EventLog::LNG => $trackerHistory->getLng()
//                        ]
//                    )
//                    ) {
//                        $this->logger->info('not found to scopes', ['message' => $message, 'ntf' => $ntf->getId()]);
//                        if ($this->memoryDb->deleteItem($this->redisKey)) {
//                            $this->cacheData = [];
//                        }
//                        continue;
//                    }
//                    $this->logger->info('checking to scopes finished', ['message' => $message, 'ntf' => $ntf->getId()]);

                    $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                    $status = $this->getStatus($trackerData, self::TAG_LONG_STANDING);
                    $this->cacheData = $this->updateCache(['status' => $status], self::TAG_LONG_STANDING);

                    // add only if there is a trigger condition
                    if (!$this->cacheData) {
                        $this->memoryDb->setToJsonTtl(
                            $this->redisKey,
                            $this->setDataCache($ntf, $event, $device, $vehicle, $trackerData, $status),
                            self::TAG_LONG_STANDING,
                            self::MAX_TTL,
                        );

//                        $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
                        continue;
                    }

                    if ($ntf->isTimeDuration()) {
                        $duration = $this->getDuration($ntf, $trackerData, $status, Device::STATUS_STOPPED);
                        if (!is_null($duration)) {
                            $this->triggerLongStandingNtf($trackerHistory, $ntf, $duration);
                            continue;
                        }
                    }
                }

//                $this->logger->info('notification processing completed', ['message' => $message]);
            }

            $this->em->clear();
//            $this->slaveEntityManager->clear();
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            return false;
        }
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @param Notification $ntf
     * @param $duration
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function triggerLongStandingNtf(
        TrackerHistory $trackerHistory,
        Notification $ntf,
        $duration = null
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
            'notificationId' => $ntf->getId(),
            'prevTHId' => $this->cacheData['thId'],
            'cacheData' => $this->cacheData
        ];
        // update the trigger value in the cache to not send notifications until the flag is cleared
        $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_LONG_STANDING);

        $this->notificationDispatcher->dispatch(
            Event::VEHICLE_LONG_STANDING,
            $trackerHistory,
            $trackerHistory->getTs(),
            $context
        );
    }

    private function getStatus(array $trackerData, string $tag): string
    {
        $status = TrackerHistory::getDeviceStatusByIgnitionAndMovement(
            $trackerData['ignition'],
            $trackerData['movement']
        );

        switch ($status) {
            case Device::STATUS_DRIVING:
                if ($this->cacheData) {
                    if ($this->memoryDb->deleteItem($this->redisKey)) {
                        $this->cacheData = [];
                    }
                    $this->updateCache(['stopTime' => null], $tag);
                }

                return Device::STATUS_DRIVING;
            case Device::STATUS_IDLE:
            case Device::STATUS_STOPPED:
                // save the time of the first stop in order to calculate the duration of the stop in the future
                if ($this->cacheData && empty($this->cacheData['stopTime'])) {
                    $this->updateCache(['stopTime' => $trackerData['ts']], $tag);
                }

                return Device::STATUS_STOPPED;
            default:
                return $status;
        }
    }
}
