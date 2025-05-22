<?php

namespace App\EventListener\DeviceSensor;

use App\Entity\Device;
use App\Entity\DeviceSensor;
use App\Entity\DeviceSensorHistory;
use App\Entity\DeviceSensorType;
use App\Entity\Sensor;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\Device\DeviceTempAndHumiditySensorIdReceivedEvent;
use App\Events\DeviceSensor\DeviceSensorInstalledEvent;
use App\Events\DeviceSensor\DeviceSensorUninstalledEvent;
use App\Events\Sensor\SensorCreatedEvent;
use App\Events\User\Driver\DriverFOBIdReceivedEvent;
use App\Events\User\Driver\DriverSensorIdReceivedEvent;
use App\Exceptions\ValidationException;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\TemperatureAndHumiditySensor;
use App\Service\Vehicle\VehicleService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceSensorListener implements EventSubscriberInterface
{
    private $listenedEvents = [];
    private $em;
    private $entityHistoryService;
    private $vehicleService;
    private $eventDispatcher;
    private $notificationDispatcher;
    private $translator;
    private $logger;

    /**
     * DeviceListener constructor.
     * @param EntityManager $em
     * @param EntityHistoryService $entityHistoryService
     * @param VehicleService $vehicleService
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManager $em,
        EntityHistoryService $entityHistoryService,
        VehicleService $vehicleService,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->entityHistoryService = $entityHistoryService;
        $this->vehicleService = $vehicleService;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @param TemperatureAndHumiditySensor $tempAndHumiditySensor
     * @param Device $device
     * @return Sensor|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    private function handleSensor(
        TemperatureAndHumiditySensor $tempAndHumiditySensor,
        Device $device
    ): ?Sensor {
        $sensor = $this->em->getRepository(Sensor::class)->getBySensorId($tempAndHumiditySensor->getBLESensorId());

        if ($sensor) {
            if (!$sensor->getDeviceSensors()->isEmpty()) {
                if ($sensor->getLastDeviceSensorWithoutCondition()->getTeamId() != $device->getTeamId()) {
                    $this->logger->error(sprintf(
                        'Sensor ID: %s with team ID: %s installed on device ID: %s from another team ID: %s',
                        $sensor->getId(),
                        $sensor->getTeamId(),
                        $device->getId(),
                        $device->getTeamId()
                    ));
                    // @todo
//                    $this->notificationDispatcher->dispatch(Event::DEVICE_SENSOR_WRONG_TEAM, $device);

                    return null;
                }
            } else {
                $sensor->setTeam($device->getTeam());
            }
        }

        return $sensor;
    }

    /**
     * @param Device $device
     * @param Sensor $sensor
     * @return DeviceSensor
     * @throws \Doctrine\ORM\ORMException
     */
    private function handleDeviceSensor(
        Device $device,
        Sensor $sensor
    ): DeviceSensor {
        $deviceSensor = $this->em->getRepository(DeviceSensor::class)->getBySensorAndDevice($sensor, $device);

        if (!$deviceSensor) {
            $deviceSensor = new DeviceSensor();
            $deviceSensor->setDevice($device);
            $deviceSensor->setSensor($sensor);
            $deviceSensor->setTeam($device->getTeam());
            $device->addTrackerSensor($deviceSensor);
            $this->em->persist($deviceSensor);
            // @todo remove flush below if revert transaction in TopflytechTrackerService::handleDeviceExtraData
            $this->em->flush();
            $this->eventDispatcher
                ->dispatch(new DeviceSensorInstalledEvent($deviceSensor), DeviceSensorInstalledEvent::NAME);
            // @todo add notification `new device sensor has been added`?
        }

        // @todo try if case without transaction is bad
//        $this->em->lock($deviceSensor, LockMode::PESSIMISTIC_WRITE);

        return $deviceSensor;
    }

    /**
     * @param Device $device
     * @param DeviceSensor $deviceSensor
     * @param TrackerPayload $trackerPayload
     * @param DeviceDataInterface $trackerRecord
     * @param TemperatureAndHumiditySensor $tempAndHumiditySensor
     * @return TrackerHistorySensor
     * @throws \Doctrine\ORM\ORMException
     */
    private function handleTrackerHistorySensor(
        Device $device,
        DeviceSensor $deviceSensor,
        TrackerPayload $trackerPayload,
        DeviceDataInterface $trackerRecord,
        TemperatureAndHumiditySensor $tempAndHumiditySensor
    ): TrackerHistorySensor {
        $trackerHistorySensor = new TrackerHistorySensor();
        $trackerHistorySensor->setDevice($device);
        $trackerHistorySensor->setTeam($device->getTeam());
        $trackerHistorySensor->setVehicle($device->getVehicle());
        $trackerHistorySensor->setDeviceSensor($deviceSensor);
        $trackerHistorySensor->setTrackerPayload($trackerPayload);
        $trackerHistorySensor->setOccurredAt($trackerRecord->getDateTime());
        $trackerHistorySensor->setData($tempAndHumiditySensor->getDataArray());
        $trackerHistorySensor->setIsNullableData($tempAndHumiditySensor->isNullableData());
        $this->em->persist($trackerHistorySensor);
        // @todo remove flush below if revert transaction in TopflytechTrackerService::handleDeviceExtraData
        $this->em->flush();

        return $trackerHistorySensor;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DeviceSensorInstalledEvent::NAME => 'onDeviceSensorInstalled',
            DeviceSensorUninstalledEvent::NAME => 'onDeviceSensorUninstalled',
            DriverSensorIdReceivedEvent::NAME => 'onDriverSensorIdReceivedEvent',
            DeviceTempAndHumiditySensorIdReceivedEvent::NAME => 'onTopflytechTempAndHumiditySensorIdReceivedEvent',
            DriverFOBIdReceivedEvent::NAME => 'onDriverFOBIdReceivedEvent',
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
     * @param DeviceSensorInstalledEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceSensorInstalled(DeviceSensorInstalledEvent $event)
    {
        $deviceSensor = $event->getDeviceSensor();
        $this->logger->info('before onDeviceSensorInstalled', ['device' => $deviceSensor->getDevice()->getId(), 'deviceSensor' => $deviceSensor->getId()]);
        $this->entityHistoryService->create(
            $deviceSensor,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::DEVICE_SENSOR_INSTALLED,
            $deviceSensor->getUpdatedBy()
        );
        $this->logger->info('after $this->entityHistoryService->create()', ['device' => $deviceSensor->getDevice()->getId(), 'deviceSensor' => $deviceSensor->getId()]);
        $deviceSensorHistory = new DeviceSensorHistory();
        $deviceSensorHistory->setDevice($deviceSensor->getDevice())
            ->setSensor($deviceSensor->getSensor());
        $this->em->persist($deviceSensorHistory);
        $this->em->flush();
        $this->logger->info('after onDeviceSensorInstalled', ['device' => $deviceSensor->getDevice()->getId(), 'deviceSensor' => $deviceSensor->getId()]);
    }

    /**
     * @param DeviceSensorUninstalledEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onDeviceSensorUninstalled(DeviceSensorUninstalledEvent $event)
    {
        $deviceSensor = $event->getDeviceSensor();
        $this->entityHistoryService->create(
            $deviceSensor,
            Carbon::now('UTC')->getTimestamp(),
            EntityHistoryTypes::DEVICE_SENSOR_UNINSTALLED,
            $deviceSensor->getUpdatedBy()
        );
        $deviceSensorHistory = $this->em->getRepository(DeviceSensorHistory::class)
            ->getLastBySensorAndDevice($deviceSensor->getSensor(), $deviceSensor->getDevice());

        if ($deviceSensorHistory) {
            $deviceSensorHistory->setUninstalledAt(new \DateTime());
        }

        $this->em->flush();
    }

    public function onDriverSensorIdReceivedEvent(DriverSensorIdReceivedEvent $event): void
    {
        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            /** @var DeviceDataInterface $record */
            $record = $item['record'];

            if ($record->getDriverIdTag()) {
                $driverSensorId = $record->getDriverIdTag();
                $device = $event->getDevice();
                $vehicle = $device?->getVehicle();
                $driver = $this->em->getRepository(User::class)->getByDriverSensorId($driverSensorId);

                if (!$device || !$vehicle || !$driver) {
                    return;
                }

                if ($vehicle->getDriver() != $driver) {
                    if (!$driver->isVehicleInUserGroups($vehicle)) {
                        // @todo implement notification `DRIVER_ASSIGNMENT_VIOLATION`
                        // $this->notificationDispatcher->dispatch(Event::DRIVER_ASSIGNMENT_VIOLATION, $device);
                        return;
                    }

                    try {
                        $this->vehicleService->setVehicleDriver($vehicle, $driver, $record->getDateTime());
                    } catch (ValidationException $e) {
                        $e->addErrors(['Vehicle ID' => $vehicle?->getId(), 'Driver ID' => $driver?->getId()]);
                        $this->logger->error($e->getImplodedErrorsMessage());
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
            }
        }
    }

    public function onDriverFOBIdReceivedEvent(DriverFOBIdReceivedEvent $event): void
    {
        foreach ($event->getTrackerHistoryData()['data'] as $item) {
            /** @var DeviceDataInterface $record */
            $record = $item['record'];

            if ($record->getDriverFOBId()) {
                $driverFOBId = $record->getDriverFOBId();
                $device = $event->getDevice();
                $vehicle = $device?->getVehicle();
                $driver = $this->em->getRepository(User::class)->getByDriverFOBId($driverFOBId);

                if (!$device || !$vehicle || !$driver) {
                    return;
                }

                if ($vehicle->getDriver() != $driver) {
                    try {
                        $this->vehicleService->setVehicleDriver($vehicle, $driver, $record->getDateTime());
                    } catch (ValidationException $e) {
                        $e->addErrors(['Vehicle ID' => $vehicle?->getId(), 'Driver ID' => $driver?->getId()]);
                        $this->logger->error($e->getImplodedErrorsMessage());
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @param DeviceTempAndHumiditySensorIdReceivedEvent $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onTopflytechTempAndHumiditySensorIdReceivedEvent(
        DeviceTempAndHumiditySensorIdReceivedEvent $event
    ): void {
        $trackerRecord = $event->getTrackerRecord();
        $tempAndHumiditySensorData = $trackerRecord->getTempAndHumidityData();
        $device = $event->getDevice();
        $trackerPayload = $event->getTrackerPayload();
        $occurredAt = $trackerRecord->getDateTime();

        if (!$device) {
            return;
        }

        $trackerHistorySensorIds = [];

        /** @var TemperatureAndHumiditySensor $tempAndHumiditySensor */
        foreach ($tempAndHumiditySensorData as $tempAndHumiditySensor) {
            $sensor = $this->handleSensor($tempAndHumiditySensor, $device);

            if (!$sensor) {
                continue;
            }

            if ($tempAndHumiditySensor->getRSSI() && $sensor->hasStrongerDeviceSensorByRSSI(
                    $tempAndHumiditySensor->getRSSI(),
                    $trackerRecord->getDateTime(),
                    $device
                )
            ) {
                continue;
            }

            $prevSensorStatus = $sensor->getStatus();
            $prevLastOccurredAt = $sensor->getLastOccurredAt();
            $deviceSensor = $this->handleDeviceSensor($device, $sensor);

            if ($deviceSensor->isDeleted()) {
                continue;
            }

            $trackerHistorySensor = $this->handleTrackerHistorySensor(
                $device,
                $deviceSensor,
                $trackerPayload,
                $trackerRecord,
                $tempAndHumiditySensor
            );

            if (($occurredAt > $deviceSensor->getOccurredAt()) && !$trackerHistorySensor->isNullableData()) {
                $deviceSensor->setLastTrackerHistorySensor($trackerHistorySensor);
                $deviceSensor->setLastOccurredAt($trackerHistorySensor->getOccurredAt());
                $deviceSensor->setRSSI($trackerHistorySensor->getRSSI());
            }

//            $this->em->flush();

            if (($occurredAt > $prevLastOccurredAt) && !$trackerHistorySensor->isNullableData()) {
                $status = ($occurredAt >= (new Carbon())->subRealSeconds(BaseBLE::RSSI_ACCURACY_TIME))
                    ? DeviceSensor::STATUS_ONLINE
                    : DeviceSensor::STATUS_OFFLINE;
                $deviceSensor->setStatus($status);
//                $this->em->flush();

                $this->triggerDeviceSensorStatusEvent($event, $prevSensorStatus, $sensor, $trackerHistorySensor);
            }

            $trackerHistorySensorIds[] = $trackerHistorySensor->getId();
            $this->em->flush();
        }

        $event->setTrackerHistorySensorIdForEvents($trackerHistorySensorIds);
    }

    private function triggerDeviceSensorStatusEvent(
        DeviceTempAndHumiditySensorIdReceivedEvent $event,
        $prevSensorStatus,
        Sensor $sensor,
        TrackerHistorySensor $trackerHistorySensor
    ) {
        if (!is_null($prevSensorStatus) && $prevSensorStatus != $sensor->getStatus()) {
            $event->setTrackerHistorySensorsForStatusEvent($trackerHistorySensor);
        }
    }
}