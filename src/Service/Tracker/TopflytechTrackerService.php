<?php

namespace App\Service\Tracker;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\DrivingBehavior;
use App\Entity\Notification\Event;
use App\Entity\Tracker\TrackerAuth;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\User;
use App\Events\Device\DeviceDrivingBehaviorReceivedEvent;
use App\Events\Device\DeviceDTCVINReceivedEvent;
use App\Events\Device\DeviceNetworkReceivedEvent;
use App\Events\Device\DeviceTempAndHumiditySensorIdReceivedEvent;
use App\Events\User\Driver\DriverFOBIdReceivedEvent;
use App\Events\User\Driver\DriverSensorIdReceivedEvent;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\Device\DeviceSensorQueue\DeviceSensorQueueMessage;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\DriverBehaviorBase;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;
use App\Service\Tracker\Parser\Topflytech\TcpEncoder;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @todo add driving behavior service?
 */
class TopflytechTrackerService extends TrackerService
{
    public const NEW_SENSOR_HISTORY_EVENT_NAME = 'newSensorHistory';
    public const NEW_DTC_VIN_HISTORY_EVENT_NAME = 'newDTCVINHistory';
    public const NEW_DRIVING_BEHAVIOR_EVENT_NAME = 'newDrivingBehavior';

    public $em;
    public $eventDispatcher;
    public $notificationDispatcher;
    public $logger;
    public $sensorEventProducer;

    /**
     * @param array $events
     * @throws Exception
     */
    private function triggerDeviceExtraEvents(array $events = [])
    {
        foreach ($events as $event) {
            switch (true) {
                case $event instanceof DeviceTempAndHumiditySensorIdReceivedEvent:
                    $this->triggerDeviceSensorEvent($event);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param DeviceTempAndHumiditySensorIdReceivedEvent $event
     * @throws Exception
     */
    private function triggerDeviceSensorEvent(DeviceTempAndHumiditySensorIdReceivedEvent $event)
    {
        foreach ($event->getTrackerHistorySensorIdForEvents() as $trackerHistorySensorId) {
            $eventMessage = new DeviceSensorQueueMessage($event->getDevice(), $trackerHistorySensorId);
            $this->sensorEventProducer->publish($eventMessage);
        }

        $eventStatus = $this->em->getRepository(Event::class)->findOneBy(['name' => Event::SENSOR_STATUS]);
        foreach ($event->getTrackerHistorySensorsForEvents() as $trackerHistorySensor) {
            $this->notificationDispatcher->dispatch(
                $eventStatus->getName(),
                $trackerHistorySensor,
                new \DateTime()
            );
        }
    }

    /**
     * @param string $payload
     * @param TcpDecoder $decoder
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function updateDeviceInfoByLoginMessage(string $payload, TcpDecoder $decoder)
    {
        $loginMessage = $decoder->decodeLoginMessage($payload);
        $imei = $loginMessage->getImei();
        $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);

        if ($device) {
            $HWVersion = $loginMessage->getHWVersion();
            $FWVersion = $loginMessage->getFWVersion();
            $protocol = $decoder->getProtocol($payload);

            if ($HWVersion && $HWVersion != $device->getHw()) {
                $device->setHw($HWVersion);
            }
            if ($FWVersion && $FWVersion != $device->getSw()) {
                $device->setSw($FWVersion);
            }
            if ($protocol && $protocol != $device->getProtocol()) {
                $device->setProtocol($protocol);
            }

            $this->em->flush();
        }
    }

    /**
     * @return float|int|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @todo it's not used, for future to avoid issue with odometer's reset
     */
    private function getOdometerValue(Device $device, Data $record)
    {
        // value by doc 4kkk - greater than real 2kkk
        $odometerValue = $record->getOdometer();
        $dateTime = $record->getDateTime();
        $prevTrackerRecord = $device->getLastTrackerRecord();
        $lastOdometer = $prevTrackerRecord ? $prevTrackerRecord->getOdometer() : null;

        if ($prevTrackerRecord && $dateTime) {
            if ($prevTrackerRecord->getTs() > $dateTime) {
                $prevTrackerRecord = $this->em->getRepository(TrackerHistory::class)
                    ->getPreviousTrackerHistoryByDeviceAndDate($device, $dateTime);

                if ($prevTrackerRecord) {
                    $lastOdometer = $prevTrackerRecord->getOdometer();
                }
            }

            if ($prevTrackerRecord->getTs() < $dateTime
                && $lastOdometer && $lastOdometer > Data::ODOMETER_LIMIT_MAX && $odometerValue
            ) {
                if (abs($lastOdometer - $odometerValue) > Data::ODOMETER_LIMIT_MIN) {
                    $odometerValue = $lastOdometer + $record->getOdometer();
                } else {
                    // @todo: add new field `odometer_from_device` and compare with calculated value. Check entire logic
                }
            }
        }

        return $odometerValue;
    }

    /**
     * @param string $protocol
     * @return string
     */
    private function getDefaultModelNameByProtocol(string $protocol): string
    {
        return match ($protocol) {
            TcpDecoder::PROTOCOL_TLD1DADE => DeviceModel::TOPFLYTECH_TLD1_DA_DE,
            TcpDecoder::PROTOCOL_TLP1 => DeviceModel::TOPFLYTECH_TLP1_LF,
            default => DeviceModel::TOPFLYTECH_TLD1_A_E,
        };
    }

    private function resolveDataBySaveMode(
        ?Device $device,
        string $payload,
        TcpDecoder $decoder,
        string $imei
    ): array {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD
            => $this->handleDeviceDataOnlyPayload(null, $device, $payload, $decoder, $imei),
            default => $this->handleDeviceData(null, $device, $payload, $decoder, $imei),
        };
    }

    private function resolveExtraDataBySaveMode(
        ?Device $device,
        string $payload,
        TcpDecoder $decoder,
        string $imei
    ): array {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD =>
            $this->handleDeviceExtraDataOnlyPayload(null, $device, $payload, $decoder, $imei),
            default => $this->handleDeviceExtraData(null, $device, $payload, $decoder, $imei),
        };
    }

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        Producer $sensorEventProducer,
        EngineOnTimeService $engineOnTimeService,
        MemoryDbService $memoryDb,
        BillingEntityHistoryService $billingEntityHistoryService
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->sensorEventProducer = $sensorEventProducer;
        $this->engineOnTimeService = $engineOnTimeService;
        $this->memoryDb = $memoryDb;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
    }

