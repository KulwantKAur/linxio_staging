<?php

namespace App\EventListener\Device;

use App\Entity\BillingEntityHistory;
use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Reseller;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryDTCVIN;
use App\Entity\Tracker\TrackerHistoryJammer;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\Device\DeviceAccidentReceivedEvent;
use App\Events\Device\DeviceBatteryEvent;
use App\Events\Device\DeviceChangedTeamEvent;
use App\Events\Device\DeviceContractChangedEvent;
use App\Events\Device\DeviceCreatedEvent;
use App\Events\Device\DeviceDeactivatedEvent;
use App\Events\Device\DeviceDeletedEvent;
use App\Events\Device\DeviceDrivingBehaviorReceivedEvent;
use App\Events\Device\DeviceDTCVINReceivedEvent;
use App\Events\Device\DeviceEngineOnTimeEvent;
use App\Events\Device\DeviceExceedingSpeedLimitEvent;
use App\Events\Device\DeviceFinishedReceiveDataEvent;
use App\Events\Device\DeviceInstalledEvent;
use App\Events\Device\DeviceIOEvent;
use App\Events\Device\DeviceJammerReceivedEvent;
use App\Events\Device\DeviceLongDrivingEvent;
use App\Events\Device\DeviceLongStandingEvent;
use App\Events\Device\DeviceMovingEvent;
use App\Events\Device\DeviceMovingWithoutDriverEvent;
use App\Events\Device\DeviceNetworkReceivedEvent;
use App\Events\Device\DevicePanicButtonEvent;
use App\Events\Device\DevicePhoneChangedEvent;
use App\Events\Device\DeviceRestoredEvent;
use App\Events\Device\DeviceTodayDataEvent;
use App\Events\Device\DeviceTowingEvent;
use App\Events\Device\DeviceUnavailableEvent;
use App\Events\Device\DeviceUninstalledEvent;
use App\Events\Device\DeviceUpdatedEvent;
use App\Events\Device\DeviceVoltageEvent;
use App\Events\Device\EngineHistoryEvent;
use App\Events\Device\OverSpeedingEvent;
use App\Events\Vehicle\VehicleStatusChangedEvent;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\Device\DeviceBatteryQueue\DeviceBatteryConsumer;
use App\Service\Device\DeviceBatteryQueue\DeviceBatteryQueueMessage;
use App\Service\Device\DeviceIOQueue\IOConsumer;
use App\Service\Device\DeviceIOQueue\IOQueueMessage;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceExceedingSpeedLimitConsumer;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceExceedingSpeedLimitQueueMessage;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceOverSpeedingConsumer;
use App\Service\Device\DeviceOverSpeedingQueue\DeviceOverSpeedingQueueMessage;
use App\Service\Device\DeviceQueue\DeviceLongDrivingQueue\DeviceLongDrivingConsumer;
use App\Service\Device\DeviceQueue\DeviceLongDrivingQueue\DeviceLongDrivingQueueMessage;
use App\Service\Device\DeviceQueue\DeviceLongStanding\DeviceLongStandingConsumer;
use App\Service\Device\DeviceQueue\DeviceLongStanding\DeviceLongStandingQueueMessage;
use App\Service\Device\DeviceQueue\DeviceMovingQueue\DeviceMovingConsumer;
use App\Service\Device\DeviceQueue\DeviceMovingQueue\DeviceMovingQueueMessage;
use App\Service\Device\DeviceQueue\EngineOnTime\EngineOnTimeConsumer;
use App\Service\Device\DeviceQueue\EngineOnTime\EngineOnTimeQueueMessage;
use App\Service\Device\DeviceQueue\MovingWithoutDriver\MovingWithoutDriverConsumer;
use App\Service\Device\DeviceQueue\MovingWithoutDriver\MovingWithoutDriverMessage;
use App\Service\Device\DeviceQueue\PanicButton\PanicButtonQueueMessage;
use App\Service\Device\DeviceService;
use App\Service\Device\DeviceTowingQueue\DeviceTowingConsumer;
use App\Service\Device\DeviceTowingQueue\DeviceTowingQueueMessage;
use App\Service\Device\DeviceVoltageQueue\DeviceVoltageConsumer;
use App\Service\Device\DeviceVoltageQueue\DeviceVoltageQueueMessage;
use App\Service\Device\EngineHistoryQueue\EngineHistoryConsumer;
use App\Service\Device\EngineHistoryQueue\EngineHistoryQueueMessage;
use App\Service\Device\TodayDataQueue\TodayDataConsumer;
use App\Service\Device\TodayDataQueue\TodayDataQueueMessage;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\DeviceRedisModel;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\PanicButtonInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\TopflytechTrackerService;
use App\Service\Vehicle\VehicleOdometerService;
use App\Service\Vehicle\VehicleService;
use App\Util\DateHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeviceListener implements EventSubscriberInterface
{
    private $listenedEvents = [];

    private $em;
    private $entityHistoryService;
    private $tokenStorage;
    private $deviceService;
    private $vehicleService;
    private Producer $voltageProducer;
    private Producer $batteryProducer;
    private Producer $towingProducer;
    private Producer $panicButtonProducer;
    private Producer $overSpeedingProducer;
    private Producer $exceedingSpeedLimitProducer;
    private Producer $longDrivingProducer;
    private Producer $longStandingProducer;
    private Producer $engineOnTimeProducer;
    private Producer $ioProducer;
    private Producer $movingWithoutDriverProducer;
    private Producer $movingProducer;
    private Producer $todayDataProducer;
    private Producer $trackerEngineHistoryProducer;
    private $notificationDispatcher;
    private $eventDispatcher;
    private $vehicleOdometerService;
    private $clientObjectPersister;
    private $resellerObjectPersister;
    private BillingEntityHistoryService $billingEntityHistoryService;
    private ObjectPersister $deviceObjectPersister;
    private ObjectPersister $reminderObjectPersister;
    public MemoryDbService $memoryDb;

    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        TokenStorageInterface $tokenStorage,
        DeviceService $deviceService,
        VehicleService $vehicleService,
        Producer $voltageProducer,
        Producer $towingProducer,
        Producer $panicButtonProducer,
        Producer $overSpeedingProducer,
        Producer $exceedingSpeedLimitProducer,
        Producer $longDrivingProducer,
        Producer $longStandingProducer,
        Producer $engineOnTimeProducer,
        Producer $ioProducer,
        Producer $movingWithoutDriverProducer,
        Producer $movingProducer,
        NotificationEventDispatcher $notificationDispatcher,
        VehicleOdometerService $vehicleOdometerService,
        EventDispatcherInterface $eventDispatcher,
        ObjectPersister $clientObjectPersister,
        ObjectPersister $resellerObjectPersister,
        Producer $batteryProducer,
        BillingEntityHistoryService $billingEntityHistoryService,
        ObjectPersister $deviceObjectPersister,
        MemoryDbService $memoryDb,
        ObjectPersister $reminderObjectPersister,
        Producer $todayDataProducer,
        Producer $trackerEngineHistoryProducer
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->tokenStorage = $tokenStorage;
        $this->deviceService = $deviceService;
        $this->vehicleService = $vehicleService;
        $this->voltageProducer = $voltageProducer;
        $this->towingProducer = $towingProducer;
        $this->panicButtonProducer = $panicButtonProducer;
        $this->overSpeedingProducer = $overSpeedingProducer;
        $this->exceedingSpeedLimitProducer = $exceedingSpeedLimitProducer;
        $this->engineOnTimeProducer = $engineOnTimeProducer;
        $this->ioProducer = $ioProducer;
        $this->movingWithoutDriverProducer = $movingWithoutDriverProducer;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->vehicleOdometerService = $vehicleOdometerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->clientObjectPersister = $clientObjectPersister;
        $this->resellerObjectPersister = $resellerObjectPersister;
        $this->batteryProducer = $batteryProducer;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
        $this->longDrivingProducer = $longDrivingProducer;
        $this->longStandingProducer = $longStandingProducer;
        $this->movingProducer = $movingProducer;
        $this->deviceObjectPersister = $deviceObjectPersister;
        $this->memoryDb = $memoryDb;
        $this->reminderObjectPersister = $reminderObjectPersister;
        $this->todayDataProducer = $todayDataProducer;
        $this->trackerEngineHistoryProducer = $trackerEngineHistoryProducer;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DeviceCreatedEvent::NAME => 'onDeviceCreated',
            DeviceUpdatedEvent::NAME => 'onDeviceUpdated',
            DeviceDeletedEvent::NAME => 'onDeviceDeleted',
            DeviceRestoredEvent::NAME => 'onDeviceRestored',
            DeviceChangedTeamEvent::NAME => 'onDeviceChangedTeam',
            DeviceUnavailableEvent::NAME => 'onDeviceUnavailable',
            DeviceDeactivatedEvent::NAME => 'onDeviceDeactivated',
            DeviceInstalledEvent::NAME => 'onDeviceInstalled',
            DeviceContractChangedEvent::NAME => 'onDeviceContractChanged',
            DevicePhoneChangedEvent::NAME => 'onDevicePhoneChanged',
            DeviceUninstalledEvent::NAME => 'onDeviceUninstalled',
            DeviceFinishedReceiveDataEvent::NAME => 'onDeviceFinishedReceiveData', 
           DeviceVoltageEvent::NAME => 'onDeviceVoltageEvent',
            DeviceTowingEvent::NAME => 'onDeviceTowingEvent',
            DevicePanicButtonEvent::NAME => 'onDevicePanicButtonEvent',
            OverSpeedingEvent::NAME => 'onDeviceOverSpeedingEvent',
            DeviceExceedingSpeedLimitEvent::NAME => 'onDeviceExceedingSpeedLimitEvent',
            DeviceEngineOnTimeEvent::NAME => 'onDeviceEngineOnTimeEvent',
            DeviceIOEvent::NAME => 'onDeviceIOEvent',
            DeviceDTCVINReceivedEvent::NAME => 'onTopflytechDTCVINReceivedEvent',
            DeviceDrivingBehaviorReceivedEvent::NAME => 'onDrivingBehaviorReceivedEvent',
            DeviceMovingWithoutDriverEvent::NAME => 'onDeviceMovingWithoutDriverEvent',
            DeviceBatteryEvent::NAME => 'onDeviceBatteryEvent',
            DeviceNetworkReceivedEvent::NAME => 'onDeviceNetworkReceivedEvent',
            DeviceLongDrivingEvent::NAME => 'onDeviceLongDrivingEvent',
            DeviceLongStandingEvent::NAME => 'onDeviceLongStandingEvent',
            DeviceMovingEvent::NAME => 'onDeviceMovingEvent',
            DeviceTodayDataEvent::NAME => 'onDeviceTodayDataEvent',
            DeviceJammerReceivedEvent::NAME => 'onTopflytechJammerReceivedEvent',
            EngineHistoryEvent::NAME => 'onEngineHistoryEvent',
            DeviceAccidentReceivedEvent::NAME => 'onTopflytechAccidentReceivedEvent',
        ];
    }

    /**
     * @param string $event
     * @return bool
     */
    protected function isEventWas(string $event): bool
    {
        return in_array($event, $this->listenedEvents, true);
    }

    /**
     * @param DeviceUpdatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceUpdated(DeviceUpdatedEvent $event)
    {
        $device = $event->getDevice();
        $this->entityHistoryService->create(
            $device,
            $device->getUpdatedAt() ? $device->getUpdatedAt()->getTimestamp() : Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::DEVICE_UPDATED,
            $device->getUpdatedBy()
        );

        $this->em->flush();
    }

    /**
     * @param DeviceInstalledEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceInstalled(DeviceInstalledEvent $event)
    {
        $device = $event->getDevice();
        $this->entityHistoryService->create(
            $device,
            json_encode($device->getVehicle()?->toArray([
                'type',
                'typeName',
                'make',
                'makeModel',
                'model',
                'regNo',
                'defaultLabel',
                'vin'
            ])),
            EntityHistoryTypes::DEVICE_INSTALLED,
            $device->getUpdatedBy()
        );
        $device->setStatus(Device::STATUS_OFFLINE);

        $this->em->flush();
    }

    /**
     * @param DeviceUninstalledEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceUninstalled(DeviceUninstalledEvent $event)
    {
        if (!$event->getDeviceInstallation()) {
            return;
        }

        $device = $event->getDevice();
        $vehicle = $event->getVehicle();
        $deviceInstallation = $event->getDeviceInstallation();

        $deviceInstallation->setUninstallDate(new \DateTime());
        $device->uninstall();
        $device->setUpdatedAt(new \DateTime());     //for elasticsearch populate
        $device->setUpdatedBy($event->getUser());
        $vehicle->setStatus(Vehicle::STATUS_OFFLINE);
        $vehicle->setUpdatedBy($event->getUser());
        $vehicle->setUpdatedAt(new \DateTime());
        $this->deviceService->clearDeviceCommandsDuringUninstall($device, $vehicle, $deviceInstallation);
        $deviceInstallation->setIsOdometerSynced(true);
        $this->em->flush();
        $this->entityHistoryService->create(
            $device,
            json_encode($vehicle->toArray([
                'type',
                'typeName',
                'make',
                'makeModel',
                'model',
                'regNo',
                'defaultLabel',
                'vin'
            ])),
            EntityHistoryTypes::DEVICE_UNINSTALLED,
            $device->getUpdatedBy()
        );
    }

    /**
     * @param DeviceDeletedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceDeleted(DeviceDeletedEvent $event)
    {
        $device = $event->getDevice();
        $this->entityHistoryService->create(
            $device,
            time(),
            EntityHistoryTypes::DEVICE_DELETED,
            $device->getUpdatedBy()
        );

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_CREATE_DELETE
        );
        $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);

        $this->em->flush();
        $this->updateEntities($device);
    }

    public function onDeviceChangedTeam(DeviceChangedTeamEvent $event)
    {
        $device = $event->getDevice();

        $this->billingEntityHistoryService->onDeviceChangeTeam($device);

        $this->em->flush();
    }

    /**
     * @param DeviceCreatedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceCreated(DeviceCreatedEvent $event)
    {
        $device = $event->getDevice();
        $this->entityHistoryService->create(
            $device,
            $device->getCreatedAt()->getTimestamp(),
            EntityHistoryTypes::DEVICE_CREATED,
            $device->getCreatedBy()
        );

        $this->billingEntityHistoryService->create([
            'entityId' => $device->getId(),
            'entity' => BillingEntityHistory::ENTITY_DEVICE,
            'type' => BillingEntityHistory::TYPE_CREATE_DELETE,
            'dateFrom' => new \DateTime(),
            'team' => $device->getTeam()
        ]);

        if ($device->getStatus()) {
            $this->entityHistoryService->create($device, $device->getStatus(), EntityHistoryTypes::DEVICE_STATUS);
        }

        $this->em->flush();

        $this->updateEntities($device);
    }

    public function onDeviceRestored(DeviceRestoredEvent $event)
    {
        $device = $event->getDevice();

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_ARCHIVE
        );
        $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);

        if ($device->getStatus()) {
            $this->entityHistoryService->create($device, $device->getStatus(), EntityHistoryTypes::DEVICE_STATUS);
        }

        $this->em->flush();

        $this->updateEntities($device);
    }

    public function onDeviceUnavailable(DeviceUnavailableEvent $event)
    {
        $device = $event->getDevice();

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_UNAVAILABLE
        );

        if ($lastBillingEntity) {
            $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);
        } else {
            $this->billingEntityHistoryService->create([
                'entityId' => $device->getId(),
                'entity' => BillingEntityHistory::ENTITY_DEVICE,
                'type' => BillingEntityHistory::TYPE_UNAVAILABLE,
                'dateFrom' => new \DateTime(),
                'team' => $device->getTeam()
            ]);
        }

        $this->entityHistoryService->create(
            $device,
            $device->getIsUnavailable(),
            EntityHistoryTypes::DEVICE_UNAVAILABLE,
            $device->getUpdatedBy()
        );


        $this->em->flush();
    }

    public function onDeviceDeactivated(DeviceDeactivatedEvent $event)
    {
        $device = $event->getDevice();

        $lastBillingEntity = $this->billingEntityHistoryService->getLastRecord(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_DEACTIVATED
        );

        if ($lastBillingEntity) {
            $this->billingEntityHistoryService->update(['dateTo' => new \DateTime()], $lastBillingEntity);
        } else {
            $this->billingEntityHistoryService->create([
                'entityId' => $device->getId(),
                'entity' => BillingEntityHistory::ENTITY_DEVICE,
                'type' => BillingEntityHistory::TYPE_DEACTIVATED,
                'dateFrom' => new \DateTime(),
                'team' => $device->getTeam()
            ]);
        }

        $this->entityHistoryService->create(
            $device,
            $device->getIsDeactivated(),
            EntityHistoryTypes::DEVICE_DEACTIVATED,
            $device->getUpdatedBy()
        );

        $this->em->flush();
    }

    public function onDeviceFinishedReceiveData(DeviceFinishedReceiveDataEvent $event)
    {
        $device = $event->getDevice();
        $trackerHistoryIDs = $event->getTrackerHistoryIDs();
        $this->updateLastDeviceRecordAndStatus($device);
        $this->vehicleService->updateVehicleDriver($device, $trackerHistoryIDs);
        $this->vehicleOdometerService->updateVehicleOdometer($device, $trackerHistoryIDs);

        if ($device->getVehicle()) {
            $vehicle = $device->getVehicle();
            $vehiclePrevStatus = $vehicle->getStatus();
            $vehicle->setStatusByDevice();
            $this->em->flush();

            if ($vehiclePrevStatus !== $vehicle->getStatus()) {
                $this->eventDispatcher->dispatch(new VehicleStatusChangedEvent($vehicle),
                    VehicleStatusChangedEvent::NAME);
            }
        }

        $lastElasticSearchUpdateTs = $this->memoryDb->get(DeviceRedisModel::getLastElasticsearchUpdate($device));
        $nowTs = (new \DateTime())->getTimestamp();

        if (!$lastElasticSearchUpdateTs || ($nowTs - $lastElasticSearchUpdateTs > 600)) {
            $this->memoryDb->set(DeviceRedisModel::getLastElasticsearchUpdate($device), $nowTs);
            $device->setLastDataReceivedAt(new \DateTime());
            $this->deviceObjectPersister->replaceOne($device);

            //update ES for reminders (remaining mileage)
            $reminders = $device->getVehicle()?->getReminders() ?? [];
            foreach ($reminders as $reminder) {
                $this->reminderObjectPersister->replaceOne($reminder);
            }
        }
    }

    private function updateLastDeviceRecordAndStatus(Device $device): void
    {
//        $lastTrackerRecord = $this->getUpdatedLastDeviceRecord($device);
        $lastTrackerRecord = $device->getLastTrackerRecord();

        if ($lastTrackerRecord) {
//            $device->setLastTrackerRecord($lastTrackerRecord);
            $lastDeviceStatus = TrackerHistory::getDeviceStatusByIgnitionAndMovement(
                $lastTrackerRecord->getIgnition(),
                $lastTrackerRecord->getMovement()
            );

            if ($lastDeviceStatus
                && ($lastDeviceStatus != $device->getStatus())
                && $device->isAvailableForEditStatus()
            ) {
                $device->setStatus($lastDeviceStatus);
            }

            $this->updateDeviceStatusExt($device, $lastTrackerRecord);
        }

        $this->em->flush();
    }

    /**
     * @param Device $device
     * @return TrackerHistoryLast|null
     * @throws \Doctrine\ORM\ORMException
     */
