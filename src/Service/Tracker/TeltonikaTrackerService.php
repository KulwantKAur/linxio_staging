<?php

namespace App\Service\Tracker;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Tracker\TrackerAuth;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\User;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\DrivingBehavior\DrivingBehaviorService;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\SensorIOInterface;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\BaseType;
use App\Service\Tracker\Parser\Teltonika\TcpDecoder;
use App\Service\Tracker\Parser\Teltonika\TcpEncoder;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeltonikaTrackerService extends TrackerService
{
    private $drivingBehaviorService;
    private $translator;
    protected $simulatorBaseImei;
    protected $simulatorDevicesCount;
    protected $mapService;
    public $em;
    public $notificationDispatcher;
    public $eventDispatcher;
    public $logger;

    /**
     * @param Device $device
     * @param string $payload
     * @param TcpDecoder $decoder
     * @param TrackerAuth|null $trackerAuth
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function resolveDataBySaveMode(
        Device $device,
        string $payload,
        TcpDecoder $decoder,
        ?TrackerAuth $trackerAuth
    ): array {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD => $this->handleDeviceDataOnlyPayload(
                $trackerAuth,
                $device,
                $payload,
                $decoder,
                $device->getImei()
            ),
            default => $this->handleDeviceData(
                $trackerAuth,
                $device,
                $payload,
                $decoder,
                $device->getImei()
            )
        };
    }

    /**
     * TeltonikaTrackerService constructor.
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param int $simulatorBaseImei
     * @param int $simulatorDevicesCount
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param DrivingBehaviorService $drivingBehaviorService
     * @param EngineOnTimeService $engineOnTimeService
     * @param MemoryDbService $memoryDb
     * @param BillingEntityHistoryService $billingEntityHistoryService
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        int $simulatorBaseImei,
        int $simulatorDevicesCount,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        DrivingBehaviorService $drivingBehaviorService,
        EngineOnTimeService $engineOnTimeService,
        MemoryDbService $memoryDb,
        BillingEntityHistoryService $billingEntityHistoryService
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->simulatorBaseImei = $simulatorBaseImei;
        $this->simulatorDevicesCount = $simulatorDevicesCount;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->drivingBehaviorService = $drivingBehaviorService;
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
    ): array {
        $decoder = new TcpDecoder();

        if ($decoder->isAuthentication($payload)) {
            $response = $this->authorizeDevice($payload, $socketId, $decoder);
        } else {
            $trackerAuth = $this->getTrackerAuth($socketId, null, null, $imei);

            if (!$trackerAuth) {
                return [
                    'error' => "Authorization with socket: $socketId and imei: $imei is not found",
                ];
            }

            $device = $trackerAuth->getDevice();
            $response = $this->resolveDataBySaveMode($device, $payload, $decoder, $trackerAuth);
        }

        return $response;
    }

    /**
     * @param string $payloadFromDevice
     * @param string|null $socketId
     * @return null|string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getImei(string $payloadFromDevice, ?string $socketId): ?string
    {
        $decoder = new TcpDecoder();

        if ($decoder->isAuthentication($payloadFromDevice)) {
            $imeiModel = $decoder->decodeAuthentication($payloadFromDevice);
            $imei = $imeiModel->getImei();
        } else {
            $imei = $this->em->getRepository(TrackerAuth::class)->findImeiBySocketId($socketId);
        }

        return $imei ?? null;
    }

    /**
     * @param string $payloadFromDevice
     */
    public function parseFromSms(string $payloadFromDevice)
    {
        // todo implement
    }

    /**
     * @param DeviceDataInterface $record
     * @param TrackerPayload $trackerPayload
     * @param Device $device
     * @return TrackerHistory
     * @throws \Doctrine\DBAL\DBALException
     */
    public function saveHistoryFromRecord(
        DeviceDataInterface $record,
        TrackerPayload $trackerPayload,
        Device $device,
        ?TrackerHistory $prevTh
    ): TrackerHistory {
        $gpsData = $record->getGpsData();

        $connection = $this->em->getConnection();
        $data = [
            'tracker_payload_id' => $trackerPayload->getId(),
            'device_id' => $device->getId(),
            'ts' => $record->getDateTime()->format('Y-m-d H:i:s.000'),
            'lat' => $gpsData->getLatitude(),
            'lng' => $gpsData->getLongitude(),
            'alt' => $gpsData->getAltitude(),
            'angle' => $gpsData->getAngle(),
            'speed' => $gpsData->getSpeed(),
            'satellites' => $record->getSatellites(),
            'created_at' => 'NOW()',
            'vehicle_id' => $device->getVehicle()?->getId(),
            'driver_id' => $device->getVehicle() && $device->getVehicle()->getDriver()
                ? $device->getVehicle()->getDriver()->getId()
                : null
        ];
        $connection->insert('tracker_history', $data);
        $trackerHistoryId = $connection->lastInsertId();

        return $this->em->getRepository(TrackerHistory::class)->find($trackerHistoryId);
    }

    /**
     * @param DeviceDataInterface $data
     * @param TrackerPayload $trackerPayload
     * @param TrackerHistory $trackerHistory
     * @param Device $device
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        TrackerHistory $trackerHistory,
        Device $device
    ): void {
        /** @var SensorIOInterface $data */
        $ioData = $data->getSensorsIOData();
        $trackerHistory->setIOExtraData($ioData);
        $dbParams = $this->drivingBehaviorService->getInsertParamsFromHistory($trackerHistory);
        $deviceModelName = $device->getModel()->getName();
        $drivingBehaviorParamsMap = DrivingBehaviorService::SENSOR_EVENT_TO_BEHAVIOR_PARAM;

        foreach ($ioData as $ioKey => $ioValue) {
            $connection = $this->em->getConnection();

            if (isset($drivingBehaviorParamsMap[$deviceModelName][$ioKey])) {
                $this->drivingBehaviorService->setParamBySensorEvent(
                    $deviceModelName,
                    $ioKey,
                    $ioValue,
                    $dbParams
                );
                $connection->insert('driving_behavior', $dbParams);
            }

            $this->updateHistoryFromSensor($ioKey, $ioValue, $device, $trackerHistory);
        }
    }

    /**
     * @param $eventRemoteId
     * @param $eventValue
     * @param Device $device
     * @param TrackerHistory $trackerHistory
     * @return void
     * @throws \Exception
     */
    protected function updateHistoryFromSensor(
        $eventRemoteId,
        $eventValue,
        Device $device,
        TrackerHistory $trackerHistory
    ): void {
        $deviceModelName = $device->getModel()->getName();

        switch ($eventRemoteId) {
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::ODOMETER):
                $trackerHistory->setOdometer($eventValue, $device->getLastTrackerRecord());
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::ENGINE_HOURS):
                $trackerHistory->setEngineOnTime($eventValue); // seconds
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::TEMPERATURE_LEVEL):
                $trackerHistory->setTemperatureLevel($eventValue);
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::BATTERY_VOLTAGE):
                $trackerHistory->setBatteryVoltage($eventValue);
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::EXTERNAL_VOLTAGE):
                $trackerHistory->setExternalVoltage($eventValue);
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::IGNITION):
                $ignition = ($device->isFixWithSpeed() && ($trackerHistory->getSpeed() > 0)) ? 1 : $eventValue;
                $trackerHistory->setIgnition($ignition);
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::MOVEMENT):
                $trackerHistory->setMovement($eventValue);
                break;
            case $this->getEventIdByDeviceModelNameAndEventName($deviceModelName, BaseType::IBUTTON):
                $trackerHistory->setIButton($eventValue);
                break;
            default:
                break;
        }
    }

    /**
     * @param string $imei
     * @return bool
     */
    public function isImeiFromSimulator($imei)
    {
        for (
            $currentImei = $this->simulatorBaseImei;
            $currentImei < $this->simulatorBaseImei + $this->simulatorDevicesCount;
            $currentImei++
        ) {
            if ($imei == $currentImei) {
                return true;
            }
        };

        return false;
    }

    /**
     * @param $payload
     * @return string|null
     */
    public function convertPayloadToImei($payload)
    {
        $decoder = new TcpDecoder();

        if ($decoder->isAuthentication($payload)) {
            $imeiModel = $decoder->decodeAuthentication($payload);
            $imei = $imeiModel->getImei();
        }

        return $imei ?? null;
    }

    /**
     * @param $imei
     * @return string|null
     */
    public function convertImeiToPayload($imei)
    {
        $encoder = new TcpEncoder();

        return $encoder->convertImeiToPayload($imei);
    }

    /**
     * @return int
     */
    public function getBaseImei()
    {
        return $this->simulatorBaseImei;
    }

    /**
     * @inheritDoc
     */
    public function getAuthResponse(bool $success, $data = null)
    {
        $encoder = new TcpEncoder();

        return $encoder->encodeAuthentication($success);
    }

    /**
     * @inheritDoc
     */
    public function getDataResponse($data, $textPayload = null, ?Device $device = null)
    {
        $encoder = new TcpEncoder();

        return $encoder->encodeData($data, $device);
    }

    /**
     * @inheritDoc
     */
    public function getQueryDataForLogs(Device $device, $dateFrom, $dateTo)
    {
        return $this->em->getRepository(TrackerHistory::class)
            ->getQueryDataForTeltonikaLogs($device, $dateFrom, $dateTo);
    }

    /**
     * @inheritDoc
     */
    public function formatTrackerDataLog(PaginationInterface $pagination, Device $device): array
    {
        $data = [];

        foreach ($pagination as $key => $dbDatum) {
            $ioFromExtraData = '';

            if (!is_null($dbDatum['ioFromExtraData']) && isset($dbDatum['ioFromExtraData']['IOData'])) {
                $ioDataFromExtraData = $dbDatum['ioFromExtraData']['IOData'];
                $lastKey = array_key_last($ioDataFromExtraData);

                foreach ($ioDataFromExtraData as $ioKey => $ioDatum) {
                    if (is_array($ioDatum)) {
                        $ioFromExtraData .= '[' . array_key_first($ioDatum) . ']: ' . array_pop($ioDatum);
                    } else {
                        $ioFromExtraData .= '[' . $ioKey . ']: ' . $ioDatum;
                    }

                    if ($lastKey != $ioKey) {
                        $ioFromExtraData .= ', ';
                    }
                }
            }

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
                'ioData' => !empty($ioFromExtraData) ? $ioFromExtraData : $dbDatum['ioFromEvents'],
                'batteryVoltage' => isset($dbDatum['batteryVoltage']) && (floor($dbDatum['batteryVoltage']) > 100)
                    ? round($dbDatum['batteryVoltage'] / 1000, 2)
                    : $dbDatum['batteryVoltage'],
                'temperatureLevel' => isset($dbDatum['temperatureLevel']) ? $dbDatum['temperatureLevel'] / 1000 : null,
                'externalVoltage' => isset($dbDatum['externalVoltage'])
                    ? round($dbDatum['externalVoltage'] / 1000, 2)
                    : null,
            ];
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function hasImeiInDataPacket(): bool
    {
        return false;
    }

    /**
     * @param string $deviceModelName
     * @param $eventName
     * @return string
     * @throws \Exception
     */
    public function getEventIdByDeviceModelNameAndEventName(string $deviceModelName, $eventName)
    {
        $eventTypeModel = BaseType::getEventTypesModelByModelName($deviceModelName);

        return $eventTypeModel::getEventIdByEventName($eventName);
    }

    /**
     * @inheritDoc
     */
    public function saveCommandForTracker(
        Device $device,
        ?User $currentUser,
        TrackerCommandInterface $commandModel
    ): ?TrackerCommand {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getVendorName(): string
    {
        return DeviceVendor::VENDOR_TELTONIKA;
    }

    /**
     * @inheritDoc
     */
    public function isValidUnknownPayload(string $payload, string $imei): bool
    {
        return true;
    }
}
