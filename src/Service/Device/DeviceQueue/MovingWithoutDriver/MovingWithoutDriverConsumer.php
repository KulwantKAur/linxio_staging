<?php
declare(strict_types=1);

namespace App\Service\Device\DeviceQueue\MovingWithoutDriver;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Device;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\BaseService;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\Device\DeviceService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Notification\NotificationService;
use App\Service\Notification\ScopeService;
use App\Service\Redis\MemoryDbService;
use App\Util\DateHelper;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * MovingWithoutDriverConsumer
 */
class MovingWithoutDriverConsumer implements ConsumerInterface
{
    use TrackerHistoryConsumerTrait;
    use CommandLoggerTrait;

    private EntityManager $em;
    private MemoryDbService $memoryDb;
    private NotificationEventDispatcher $notificationDispatcher;
    private ScopeService $scopeService;
    private LoggerInterface $logger;
    private SlaveEntityManager $slaveEntityManager;
    private SerializerInterface $serializer;

    private const MAX_TTL = 86400;
    private const IGNORE_STOPS = 120;
    private const TAG_MOVING_WHT_DRIVER = 'movingWthDriver';
    protected array $cacheData = [];
    protected string $redisKey;
    protected NotificationService $notificationService;

    public const QUEUES_NUMBER = 2; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'moving_wth_driver_device_'; // should be equal to `routing_keys` of queues

    private const TTS_TEXT = 'Vehicle has not been assigned a driver, please pull over and assign yourself to the vehicle';

    public function __construct(
        EntityManager $em,
        MemoryDbService $memoryDb,
        NotificationEventDispatcher $notificationDispatcher,
        ScopeService $scopeService,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        NotificationService $notificationService,
        SerializerInterface $serializer,
        private readonly DeviceService $deviceService
    ) {
        $this->em = $em;
        $this->memoryDb = $memoryDb;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->scopeService = $scopeService;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->notificationService = $notificationService;
        $this->serializer = $serializer;
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
            $trackerData = property_exists($message, 'trackerHistoryData')
                ? $this->formatTHObjectsArray([$message->trackerHistoryData])[0]
                : [];
            $trackerHistoryId = $message->trackerHistoryData?->id;
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);
            $device = $trackerHistory->getDevice();

            /** @var Vehicle|null $vehicle */
            $vehicle = $device && $device->isVehicle() ? $device->getVehicle() : null;
            if (!$device || !$vehicle) {
                $this->em->clear();
                return;
            }

            if (!$trackerHistory
                || (!is_null($trackerHistory->getTs()) && DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > 2)
                || (!empty($trackerData['ts'])
                    && !$this->isValidToTriggerEvent($trackerData['ts'], $trackerData['createdAt']))
            ) {
                $this->em->clear();
                return;
            }

            /** @var Event $event */
            $event = $this->slaveEntityManager->getRepository(Event::class)
                ->getEventByName(Event::VEHICLE_DRIVING_WITHOUT_DRIVER);

            $notifications = $this->em->getRepository(Notification::class)
                ->getNotificationsByListenerTeam(
                    $event,
                    $device->getTeam(),
                    BaseService::parseDateToUTC($trackerData['ts'])
                );
            if (!$notifications) {
                $this->em->clear();
                return;
            }

            // getting a list of notifications for the entity by received device
            $notifications = $this->scopeService->filterNotifications($notifications, $trackerHistory);
            if (!$notifications) {
                $this->em->clear();
                return;
            }

            foreach ($notifications as $ntf) {
                $this->setRedisKey(self::TAG_MOVING_WHT_DRIVER, $event, $device, $ntf);

                $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                $status = $this->getStatus($trackerData, self::TAG_MOVING_WHT_DRIVER);
                $this->cacheData = $this->updateCache(['status' => $status], self::TAG_MOVING_WHT_DRIVER);

                // add only if there is a trigger condition
                if (!$this->cacheData && is_null($trackerData['driverId'])) {
                    $this->memoryDb->setToJsonTtl(
                        $this->redisKey,
                        $this->setDataCache($ntf, $event, $device, $vehicle, $trackerData, $status),
                        self::TAG_MOVING_WHT_DRIVER,
                        self::MAX_TTL,
                    );
                    continue;
                }

                if ($trackerData['id']
                    && is_null($trackerData['driverId'])
                    && $status === Device::STATUS_DRIVING
                    && $this->isDuration($ntf, $trackerData)
                    && $this->isDistance($ntf, $trackerData)
                    && !$this->cacheData['isTrigger']
                ) {
                    // update the trigger value in the cache to not send notifications until the flag is deleted
                    $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_MOVING_WHT_DRIVER);
                    $duration = $this->getDrivingDuration($trackerData['ts'], $this->cacheData['ts']);
                    $distance = $this->getDistance($trackerData['odometer'], $this->cacheData['odometer']);

                    $this->triggerDeviceTts($trackerHistory->getDevice(), self::TTS_TEXT, $this->logger);

                    $this->notificationDispatcher->dispatch(
                        Event::VEHICLE_DRIVING_WITHOUT_DRIVER,
                        $trackerHistory,
                        $trackerHistory->getTs(),
                        [
                            'duration' => $duration,
                            'distance' => $distance,
                            'notificationId' => $ntf->getId(),
                            'cacheData' => $this->cacheData
                        ]
                    );
                }
            }

            $this->em->clear();
        } catch (\Throwable $e) {
            $this->logException($e);
            return false;
        }
    }

    /**
     * @param int $odometerNow
     * @param int $odometerPrev
     * @return int
     */
    public function getDistance(int $odometerNow, int $odometerPrev): int
    {
        return $odometerNow - $odometerPrev;
    }

    /**
     * @param Notification $ntf
     * @param array $trackerData
     * @return bool
     */
    private function isDuration(Notification $ntf, array $trackerData): bool
    {
        $timeDurationNtf = $ntf->getAdditionalParams()[Notification::TIME_DURATION] ?? null;

        return !is_null($timeDurationNtf) && $trackerData['ts'] && $this->cacheData['ts']
            && ($this->getDrivingDuration($trackerData['ts'], $this->cacheData['ts']) >= $timeDurationNtf);
    }

    /**
     * @param Notification $ntf
     * @param array $trackerData
     * @return bool
     */
    private function isDistance(Notification $ntf, array $trackerData): bool
    {
        $distanceNtf = $ntf->getAdditionalParams()[Notification::DISTANCE] ?? null;

        return !is_null($distanceNtf) && $trackerData['odometer'] && $this->cacheData['odometer']
            && ($this->getDistance($trackerData['odometer'], $this->cacheData['odometer']) >= $distanceNtf);
    }
}