    /**
     * @inheritDoc
     */
    public function parseFromTcp(
        mixed $payload,
        ?string $socketId = null,
        ?string $imei = null
    ) {
        $decoder = new TcpDecoder();
        $imei = $decoder->getImei($payload);

        if ($decoder->isAuthentication($payload)) {
            if ($decoder->isLoginMessage($payload)) {
                $this->updateDeviceInfoByLoginMessage($payload, $decoder);
            }

            $response = $this->authorizeDevice($payload, $socketId, $decoder);
        } elseif ($decoder->isRequestTypeWithCorrectData($payload)) {
            $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);
            $response = $this->resolveDataBySaveMode($device, $payload, $decoder, $imei);
        } elseif ($decoder->isCommandRequest($payload)) {
            $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);
            $response = $this->handleCommandData($device, $payload, $imei);
        } elseif ($decoder->isRequestTypeWithExtraData($payload)) {
            $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);
            $response = $this->resolveExtraDataBySaveMode($device, $payload, $decoder, $imei);
        } else {
            $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);

            if ($device) {
                $this->updateDeviceLastDataReceivedAt($device);
            }

            $this->saveUnknownPayload($payload, $imei);
            $response = [
                'imei' => $imei,
                'response' => $this->getDataResponse([], $payload, $device),
            ];
        }

        return $response;
    }

    public function saveHistoryFromRecord(
        DeviceDataInterface $record,
        TrackerPayload $trackerPayload,
        Device $device,
        ?TrackerHistory $prevTh
    ): TrackerHistory {
        /** @var Data $record */
        $gpsData = $record->getGpsData();
        $speed = !is_null($record->getSpeed()) ? $record->getSpeed() : $gpsData->getSpeed();

        $trackerHistory = new TrackerHistory();
        $trackerHistory->setTrackerPayload($trackerPayload);
        $trackerHistory->setDevice($device);
        $trackerHistory->setTeam($device->getTeam());
        $trackerHistory->setTs($record->getDateTime());
        $trackerHistory->setLat($gpsData->getLatitude());
        $trackerHistory->setLng($gpsData->getLongitude());
        $trackerHistory->setAlt($gpsData->getAltitude());
        $trackerHistory->setVehicle($device->getVehicle());
        $trackerHistory->setDriver($device->getVehicle()?->getDriver());
        $trackerHistory->setAngle($gpsData->getAngle());
        $trackerHistory->setSpeed($speed);
        $trackerHistory->setMovement($record->getMovement());
        $ignition = ($device->isFixWithSpeed() && ($speed > 0)) ? 1 : $record->getIgnition();
        $trackerHistory->setIgnition($ignition);
        $trackerHistory->setBatteryVoltage($record->getBatteryVoltageMilli());
        $trackerHistory->setBatteryVoltagePercentage($record->getBatteryVoltagePercentage());
        $trackerHistory->setSolarChargingStatus($record->getSolarChargingStatus());
        $trackerHistory->setOdometer(
            $record->getOdometer(),
            $device->getLastTrackerRecord(),
            $device->getVendorName()
        );
        $trackerHistory->setExternalVoltage($record->getExternalVoltageMilli());
        $trackerHistory->setTemperatureLevel($record->getDeviceTemperatureMilli());
        $trackerHistory->setEngineOnTime($record->getEngineOnTime());
        $trackerHistory->setIButton($record->getDriverIdTag());
        $trackerHistory->setOBDExtraData($record->getOBDData());
        $trackerHistory->setIOExtraData($record->getIODataArray());
        $trackerHistory->setBLEDriverSensorExtraData($record->getBLEDriverSensorData());
        $trackerHistory->setBLETempAndHumidityExtraData($record->getTempAndHumidityData());
        $trackerHistory->setBLESOSExtraData($record->getBLESOSData());
        $trackerHistory->setAlarmExtraData($record->getAlarmTypeData());
        $trackerHistory->setIsSOSButton($record->isPanicButton());
        $trackerHistory->setIsJammerAlarm($record->isJammerAlarm() ? ($record->isJammerAlarmStarted() ? 1 : 0) : null);
        $trackerHistory->setSatellites($record->getSatellites());
        $trackerHistory->setAccidentHappened(
            $record->getAccidentData() ? ($record->isAccidentHappened() ? 1 : 0) : null
        );

        $this->em->persist($trackerHistory);
//        $this->em->flush();

        return $trackerHistory;
    }

    /**
     * @inheritDoc
     */
    public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        TrackerHistory $trackerHistory,
        Device $device
    ): void {
        /** @var DriverBehaviorBase $driverBehaviorData */
        $driverBehaviorData = $data->getDriverBehaviorData();

        if ($driverBehaviorData) {
            $drivingBehavior = self::mapDrivingBehavior($driverBehaviorData->getBehaviorType());

            if ($drivingBehavior) {
                $drivingBehavior->fromTrackerHistory($trackerHistory);
                $drivingBehavior = $this->updateDrivingBehaviorWithCorrectCoordinates($device, $drivingBehavior);

                $this->em->persist($drivingBehavior);
                $this->em->flush();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAuthResponse(bool $success, $data)
    {
        $encoder = new TcpEncoder();

        return $encoder->encodeAuthentication($success, $data);
    }

    /**
     * @inheritDoc
     */
    public function getDataResponse($data, $textPayload, ?Device $device = null)
    {
        $encoder = new TcpEncoder();

        return $encoder->encodeData($textPayload, $device);
    }

    /**
     * @inheritDoc
     */
    public function parseFromSms(string $payloadFromDevice)
    {
        // TODO: Implement parseFromSms() method.
    }

    /**
     * @inheritDoc
     */
    public function getQueryDataForLogs(Device $device, $dateFrom, $dateTo)
    {
        return $this->em->getRepository(TrackerHistory::class)
            ->getQueryDataForTopflytechLogs($device, $dateFrom, $dateTo);
    }

    /**
     * @inheritDoc
     */
    public function formatTrackerDataLog(PaginationInterface $pagination, Device $device): array
    {
        $data = [];

        foreach ($pagination as $key => $dbDatum) {
            $data[] = [
                'ts' => DateHelper::formatDate($dbDatum['ts']),
                'odometer' => isset($dbDatum['odometer']) ? round($dbDatum['odometer'] / 1000, 1) : null,
                'movement' => $dbDatum['movement'],
                'ignition' => $dbDatum['ignition'],
                'createdAt' => DateHelper::formatDate($dbDatum['createdAt']),
                'gpsData' => [
                    'speed' => $dbDatum['speed'],
                    'angle' => $dbDatum['angle'],
                    'coordinates' => [
                        'lat' => $dbDatum['lat'],
                        'lng' => $dbDatum['lng']
                    ],
                ],
                'extraData' => $dbDatum['extraData'],
                'payload' => $dbDatum['payload'],
                'batteryVoltage' => isset($dbDatum['batteryVoltage']) && (floor($dbDatum['batteryVoltage']) > 100)
                    ? round($dbDatum['batteryVoltage'] / 1000, 2)
                    : $dbDatum['batteryVoltage'],
                'batteryVoltagePercentage' => $dbDatum['batteryVoltagePercentage'],
                'temperatureLevel' => isset($dbDatum['temperatureLevel']) ? $dbDatum['temperatureLevel'] / 1000 : null,
                'externalVoltage' => isset($dbDatum['externalVoltage'])
                    ? round($dbDatum['externalVoltage'] / 1000, 2)
                    : null,
            ];
        }

        return $data;
    }

    /**
     * @param string $socketId
     * @param string|null $payload
     * @param string|null $textPayload
     * @param string|null $imei
     * @return TrackerAuth|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTrackerAuth(
        string $socketId,
        ?string $payload = null,
        ?string $textPayload = null,
        ?string $imei = null
    ): ?TrackerAuth {
        $trackerAuth = $this->em->getRepository(TrackerAuth::class)->findOneByImei($imei);

        if (!$trackerAuth) {
            $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);

            if ($device) {
                $trackerAuth = $this->saveAuth($payload, $device, $imei, $socketId);
            }
        }

        return $trackerAuth;
    }

    /**
     * @inheritDoc
     */
    public function updateDeviceModelByProtocol(
        DecoderInterface $decoder,
        $textPayload,
        Device $device
    ): Device {
        $protocol = $decoder->getProtocol($textPayload);
        $modelName = $device->getModelName();
        $protocolByModel = TcpDecoder::getProtocolByModelName($modelName);

        if ($protocolByModel != $protocol) {
            $defaultModelName = $this->getDefaultModelNameByProtocol($protocol);
            $modelByProtocol = $this->em->getRepository(DeviceModel::class)->findOneBy(
                ['name' => $defaultModelName]
            );

            if ($modelByProtocol && $device->getModel() != $modelByProtocol) {
                $device->setModel($modelByProtocol);
                $device->setUsage($modelByProtocol->getUsage());
                $this->em->flush($device);
            }
        }

        return $device;
    }

    /**
     * @inheritDoc
     */
    public function hasImeiInDataPacket(): bool
    {
        return true;
    }

    /**
     * @todo move to parent class and make it common?
     * @inheritDoc
     */
    public function saveCommandForTracker(
        Device $device,
        ?User $currentUser,
        TrackerCommandInterface $commandModel
    ): ?TrackerCommand {
        $trackerCommand = new TrackerCommand();
        $trackerCommand->setDevice($device);
        $trackerCommand->setVehicle($device->getVehicle());
        $trackerCommand->setCreatedBy($currentUser);
        $trackerCommand->setCommandRequest($commandModel->getCommand());
        $trackerCommand->setType($commandModel->getType());

        $this->em->persist($trackerCommand);
        $this->em->flush();

        return $trackerCommand;
    }

    /**
     * @param Device|null $device
     * @param string $payload
     * @param string $imei
     * @return array
     * @throws Exception
     */
    public function handleCommandData(
        ?Device $device,
        string $payload,
        string $imei
    ): array {
        if ($device) {
            $this->updateDeviceLastDataReceivedAt($device);
            $trackerCommand = $device->getOldestSentNotRespondedTrackerCommand();

            if ($trackerCommand) {
                $trackerCommand->setTrackerResponse($payload);
                $trackerCommand->setRespondedAt(new \DateTime());
                $this->em->flush();
            }

            return [
                'imei' => $imei,
                'deviceId' => $device->getId(),
                'response' => $this->getDataResponse(null, $payload, $device)
            ];
        }

        return [
            'error' => "Device with imei: $imei is not found",
        ];
    }

    /**
     * @param array $events
     * @return array
     */
    private function updateResponseWithEvents(array $events): array
    {
        $eventsData = [];

        foreach ($events as $event) {
            switch (true) {
                case ($event instanceof DeviceTempAndHumiditySensorIdReceivedEvent):
                    $trackerHistorySensorIds = $event->getTrackerHistorySensorIdForEvents();

                    $eventData = [
                        'name' => self::NEW_SENSOR_HISTORY_EVENT_NAME,
                        'data' => [
                            'trackerHistorySensorsIds' => $trackerHistorySensorIds,
                            'assetIds' => $this->getAssetIdsFromTrackerHistorySensorIds($trackerHistorySensorIds),
                        ]
                    ];
                    break;
                case ($event instanceof DeviceDTCVINReceivedEvent):
                    $eventData = [];

                    if ($event->isDTCData()) {
                        $trackerHistoryDTCVINIds = $event->getTrackerHistoryDTCVINIdsForEvents();

                        $eventData = [
                            'name' => self::NEW_DTC_VIN_HISTORY_EVENT_NAME,
                            'data' => [
                                'trackerHistoryDTCVINIds' => $trackerHistoryDTCVINIds
                            ]
                        ];
                    }

                    break;
                case ($event instanceof DeviceDrivingBehaviorReceivedEvent):
                    $drivingBehaviorIds = $event->getDrivingBehaviorIdsForEvents();

                    $eventData = [
                        'name' => self::NEW_DRIVING_BEHAVIOR_EVENT_NAME,
                        'data' => [
                            'drivingBehaviorIds' => $drivingBehaviorIds
                        ]
                    ];
                    break;
                default:
                    $eventData = [];
                    break;
            }

            if ($eventData) {
                $eventsData[] = $eventData;
            }
        }

        return $eventsData;
    }

    /**
     * @param array $trackerHistorySensorIds
     * @return array
     */
    private function getAssetIdsFromTrackerHistorySensorIds(array $trackerHistorySensorIds): array
    {
        $assetIds = [];

        foreach ($trackerHistorySensorIds as $trackerHistorySensorId) {
            $trackerHistorySensor = $this->em->getRepository(TrackerHistorySensor::class)
                ->find($trackerHistorySensorId);

            if ($trackerHistorySensor && $trackerHistorySensor->getAssetId()) {
                $assetIds[] = $trackerHistorySensor->getAssetId();
            }
        }

        return $assetIds;
    }

    public function handleDeviceExtraData(
        ?TrackerAuth $trackerAuth,
        ?Device $device,
        string $payloadFromDevice,
        DecoderInterface $decoder,
        string $imei,
        $textPayload = null
    ): array {
        if ($device) {
            try {
                $command = $device->getTrackerCommandToSend();
                $payload = $textPayload ?: $payloadFromDevice;
                $this->updateDeviceLastDataReceivedAt($device);
                $data = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
                $trackerPayload = $this->savePayload($payloadFromDevice, $trackerAuth, $device);
                $events = $this->saveExtraData($data, $device, $trackerPayload);
                $this->triggerDeviceExtraEvents($events);
            } catch (\Exception $e) {
                throw $e;
            }

            return [
                'imei' => $imei,
                'deviceId' => $device->getId(),
                'vehicleId' => $device->getVehicleId(),
                'response' => $this->getDataResponse($data, $payload, $device),
                'eventsData' => $this->updateResponseWithEvents($events),
                'command' => $command
            ];
        }

        return [
            'error' => "Device with imei: $imei is not found",
        ];
    }

    public function handleDeviceExtraDataOnlyPayload(
        ?TrackerAuth $trackerAuth,
        ?Device $device,
        string $payloadFromDevice,
        DecoderInterface $decoder,
        string $imei,
        $textPayload = null
    ): array {
        if ($device) {
            try {
                $command = $device->getTrackerCommandToSend();
                $payload = $textPayload ?: $payloadFromDevice;
                $this->updateDeviceLastDataReceivedAt($device);
                $data = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
                $trackerPayload = $this->savePayloadTemp($payloadFromDevice, $trackerAuth, $device);
            } catch (\Exception $e) {
                throw $e;
            }

            return [
                'imei' => $imei,
                'deviceId' => $device->getId(),
                'vehicleId' => $device->getVehicleId(),
                'response' => $this->getDataResponse($data, $payload, $device),
                'eventsData' => [],
                'command' => $command
            ];
        }

        return [
            'error' => "Device with imei: $imei is not found",
        ];
    }

    public function saveExtraData(array $data, Device $device, TrackerPayload $trackerPayload): array
    {
        $events = [];

        /** @var Data $record */
        foreach ($data as $record) {
            if (!$record->getDateTime() || !$this->isTrackerDataDatetimeValid($record->getDateTime())) {
                continue;
            }
            // @todo check if it's ok for transaction
            if ($record->getTempAndHumidityData() && !$device->sensorRecordExistsForDevice($record->getDateTime())) {
                $events[] = $this->eventDispatcher->dispatch(
                    new DeviceTempAndHumiditySensorIdReceivedEvent($record, $device, $trackerPayload),
                    DeviceTempAndHumiditySensorIdReceivedEvent::NAME
                );
            }
            if ($record->getDTCVINData()
                && $record->getDTCVINData()->getCodes()
                && !$device->DTCVINRecordExistsForDevice($record->getDateTime())
            ) {
                $events[] = $this->eventDispatcher->dispatch(
                    new DeviceDTCVINReceivedEvent($record, $device, $trackerPayload),
                    DeviceDTCVINReceivedEvent::NAME
                );
            }
            if ($record->getDriverBehaviorData()
                && !$device->drivingBehaviorRecordExistsForDevice($record->getDateTime())
            ) {
                $events[] = $this->eventDispatcher->dispatch(
                    new DeviceDrivingBehaviorReceivedEvent($record, $device, $trackerPayload),
                    DeviceDrivingBehaviorReceivedEvent::NAME
                );
            }
            if ($record->getNetworkData()) {
                $events[] = $this->eventDispatcher->dispatch(
                    new DeviceNetworkReceivedEvent($record, $device, $trackerPayload),
                    DeviceNetworkReceivedEvent::NAME
                );
            }
            if ($record->getOneWireData() && $record->getDriverFOBId()) {
                $this->eventDispatcher->dispatch(
                    new DriverFOBIdReceivedEvent($device, ['data' => [['record' => $record]]]),
                    DriverFOBIdReceivedEvent::NAME
                );
            }
        }

        $this->em->flush();

        return $events;
    }

    /**
     * @inheritDoc
     */
    public function getVendorName(): string
    {
        return DeviceVendor::VENDOR_TOPFLYTECH;
    }

    /**
     * @param string|null $name
     * @return DrivingBehavior|null
     */
    public static function mapDrivingBehavior(?string $name): ?DrivingBehavior
    {
        if ($name) {
            $drivingBehavior = new DrivingBehavior();

            switch ($name) {
                case DriverBehaviorBase::HARSH_ACCELERATION:
                    $drivingBehavior->setHarshAcceleration(1);
                    break;
                case DriverBehaviorBase::HARSH_BRAKING:
                    $drivingBehavior->setHarshBraking(1);
                    break;
                case DriverBehaviorBase::HARSH_TURNING:
                    $drivingBehavior->setHarshCornering(1);
                    break;
            }

            return $drivingBehavior;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isValidUnknownPayload(string $payload, string $imei): bool
    {
        return !preg_match('~[a-z].*[a-z].*[a-z].*~', $imei);
    }
}