//    private function getUpdatedLastDeviceRecord(Device $device): ?TrackerHistoryLast
//    {
//        $trackerHistory = $this->em->getRepository(TrackerHistory::class)->getLastRecordByDevice($device);
//        // @todo there is no real `engineOnTime` for realtime due to SQL without doctrine in `EngineOnTimeService`
//        // $this->em->refresh($trackerHistory);
//
//        if ($trackerHistory) {
//            $lastTrackerHistory = $this->em->getRepository(TrackerHistoryLast::class)
//                ->findOneBy(['device' => $device, 'vehicle' => $device->getVehicle()]);
//
//            if ($lastTrackerHistory) {
//                if ($lastTrackerHistory->getTs() < $trackerHistory->getTs()) {
//                    $lastTrackerHistory->fromTrackerHistory($trackerHistory);
//                }
//            } else {
//                $lastTrackerHistory = new TrackerHistoryLast();
//                $lastTrackerHistory->fromTrackerHistory($trackerHistory);
//                $this->em->persist($lastTrackerHistory);
//                $device->setLastTrackerRecord($lastTrackerHistory);
//            }
//        }
//
//        return $lastTrackerHistory ?? null;
//    }

    /**
     * @param Device $device
     * @param TrackerHistoryLast $lastTrackerRecord
     */
    private function updateDeviceStatusExt(Device $device, TrackerHistoryLast $lastTrackerRecord): void
    {
        $prevStatusExt = $device->getStatusExt();

        if (
            (
                $device->isVendorHasExternalVoltage() && is_null($lastTrackerRecord->getExternalVoltage())
                && $prevStatusExt && $prevStatusExt == Device::STATUS_EXT_NO_POWER_LIMIT
            )
            ||
            (
                !is_null($lastTrackerRecord->getExternalVoltage())
                && $lastTrackerRecord->getExternalVoltage() < Device::STATUS_EXT_NO_POWER_LIMIT
            )
        ) {
            $statusExt = Device::STATUS_EXT_NO_POWER;
        } elseif (
            (
                $device->isVendorHasSatellites() && is_null($lastTrackerRecord->getSatellites())
                && $prevStatusExt && $prevStatusExt == Device::STATUS_EXT_NO_GPS
            )
            ||
            (!is_null($lastTrackerRecord->getSatellites()) && $lastTrackerRecord->getSatellites() <= 0)
        ) {
            $statusExt = Device::STATUS_EXT_NO_GPS;
        } else {
            $statusExt = Device::STATUS_EXT_OK;
        }

        $device->setStatusExt($statusExt);
    }

    /**
     * @param DeviceVoltageEvent $event
     */
    public function onDeviceVoltageEvent(DeviceVoltageEvent $event): void
    {
        if (!$event->getDevice()?->getVehicle()) {
            return;
        }

        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::TRACKER_VOLTAGE)) {
            return;
        }

        foreach ($event->getTrackerHistoryIDs() as $trackerHistoryID) {
            $eventMessage = new DeviceVoltageQueueMessage($event->getDevice(), $trackerHistoryID);
            $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                $event->getDevice()->getId(),
                DeviceVoltageConsumer::QUEUES_NUMBER,
                DeviceVoltageConsumer::ROUTING_KEY_PREFIX
            );
            $this->voltageProducer->publish($eventMessage, $routingKey);
        }
    }

    /**
     * @param DeviceBatteryEvent $event
     */
    public function onDeviceBatteryEvent(DeviceBatteryEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::TRACKER_BATTERY_PERCENTAGE)) {
            return;
        }

        foreach ($event->getTrackerHistoryIDs() as $trackerHistoryID) {
            $eventMessage = new DeviceBatteryQueueMessage($event->getDevice(), $trackerHistoryID);
            $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                $event->getDevice()->getId(),
                DeviceBatteryConsumer::QUEUES_NUMBER,
                DeviceBatteryConsumer::ROUTING_KEY_PREFIX
            );
            $this->batteryProducer->publish($eventMessage, $routingKey);
        }
    }

    /**
     * @param DeviceTowingEvent $event
     */
    public function onDeviceTowingEvent(DeviceTowingEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::VEHICLE_TOWING_EVENT)) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                if ($event->getDevice()?->getVehicle() && DateHelper::getDiffInDaysNow($trackerHistory->getTs()) < 2) {
                    $eventMessage = new DeviceTowingQueueMessage($event->getDevice(), $trackerHistory);
//                    $this->towingProducer->publish($eventMessage);
                    $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                        $event->getDevice()->getId(),
                        DeviceTowingConsumer::QUEUES_NUMBER,
                        DeviceTowingConsumer::ROUTING_KEY_PREFIX
                    );
                    $this->towingProducer->publish($eventMessage, $routingKey);
                }
            }
        }
    }

    /**
     * @param OverSpeedingEvent $event
     * @return void
     * @throws \Exception
     */
    public function onDeviceOverSpeedingEvent(OverSpeedingEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::VEHICLE_OVERSPEEDING)) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                if ($event->getDevice()?->getVehicle() && DateHelper::getDiffInDaysNow($trackerHistory->getTs()) < 2) {
                    $eventMessage = new DeviceOverSpeedingQueueMessage(
                        $event->getDevice(),
                        $trackerHistory
                    );
                    $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                        $event->getDevice()->getId(),
                        DeviceOverSpeedingConsumer::QUEUES_NUMBER,
                        DeviceOverSpeedingConsumer::ROUTING_KEY_PREFIX
                    );
                    $this->overSpeedingProducer->publish($eventMessage, $routingKey);
                }
            }
        }
    }

    /**
     * @param DeviceLongDrivingEvent $event
     * @return void
     * @throws \Exception
     */
    public function onDeviceLongDrivingEvent(DeviceLongDrivingEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::VEHICLE_LONG_DRIVING)) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                if ($event->getDevice()?->getVehicle()) {
                    $eventMessage = new DeviceLongDrivingQueueMessage($event->getDevice(), $trackerHistory);

                    $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                        $event->getDevice()->getId(),
                        DeviceLongDrivingConsumer::QUEUES_NUMBER,
                        DeviceLongDrivingConsumer::ROUTING_KEY_PREFIX
                    );
                    $this->longDrivingProducer->publish($eventMessage, $routingKey);
                }
            }
        }
    }

    /**
     * @param DeviceLongStandingEvent $event
     * @return void
     * @throws \Exception
     */
    public function onDeviceLongStandingEvent(DeviceLongStandingEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::VEHICLE_LONG_STANDING)) {
            return;
        }

        if (!$event->getDevice()?->getVehicle()) {
            return;
        }

        $eventMessage = new DeviceLongStandingQueueMessage(
            $event->getDevice(),
            null,
            $event->getTrackerHistoryData()
        );

        $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
            $event->getDevice()->getId(),
            DeviceLongStandingConsumer::QUEUES_NUMBER,
            DeviceLongStandingConsumer::ROUTING_KEY_PREFIX
        );
        $this->longStandingProducer->publish($eventMessage, $routingKey);
    }

    /**
     * @param DevicePanicButtonEvent $event
     */
    public function onDevicePanicButtonEvent(DevicePanicButtonEvent $event): void
    {
        switch ($event->getSource()) {
            case DevicePanicButtonEvent::SOURCE_DEVICE:
                foreach ($event->getTrackerHistoryData()['data'] as $item) {
                    /** @var Data $record */
                    $record = $item['record'];

                    if ($record instanceof PanicButtonInterface && $record->isPanicButton()) {
                        $eventMessage = new PanicButtonQueueMessage($event->getDevice(), $item['th']);
                        $this->panicButtonProducer->publish($eventMessage);
                    }
                }
                break;
            case DevicePanicButtonEvent::SOURCE_MOBILE:
                foreach ($event->getTrackerHistoryData() as $trackerHistory) {
                    if ($trackerHistory instanceof TrackerHistory) {
                        $eventMessage = new PanicButtonQueueMessage($event->getDevice(), $trackerHistory);
                        $this->panicButtonProducer->publish($eventMessage);
                    }
                }
                break;
        }
    }

    /**
     * @param DeviceEngineOnTimeEvent $event
     * @throws \Exception
     */
    public function onDeviceEngineOnTimeEvent(DeviceEngineOnTimeEvent $event): void
    {
        $eventMessage = new EngineOnTimeQueueMessage(
            $event->getDevice(),
            $event->getTrackerHistoryData(),
            $event->getLastTrackerHistory()
        );
        $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
            $event->getDevice()->getId(),
            EngineOnTimeConsumer::QUEUES_NUMBER,
            EngineOnTimeConsumer::ROUTING_KEY_PREFIX
        );
        $this->engineOnTimeProducer->publish($eventMessage, $routingKey);
    }

    /**
     * @param DeviceIOEvent $event
     * @throws \Exception
     */
    public function onDeviceIOEvent(DeviceIOEvent $event): void
    {
        $device = $event->getDevice();

        if ($device && $device->getVendorName() == DeviceVendor::VENDOR_TOPFLYTECH) {
            $eventMessage = new IOQueueMessage($device, $event->getTrackerHistoryIDs());
            $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                $event->getDevice()->getId(),
                IOConsumer::QUEUES_NUMBER,
                IOConsumer::ROUTING_KEY_PREFIX
            );
            $this->ioProducer->publish($eventMessage, $routingKey);
        }
    }

    protected function updateClient(Client $client)
    {
        $this->clientObjectPersister->replaceOne($client);
    }

    protected function updateReseller(Reseller $reseller)
    {
        $this->resellerObjectPersister->replaceOne($reseller);
    }

    public function updateEntities(Device $device)
    {
        if ($device->getClientEntity()) {
            $this->updateClient($device->getClientEntity());
        }
        if ($device->getTeam()->isResellerTeam()) {
            $this->updateReseller($device->getReseller());
        }
    }

    /**
     * @param DeviceDTCVINReceivedEvent $event
     * @throws \Exception
     */
    public function onTopflytechDTCVINReceivedEvent(DeviceDTCVINReceivedEvent $event): void
    {
        $device = $event->getDevice();
        $vehicle = $device->getVehicle();
        $trackerRecord = $event->getTrackerRecord();
        $DTCVINData = $trackerRecord->getDTCVINData();
        $codes = $DTCVINData->getCodes();
        $trackerPayload = $event->getTrackerPayload();
        $occurredAt = $trackerRecord->getDateTime();
        $trackerHistoryDTCVINIds = [];

        if (!$codes) {
            return;
        }
        if ($trackerRecord->isVINData()) {
            $VINCode = reset($codes);

            if ($VINCode && $vehicle && $vehicle->getVin() != $VINCode) {
                $vehicle->setVin($VINCode);
                $this->em->flush();
            }

            return;
        }

        foreach ($codes as $code) {
            $trackerHistoryDTCVIN = new TrackerHistoryDTCVIN();
            $trackerHistoryDTCVIN->setVehicle($vehicle);
            $trackerHistoryDTCVIN->setDevice($device);
            $trackerHistoryDTCVIN->setCode($code);
            $trackerHistoryDTCVIN->setTrackerPayload($trackerPayload);
            $trackerHistoryDTCVIN->setOccurredAt($occurredAt);
            $trackerHistoryDTCVIN->setData($DTCVINData->getDataArray());

            $this->em->persist($trackerHistoryDTCVIN);
            $this->em->flush();
            $trackerHistoryDTCVINIds[] = $trackerHistoryDTCVIN->getId();
        }

        $event->setTrackerHistoryDTCVINIdsForEvents($trackerHistoryDTCVINIds);
    }

    /**
     * @param DeviceDrivingBehaviorReceivedEvent $event
     * @throws \Exception
     */
    public function onDrivingBehaviorReceivedEvent(DeviceDrivingBehaviorReceivedEvent $event): void
    {
        $trackerRecord = $event->getTrackerRecord();
        /** @var Data $data */
        $driverBehaviorData = $trackerRecord->getDriverBehaviorData();
        $device = $event->getDevice();
        $trackerPayload = $event->getTrackerPayload();
        $vehicle = $device ? $device->getVehicle() : null;
        $driver = $vehicle ? $vehicle->getDriver() : null;
        $occurredAt = $trackerRecord->getDateTime();
        $drivingBehaviorIds = [];

        if (!$device || !$driverBehaviorData) {
            return;
        }

        $drivingBehavior = TopflytechTrackerService::mapDrivingBehavior($driverBehaviorData->getBehaviorType());

        if ($drivingBehavior) {
            $drivingBehavior->setDevice($device);
            $drivingBehavior->setVehicle($vehicle);
            $drivingBehavior->setDriver($driver);
            $drivingBehavior->setTs($occurredAt);
            $drivingBehavior->setTrackerPayload($trackerPayload);
            $ltr = $device->getLastTrackerRecord();

            if ($ltr) {
                $drivingBehavior->setLat($ltr->getLat());
                $drivingBehavior->setLng($ltr->getLng());
                $drivingBehavior->setSpeed($ltr->getSpeed());
                $drivingBehavior->setOdometer($ltr->getOdometer());
            }

            $this->em->persist($drivingBehavior);
            $this->em->flush();
            $drivingBehaviorIds[] = $drivingBehavior->getId();
        }

        $event->setDrivingBehaviorIdsForEvents($drivingBehaviorIds);
    }


    /**
     * @param DeviceMovingWithoutDriverEvent $event
     * @throws \Exception
     */
    public function onDeviceMovingWithoutDriverEvent(DeviceMovingWithoutDriverEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::VEHICLE_DRIVING_WITHOUT_DRIVER)) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                if ($event->getDevice()?->getVehicle()) {
                    $eventMessage = new MovingWithoutDriverMessage($event->getDevice(), $trackerHistory);

                    $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                        $event->getDevice()->getId(),
                        MovingWithoutDriverConsumer::QUEUES_NUMBER,
                        MovingWithoutDriverConsumer::ROUTING_KEY_PREFIX
                    );
                    $this->movingWithoutDriverProducer->publish($eventMessage, $routingKey);
                }
            }
        }
    }

    /**
     * @param DeviceMovingEvent $event
     * @return void
     * @throws \Exception
     */
    public function onDeviceMovingEvent(DeviceMovingEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::VEHICLE_MOVING)) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                if ($event->getDevice()->getVehicle()) {
                    $eventMessage = new DeviceMovingQueueMessage(
                        $event->getDevice(),
                        $trackerHistory
                    );
                    $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                        $event->getDevice()->getId(),
                        DeviceMovingConsumer::QUEUES_NUMBER,
                        DeviceMovingConsumer::ROUTING_KEY_PREFIX
                    );
                    $this->movingProducer->publish($eventMessage, $routingKey);
                }
            }
        }
    }

    /**
     * @param DeviceNetworkReceivedEvent $event
     * @throws \Exception
     */
    public function onDeviceNetworkReceivedEvent(DeviceNetworkReceivedEvent $event): void
    {
        $device = $event->getDevice();
        $trackerRecord = $event->getTrackerRecord();
        $networkData = $trackerRecord->getNetworkData();

        if (!$device || !$networkData) {
            return;
        }

        $device->setImsi($networkData->getIMSI());
        $device->setIccid($networkData->getICCID());
        $this->em->flush();
    }

    public function onDeviceContractChanged(DeviceContractChangedEvent $event)
    {
        $device = $event->getDevice();

        $this->entityHistoryService->create(
            $device,
            DateHelper::formatDate($device->getContractFinishAt()) ?? '',
            EntityHistoryTypes::DEVICE_CONTRACT_CHANGED,
            $device->getUpdatedBy()
        );

        $this->em->flush();
    }

    public function onDevicePhoneChanged(DevicePhoneChangedEvent $event)
    {
        $device = $event->getDevice();

        $this->entityHistoryService->create(
            $device,
            $device->getPhone(),
            EntityHistoryTypes::DEVICE_PHONE_CHANGED,
            $device->getUpdatedBy()
        );

        $this->em->flush();
    }

    public function onDeviceTodayDataEvent(DeviceTodayDataEvent $event): void
    {
        if (!$event->getDevice()?->getVehicle()) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                $eventMessage = new TodayDataQueueMessage($event->getDevice()->getVehicle(), $trackerHistory);
                $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                    $event->getDevice()->getId(),
                    TodayDataConsumer::QUEUES_NUMBER,
                    TodayDataConsumer::ROUTING_KEY_PREFIX
                );
                $this->todayDataProducer->publish($eventMessage, $routingKey);
            }
        }
    }

    /**
     * @param DeviceJammerReceivedEvent $event
     * @throws \Exception
     */
    public function onTopflytechJammerReceivedEvent(DeviceJammerReceivedEvent $event): void
    {
        $device = $event->getDevice();

        if ($device->getVendorName() !== DeviceVendor::VENDOR_TOPFLYTECH) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            /** @var DeviceDataInterface $record */
            $record = $item['record'];
            /** @var TrackerHistory $trackerHistory */
            $trackerHistory = $item['th'];

            if ($record->isJammerAlarmStarted()) {
                $this->notificationDispatcher->dispatch(
                    Event::TRACKER_JAMMER_STARTED_ALARM,
                    $trackerHistory,
                    $trackerHistory->getTs()
                );
            }

            // @todo uncomment logic if we need historical jammer data (on/off), wait for verification on prod
//            if ($record->getJammerData() && !$device->jammerRecordExistsForDevice($record->getDateTime())) {
//                $isJammerStarted = $record->isJammerAlarmStarted();
//                $trackerHistoryJammer = new TrackerHistoryJammer();
//                $trackerHistoryJammer->fromTrackerHistory($trackerHistory);
//            }
        }
    }

    /**
     * @param EngineHistoryEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEngineHistoryEvent(EngineHistoryEvent $event): void
    {
        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;
            $vehicle = $event->getDevice()->getVehicle();

            if ($trackerHistory instanceof TrackerHistory && $vehicle) {
                $eventMessage = new EngineHistoryQueueMessage($vehicle, $trackerHistory);
                $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                    $event->getDevice()->getId(),
                    EngineHistoryConsumer::QUEUES_NUMBER,
                    EngineHistoryConsumer::ROUTING_KEY_PREFIX
                );
                $this->trackerEngineHistoryProducer->publish($eventMessage, $routingKey);
            }
        }
    }

    /**
     * @param DeviceAccidentReceivedEvent $event
     * @throws \Exception
     */
    public function onTopflytechAccidentReceivedEvent(DeviceAccidentReceivedEvent $event): void
    {
        $device = $event->getDevice();

        if ($device->getVendorName() !== DeviceVendor::VENDOR_TOPFLYTECH) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            /** @var DeviceDataInterface $record */
            $record = $item['record'];
            /** @var TrackerHistory $trackerHistory */
            $trackerHistory = $item['th'];

            if ($record->isAccidentHappened()) {
                $this->notificationDispatcher->dispatch(
                    Event::TRACKER_ACCIDENT_HAPPENED_ALARM,
                    $trackerHistory,
                    $trackerHistory->getTs()
                );
            }
        }
    }

    public function onDeviceExceedingSpeedLimitEvent(DeviceExceedingSpeedLimitEvent $event): void
    {
        if (!$this->hasNotifications($event->getDevice()->getTeam(), Event::EXCEEDING_SPEED_LIMIT)) {
            return;
        }

        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            $trackerHistory = $item['th'] ?? null;

            if ($trackerHistory instanceof TrackerHistory) {
                if ($event->getDevice()?->getVehicle()) {
                    $eventMessage = new DeviceExceedingSpeedLimitQueueMessage(
                        $event->getDevice(),
                        $trackerHistory
                    );
                    $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                        $event->getDevice()->getId(),
                        DeviceExceedingSpeedLimitConsumer::QUEUES_NUMBER,
                        DeviceExceedingSpeedLimitConsumer::ROUTING_KEY_PREFIX
                    );
                    $this->exceedingSpeedLimitProducer->publish($eventMessage, $routingKey);
                }
            }
        }
    }

    private function hasNotifications(Team $team, string $event): bool
    {
        $ntfEvent = $this->em->getRepository(Event::class)
            ->getEventByName($event);
        $ntfCount = $this->em->getRepository(Notification::class)
            ->getNtfCountByTeamAndEvent($team, $ntfEvent);

        return (bool)$ntfCount;
    }
}
