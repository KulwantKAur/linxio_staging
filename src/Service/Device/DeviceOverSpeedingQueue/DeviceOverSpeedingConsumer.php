<?php

namespace App\Service\Device\DeviceOverSpeedingQueue;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\Device\Consumer\MessageHelper;
use App\Service\Device\Consumer\TrackerHistoryConsumerTrait;
use App\Service\Device\DeviceCommandService;
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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DeviceOverSpeedingConsumer implements ConsumerInterface
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
    private SerializerInterface $serializer;
    private DeviceCommandService $deviceCommandService;

    private const TAG_OVERSPEEDING = 'overspeeding';
    private const MAX_TTL = 86400;
    private const IGNORE_STOPS = 120;
    protected array $cacheData = [];
    protected string $redisKey;

    public const QUEUES_NUMBER = 3; // should be equal to number of queues in `config/rabbitmq.yaml`
    public const ROUTING_KEY_PREFIX = 'overspeeding_device_'; // should be equal to `routing_keys` of queues

    public function __construct(
        EntityManager $em,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        SlaveEntityManager $slaveEntityManager,
        MapServiceResolver $mapServiceResolver,
        MemoryDbService $memoryDb,
        ScopeService $scopeService,
        SerializerInterface $serializer,
        DeviceCommandService $deviceCommandService,
    ) {
        $this->em = $em;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->mapService = $mapServiceResolver->getInstance();
        $this->memoryDb = $memoryDb;
        $this->scopeService = $scopeService;
        $this->serializer = $serializer;
        $this->deviceCommandService = $deviceCommandService;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Doctrine\DBAL\ConnectionException
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
            $device = $this->em->getRepository(Device::class)->getDeviceWithVehicle($deviceId);

            /** @var Vehicle|null $vehicle */
            $vehicle = $device && $device->isVehicle() ? $device->getVehicle() : null;
            if (!$device || !$vehicle) {
                $this->em->clear();
                return;
            }


            /** @var TrackerHistory $trackerHistory */
            $trackerHistory = $this->serializer->deserialize(
                json_encode($trackerData),
                TrackerHistory::class,
                JsonEncoder::FORMAT,
                [
                    DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339,
                    DateTimeNormalizer::TIMEZONE_KEY => 'UTC',
                    AbstractNormalizer::GROUPS => ['th_by_event:read'],
                    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
                    ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ]
            );

            $trackerHistory->setDevice($device);
            $trackerHistory->setVehicle($vehicle);

            if (!$trackerHistory
                || DateHelper::getDiffInDaysNow($trackerHistory->getTs()) > 2
                || !$this->isValidToTriggerEvent($trackerData['ts'], $trackerData['createdAt'])
            ) {
                $this->em->clear();
                return;
            }

            /** @var Event $event */
            $event = $this->slaveEntityManager->getRepository(Event::class)
                ->getEventByName(Event::VEHICLE_OVERSPEEDING);

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
                /** @var Notification $ntf */
                foreach ($notifications as $ntf) {
                    $this->redisKey = self::TAG_OVERSPEEDING . '-' . 'eventId-' . $event->getId()
                        . 'deviceId-' . $device->getId() . 'ntfId-' . $ntf->getId();

                    $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                    $status = $this->getStatus($trackerData, self::TAG_OVERSPEEDING);
                    $this->cacheData = $this->updateCache(['status' => $status], self::TAG_OVERSPEEDING);

                    if (is_null($trackerHistory->getSpeed())) {
                        return;
                    }

                    if ($ntf->getOverSpeedParam() > $trackerHistory->getSpeed()) {
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
                            self::TAG_OVERSPEEDING,
                            self::MAX_TTL,
                        );

//                        $this->logger->info('Save cache', ['redisKey' => $this->redisKey]);
//                        continue;
                    }

                    $this->deviceCommandService->triggerOverSpeedingAlarm($device);

                    //trigger just by speed
                    if (!$ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && !$this->cacheData) {
                        $this->cacheData = $this->memoryDb->getFromJson($this->redisKey) ?: [];
                        $this->triggerOverspeedingNtf($trackerHistory, $ntf);

                        continue;
                    }

                    if ($ntf->getTimeDurationParam() && !$ntf->getDistanceParam() && $this->cacheData) {
                        $duration = $this->getDuration($ntf, $trackerData, $status);

                        if ($duration) {
                            $this->triggerOverspeedingNtf($trackerHistory, $ntf, $duration);
                            continue;
                        }
                    }

                    if ($ntf->getDistanceParam() && !$ntf->getTimeDurationParam() && $this->cacheData) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);

                        if ($distance) {
                            $this->triggerOverspeedingNtf($trackerHistory, $ntf, null, $distance);
                            continue;
                        }
                    }

                    if ($ntf->getDistanceParam() && $ntf->getTimeDurationParam() && $this->cacheData) {
                        $distance = $this->getDistance($ntf, $trackerData, $status);
                        $duration = $this->getDuration($ntf, $trackerData, $status);
                        if ($distance && $duration) {
                            $this->triggerOverspeedingNtf($trackerHistory, $ntf, $duration, $distance);
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
            $this->logException($e);
        }
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @param Notification $ntf
     * @param $duration
     * @param $distance
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function triggerOverspeedingNtf(
        TrackerHistory $trackerHistory,
        Notification $ntf,
        $duration = null,
        $distance = null
    ) {
        $lat = $trackerHistory->getLat() ?? null;
        $lng = $trackerHistory->getLng() ?? null;

        if ($lat && $lng) {
            $address = $this->mapService->getLocationByCoordinates($lat, $lng);
        } else {
            $address = null;
        }
        $context = [
            EventLog::ADDRESS => $address,
            EventLog::LAT => $lat,
            EventLog::LNG => $lng,
            EventLog::DURATION => $duration ?? 0,
            EventLog::DISTANCE => $distance ?? 0,
            'notificationId' => $ntf->getId(),
            'prevTHId' => $this->cacheData['thId'],
            'cacheData' => $this->cacheData
        ];
        // update the trigger value in the cache to not send notifications until the flag is cleared
        $this->cacheData = $this->updateCache(['isTrigger' => true], self::TAG_OVERSPEEDING);

        $this->notificationDispatcher->dispatch(
            Event::VEHICLE_OVERSPEEDING,
            $trackerHistory,
            $trackerHistory->getTs(),
            $context
        );
    }
}
