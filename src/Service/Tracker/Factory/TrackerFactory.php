<?php

namespace App\Service\Tracker\Factory;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Producer\SensorEventProducer;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\DrivingBehavior\DrivingBehaviorService;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Redis\MemoryDbService;
use App\Service\Streamax\StreamaxService;
use App\Service\Traccar\TraccarService;
use App\Service\Tracker\PivotelTrackerService;
use App\Service\Tracker\TeltonikaTrackerService;
use App\Service\Tracker\TopflytechTrackerService;
use App\Service\Tracker\TrackerService;
use App\Service\Tracker\UlbotechTrackerService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

class TrackerFactory
{
    protected $em;
    protected $logger;
    protected $translator;
    protected $eventDispatcher;
    protected $settingService;
    protected $simulatorBaseImei;
    protected $simulatorDevicesCount;
    protected $notificationDispatcher;
    protected $drivingBehaviorService;
    protected $sensorEventProducer;
    protected TraccarService $traccarService;
    protected EngineOnTimeService $engineOnTimeService;
    protected MemoryDbService $memoryDb;
    protected BillingEntityHistoryService $billingEntityHistoryService;
    protected StreamaxService $streamaxService;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param int $simulatorBaseImei
     * @param int $simulatorDevicesCount
     * @param TraccarService $traccarService
     * @param SensorEventProducer $sensorEventProducer
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param DrivingBehaviorService $drivingBehaviorService
     * @param EngineOnTimeService $engineOnTimeService
     * @param MemoryDbService $memoryDb
     * @param BillingEntityHistoryService $billingEntityHistoryService
     * @param StreamaxService $streamaxService
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        int $simulatorBaseImei,
        int $simulatorDevicesCount,
        TraccarService $traccarService,
        SensorEventProducer $sensorEventProducer,
        NotificationEventDispatcher $notificationDispatcher,
        DrivingBehaviorService $drivingBehaviorService,
        EngineOnTimeService $engineOnTimeService,
        MemoryDbService $memoryDb,
        BillingEntityHistoryService $billingEntityHistoryService,
        StreamaxService $streamaxService
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->simulatorBaseImei = $simulatorBaseImei;
        $this->simulatorDevicesCount = $simulatorDevicesCount;
        $this->traccarService = $traccarService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->sensorEventProducer = $sensorEventProducer;
        $this->drivingBehaviorService = $drivingBehaviorService;
        $this->engineOnTimeService = $engineOnTimeService;
        $this->memoryDb = $memoryDb;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
        $this->streamaxService = $streamaxService;
    }

    /**
     * @param string $vendor
     * @param Device|null $device
     * @return TrackerService
     *
     * @throws \Exception
     */
    public function getInstance(string $vendor, ?Device $device = null)
    {
        switch ($vendor) {
            case DeviceVendor::VENDOR_TELTONIKA:
                return new TeltonikaTrackerService(
                    $this->em,
                    $this->logger,
                    $this->translator,
                    $this->simulatorBaseImei,
                    $this->simulatorDevicesCount,
                    $this->eventDispatcher,
                    $this->notificationDispatcher,
                    $this->drivingBehaviorService,
                    $this->engineOnTimeService,
                    $this->memoryDb,
                    $this->billingEntityHistoryService
                );
            case DeviceVendor::VENDOR_TOPFLYTECH:
                return new TopflytechTrackerService(
                    $this->em,
                    $this->eventDispatcher,
                    $this->notificationDispatcher,
                    $this->logger,
                    $this->sensorEventProducer,
                    $this->engineOnTimeService,
                    $this->memoryDb,
                    $this->billingEntityHistoryService
                );
            case DeviceVendor::VENDOR_ULBOTECH:
                return new UlbotechTrackerService(
                    $this->em,
                    $this->eventDispatcher,
                    $this->notificationDispatcher,
                    $this->logger,
                    $this->engineOnTimeService,
                    $this->memoryDb,
                    $this->billingEntityHistoryService
                );
            case DeviceVendor::VENDOR_PIVOTEL:
                return new PivotelTrackerService(
                    $this->em,
                    $this->eventDispatcher,
                    $this->notificationDispatcher,
                    $this->logger,
                    $this->engineOnTimeService,
                    $this->memoryDb,
                    $this->billingEntityHistoryService
                );
            case DeviceVendor::VENDOR_TRACCAR:
                return $this->traccarService;
            case DeviceVendor::VENDOR_STREAMAX:
                return $this->streamaxService;
            default:
                $message = 'Unsupported vendor: ' . $vendor;
                $message .= $device ? ' for device imei: ' . $device->getImei() : '';
                throw new \Exception($message);
        }
    }
}
