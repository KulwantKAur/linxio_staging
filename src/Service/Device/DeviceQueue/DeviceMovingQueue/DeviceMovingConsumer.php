<?php

namespace App\Service\Device\DeviceQueue\DeviceMovingQueue;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\Device\DeviceService;
use App\Service\MapService\MapServiceInterface;
use App\Service\MapService\MapServiceResolver;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Notification\ScopeService;
use App\Service\Redis\MemoryDbService;
use App\Util\DateHelper;
use App\Util\ExceptionHelper;
use Doctrine\Common\Annotations\AnnotationReader;
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

class DeviceMovingConsumer implements ConsumerInterface
{
    use TrackerHistoryConsumerTrait;
    use CommandLoggerTrait;

    private NotificationEventDispatcher $notificationDispatcher;
    private LoggerInterface $logger;
    private SlaveEntityManager $slaveEntityManager;
    private MapServiceInterface $mapService;
    private MemoryDbService $memoryDb;
    private ScopeService $scopeService;
    private SerializerInterface $serializer;

    private const TAG_VEHICLE_MOVING = 'vehicleMoving';
    private const MAX_TTL = 86400;
    private const IGNORE_STOPS = 120;
    public const QUEUES_NUMBER = 2; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'moving_device_'; // should be equal to `routing_keys` of queues
    private const TTS_TEXT = 'This vehicle is being monitored by Linxio Vision';

    protected array $cacheData = [];
    protected string $redisKey;
    private ?Event $event;

    public function __construct(
        private readonly EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        MapServiceResolver $mapServiceResolver,
        MemoryDbService $memoryDb,
        ScopeService $scopeService,
        SerializerInterface $serializer,
        private readonly DeviceService $deviceService
    ) {
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->mapService = $mapServiceResolver->getInstance();
        $this->memoryDb = $memoryDb;
        $this->scopeService = $scopeService;
        $this->serializer = $serializer;
        $this->event = $this->em->getRepository(Event::class)
            ->getEventByName(Event::VEHICLE_MOVING);
    }


    /**
     * @param AMQPMessage $msg
     * @return bool|int|void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $message = json_decode($msg->getBody());
            if (!$message) {
                return;
            }

            $trackerData = property_exists($message, 'trackerHistoryData')
                ? $this->formatTHObjectsArray([$message->trackerHistoryData])[0]
                : [];
            $trackerHistoryId = $message->trackerHistoryData?->id;
            $trackerHistory = $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);

            $device = $trackerHistory->getDevice();

            /** @var Vehicle|null $vehicle */
            $vehicle = $device && $device->isVehicle() ? $device->getVehicle() : null;

            if (!$device || !$vehicle || !$trackerHistory
                || DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > 2
                || !$this->isValidToTriggerEvent($trackerData['ts'], $trackerData['createdAt'])
            ) {
                $this->em->clear();
                return;
            }

            /** @var Event $event */
            $event = $this->event ?? $this->em->getRepository(Event::class)
                ->getEventByName(Event::VEHICLE_MOVING);
            if (!$event) {
                $this->em->clear();
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
                    $this->setRedisKey(self::TAG_VEHICLE_MOVING, $event, $device, $ntf);
                    $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                    $status = $this->getStatus($trackerData, self::TAG_VEHICLE_MOVING);
                    $this->cacheData = $this->updateCache(['status' => $status], self::TAG_VEHICLE_MOVING);

                    // add only if there is a trigger condition
                    if (!$this->cacheData) {
                        $this->memoryDb->setToJsonTtl(
                            $this->redisKey,
                            $this->setDataCache($ntf, $event, $device, $vehicle, $trackerData, $status),
                            self::TAG_VEHICLE_MOVING,
                            self::MAX_TTL,
                        );

//                        $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
                        $this->em->clear();
                        continue;
                    }

                    if ($ntf->isTimeDuration() && !$ntf->isDistance()) {
                        $duration = $this->getDuration($ntf, $trackerData, $status);
                        if (!is_null($duration)) {
                            $this->triggerLongDrivingNtf($trackerHistory, $ntf, $duration);
                            continue;
                        }
                    }

                    if ($ntf->isDistance() && !$ntf->isTimeDuration()) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        if (!is_null($distance)) {
                            $this->triggerLongDrivingNtf($trackerHistory, $ntf, null, $distance);
                            continue;
                        }
                    }

                    if ($ntf->isDistance() && $ntf->isTimeDuration()) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        $duration = $this->getDuration($ntf, $trackerData, $status);

                        if ($ntf->getExpressionOperator() === Notification::OPERATOR_AND) {
                            if (!is_null($distance) && !is_null($duration)) {
                                $this->triggerLongDrivingNtf($trackerHistory, $ntf, $duration, $distance);
                                continue;
                            }
                        } elseif ($ntf->getExpressionOperator() === Notification::OPERATOR_OR) {
                            if (!is_null($distance) || !is_null($duration)) {
                                $this->triggerLongDrivingNtf($trackerHistory, $ntf, $duration, $distance);
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

    private function triggerLongDrivingNtf(
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
        $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_VEHICLE_MOVING);
        $this->triggerDeviceTts($trackerHistory->getDevice(), self::TTS_TEXT, $this->logger);

        $this->notificationDispatcher->dispatch(
            Event::VEHICLE_MOVING,
            $trackerHistory,
            $trackerHistory->getTs(),
            $context
        );
    }
}
