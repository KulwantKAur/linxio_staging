<?php

namespace App\Service\Tracker;

use App\Entity\BillingEntityHistory;
use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\DrivingBehavior;
use App\Entity\Notification\Event;
use App\Entity\Tracker\TrackerAuth;
use App\Entity\Tracker\TrackerAuthUnknown;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\Tracker\TrackerPayloadTemp;
use App\Entity\Tracker\TrackerPayloadUnknown;
use App\Entity\User;
use App\Events\Area\CheckAreaEvent;
use App\Events\Device\DeviceAccidentReceivedEvent;
use App\Events\Device\DeviceBatteryEvent;
use App\Events\Device\DeviceExceedingSpeedLimitEvent;
use App\Events\Device\DeviceFinishedReceiveDataEvent;
use App\Events\Device\DeviceIOEvent;
use App\Events\Device\DeviceJammerReceivedEvent;
use App\Events\Device\DeviceLongDrivingEvent;
use App\Events\Device\DeviceLongStandingEvent;
use App\Events\Device\DeviceMovingEvent;
use App\Events\Device\DeviceMovingWithoutDriverEvent;
use App\Events\Device\DevicePanicButtonEvent;
use App\Events\Device\DeviceTodayDataEvent;
use App\Events\Device\DeviceTowingEvent;
use App\Events\Device\DeviceVoltageEvent;
use App\Events\Device\EngineHistoryEvent;
use App\Events\Device\OverSpeedingEvent;
use App\Events\User\Driver\DriverSensorIdReceivedEvent;
use App\Service\BaseService;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\DeviceRedisModel;
use App\Service\Streamax\Model\StreamaxApiDecoder;
use App\Service\Traccar\Model\TraccarApiDecoder;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\Parser\Pivotel\TcpDecoder as TcpDecoderPivotel;
use App\Service\Tracker\Parser\Teltonika\TcpDecoder as TcpDecoderTeltonika;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder as TcpDecoderTopflytech;
use App\Service\Tracker\Parser\Ulbotech\TcpDecoder as TcpDecoderUlbotech;
use App\Service\Tracker\Traits\TrackerExtraDataTrait;
use App\Util\GeoHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\Pagination\PaginationInterface;

abstract class TrackerService extends BaseService
{
    use TrackerExtraDataTrait;

    public const DATA_SAVE_MODE_ALL = 1;
    public const DATA_SAVE_MODE_PAYLOAD = 2;
    public const REQUEST_LOG_HEADER = 'X-REQUEST-LOG-ID';

    /** @var EntityManager $em */
    public $em;
    public $eventDispatcher;
    public $notificationDispatcher;
    public $logger;
    public EngineOnTimeService $engineOnTimeService;
    public MemoryDbService $memoryDb;
    public BillingEntityHistoryService $billingEntityHistoryService;
    public int $dataSaveMode = self::DATA_SAVE_MODE_ALL;
    public ?int $requestLogId = null;

    /**
     * @param mixed|array|string $payload
     * @param string|null $socketId
     * @param string|null $imei
     * @return mixed
     */
    abstract public function parseFromTcp(
        mixed $payload,
        ?string $socketId = null,
        ?string $imei = null
    );

    /**
     * @param string $payloadFromDevice
     * @return mixed
     */
    abstract public function parseFromSms(string $payloadFromDevice);

    abstract public function saveHistoryFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        Device $device,
        ?TrackerHistory $prevTh
    ): TrackerHistory;

    abstract public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        TrackerHistory $trackerHistory,
        Device $device
    ): void;

    /**
     * @param bool $success
     * @param $data
     * @return string
     */
    abstract public function getAuthResponse(bool $success, $data);

    /**
     * @param $data
     * @param $textPayload
     * @param Device|null $device
     * @return string
     */
    abstract public function getDataResponse($data, $textPayload, ?Device $device = null);

    /**
     * @param Device $device
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    abstract public function getQueryDataForLogs(Device $device, $dateFrom, $dateTo);

    /**
     * @param PaginationInterface $pagination
     * @param Device $device
     * @return array
     */
    abstract public function formatTrackerDataLog(PaginationInterface $pagination, Device $device): array;

    abstract public function hasImeiInDataPacket(): bool;

    abstract public function saveCommandForTracker(
        Device $device,
        ?User $currentUser,
        TrackerCommandInterface $commandModel
    ): ?TrackerCommand;

    /**
     * @return string
     */
    abstract public function getVendorName(): string;

    /**
     * @param string $payload
     * @param string $imei
     * @return bool
     */
    abstract public function isValidUnknownPayload(string $payload, string $imei): bool;

    /**
     * @param TrackerHistoryTemp $trackerHistoryTemp
     * @param TrackerHistory $trackerHistory
     * @param \DateTime|null $firstInstallDate
     * @return void
     */
    private function updateTrackerHistoryWithCalculatedFlags(
        TrackerHistoryTemp $trackerHistoryTemp,
        TrackerHistory $trackerHistory,
        ?\DateTime $firstInstallDate
    ): void {
        if (
            ($trackerHistoryTemp->getTs()->getTimestamp() < self::getAllowedRecordTimestamp()) ||
            ($trackerHistory->isAllCalculated()) ||
            !$firstInstallDate ||
            ($firstInstallDate && $trackerHistoryTemp->getTs() < $firstInstallDate)
        ) {
            $trackerHistoryTemp->setIsAllCalculated();
            $trackerHistory->setIsAllCalculated();
        }
    }

    private function saveTempHistoryFromRecord(
        TrackerHistory $trackerHistory,
        ?\DateTime $firstInstallDate
    ): TrackerHistoryTemp {
        $trackerHistoryTemp = new TrackerHistoryTemp();
        $trackerHistoryTemp->fromTrackerHistory($trackerHistory);
        $this->updateTrackerHistoryWithCalculatedFlags($trackerHistoryTemp, $trackerHistory, $firstInstallDate);
        $this->em->persist($trackerHistoryTemp);
        $this->em->flush();

        return $trackerHistoryTemp;
    }

    /**
     * @param DeviceDataInterface $record
     * @return bool
     */
    private function isTrackerRecordDateTimeInvalid(DeviceDataInterface $record): bool
    {
        return $record->getDateTime() > (new Carbon())->addMinute();
    }

    /**
     * @param Device|null $device
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function isDeniedToSaveDeviceData(?Device $device, array $data)
    {
        if (!$data) {
            return true;
        }

        $client = $device->getClientEntity();

        if (!$client && !$device->getIsDeactivated()) {
            return false;
        }
        if ($device->getIsDeactivated()
            || $client->isBlockedBilling()
            || $client->isDeleted()
            || $client->isClosed()
            || ($client->isInDemo() && $client->getExpirationDate() && $client->getExpirationDate() < new \DateTime())
        ) {
            return true;
        }

        $data = array_filter($data, function (DeviceDataInterface $datum) {
            return $datum->getDateTime();
        });

        if (!$data) {
            return true;
        }

        /** @var DeviceDataInterface $firstTrackerRecord */
        $firstTrackerRecord = reset($data);
        $lastTrackerRecord = null;
        $conditionForLastPoint = null;
        $deactivatedRecordByFirstRecord = $this->billingEntityHistoryService->getRecordByDate(
            $device->getId(),
            BillingEntityHistory::ENTITY_DEVICE,
            BillingEntityHistory::TYPE_DEACTIVATED,
            $firstTrackerRecord->getDateTime(),
            null
        );

        if (!$deactivatedRecordByFirstRecord) {
            return false;
        }

        $conditionForFirstPoint = $firstTrackerRecord->getDateTime() >= $deactivatedRecordByFirstRecord->getDateFrom()
            && (
                (!$deactivatedRecordByFirstRecord->getDateTo()) || (
                    $deactivatedRecordByFirstRecord->getDateTo()
                    && $firstTrackerRecord->getDateTime() <= $deactivatedRecordByFirstRecord->getDateTo()
                )
            );

        if (count($data) > 1) {
            /** @var DeviceDataInterface $lastTrackerRecord */
            $lastTrackerRecord = end($data);
            $conditionForLastPoint = $lastTrackerRecord->getDateTime() >= $deactivatedRecordByFirstRecord->getDateFrom()
                && (
                    (!$deactivatedRecordByFirstRecord->getDateTo()) || (
                        $deactivatedRecordByFirstRecord->getDateTo()
                        && $lastTrackerRecord->getDateTime() <= $deactivatedRecordByFirstRecord->getDateTo()
                    )
                );
        }

        // allow all records in array if first || last record is allowed (to minimize load)
        return ($conditionForFirstPoint && $lastTrackerRecord && $conditionForLastPoint)
            || ($conditionForFirstPoint && !$lastTrackerRecord);
    }

    /**
     * @param Device|null $device
     * @param string $payload
     * @param array $data
     * @param string $imei
     * @return array
     */
    private function getResponseForDeniedDeviceData(
        ?Device $device,
        string $payload,
        array $data,
        string $imei
    ) {
        return [
            'imei' => $imei,
            'deviceId' => $device->getId(),
            'response' => $this->getDataResponse($data, $payload, $device),
            'trackerHistoryData' => [],
            'command' => null
        ];
    }

    /**
     * @param $device
     * @param $record
     * @return bool
     */
    private function recordExistsForDevice($device, $record): bool
    {
        // @todo revert logic to check unique device+ts if wrong on prod
        return ($device->getLastTrackerRecord()?->getTs() == $record->getDateTime())
            ? true
            : ($device->getVendorName() == DeviceVendor::VENDOR_TRACCAR
                ? $this->em->getRepository(TrackerHistory::class)
                    ->recordExistsForDevice($device, $record->getDateTime())
                : false
            );
    }

    /**
     * @param Device $device
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function updateDeviceLastDataReceivedAt(Device $device)
    {
        $this->memoryDb->setTtl(
            DeviceRedisModel::getLastDataReceivedKey($device),
            (new \DateTime())->getTimestamp(),
            [],
            DeviceRedisModel::DEVICE_LAST_DATA_RECEIVED_TTL
        );
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return bool
     * @throws \Exception
     */
    public function isTrackerDataDatetimeValid(\DateTimeInterface $dateTime): bool
    {
        $futureDateTime = (new Carbon())->addMinute();

        return $dateTime <= $futureDateTime;
    }

    /**
     * @param string $payloadFromDevice
     * @param $socketId
     * @param DecoderInterface $decoder
     * @param string|null $data
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authorizeDevice(
        string $payloadFromDevice,
        $socketId,
        DecoderInterface $decoder,
        $data = null
    ): array {
        $data = $data ?? $payloadFromDevice;
        $imeiModel = $decoder->decodeAuthentication($data);
        $imei = $imeiModel->getImei();
        $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);

        if ($device) {
            if (!$this->hasImeiInDataPacket()) {
                $this->saveAuth($payloadFromDevice, $device, $imei, $socketId);
            }

            $this->updateDeviceLastDataReceivedAt($device);
            $response = [
                'response' => $this->getAuthResponse(true, $data),
                'isAuth' => true,
                'imei' => $imei
            ];
        } else {
            $unknownDeviceAuth = $this->saveUnknownDeviceAuth($payloadFromDevice, $imei, $socketId);

            $response = [
                'response' => $this->getAuthResponse(false, $data),
                'isAuth' => true,
                'error' => "Device with imei: $imei is not found",
            ];
        }

        return $response;
    }

    /**
     * @param $payloadFromDevice
     * @param TrackerAuth|null $trackerAuth
     * @param Device|null $device
     * @return TrackerPayload
     */
    public function savePayload($payloadFromDevice, ?TrackerAuth $trackerAuth, ?Device $device): TrackerPayload
    {
        $trackerPayload = new TrackerPayload();
        $trackerPayload->setTrackerAuth($trackerAuth);
        $trackerPayload->setPayload($payloadFromDevice);
        $trackerPayload->setDevice($device);

        $this->em->persist($trackerPayload);
        $this->em->flush();

        return $trackerPayload;
    }

    /**
     * @param $payloadFromDevice
     * @param TrackerAuth|null $trackerAuth
     * @param Device|null $device
     * @return TrackerPayloadTemp
     */
    public function savePayloadTemp($payloadFromDevice, ?TrackerAuth $trackerAuth, ?Device $device): TrackerPayloadTemp
    {
        $trackerPayload = new TrackerPayloadTemp();
        $trackerPayload->setTrackerAuth($trackerAuth);
        $trackerPayload->setPayload($payloadFromDevice);
        $trackerPayload->setDevice($device);

        $this->em->persist($trackerPayload);
        $this->em->flush();

        return $trackerPayload;
    }

    /**
     * @param string $payloadFromDevice
     * @param string $imei
     * @param $socketId
     * @return TrackerAuthUnknown
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveUnknownDeviceAuth(string $payloadFromDevice, string $imei, $socketId): TrackerAuthUnknown
    {
        $deviceAuth = $this->saveUnknownAuth($payloadFromDevice, $imei, $socketId);

        if ($this->em->getRepository(TrackerAuthUnknown::class)->isImeiExists($imei)) {
            $this->notificationDispatcher->dispatch(Event::DEVICE_UNKNOWN_DETECTED, $deviceAuth);
        }

        return $deviceAuth;
    }

    /**
     * @param $imei
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function getTrackPayloadsByImei($imei, $dateFrom, $dateTo): array
    {
        $dateFrom = $dateFrom ? Carbon::createFromTimestamp($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? Carbon::createFromTimestamp($dateTo) : (new Carbon())->subHours(24);
        $payloads = $this->em->getRepository(TrackerPayload::class)
            ->getPayloadsByImei($imei, $dateFrom, $dateTo);

        return $payloads;
    }

    /**
     * @return array
     */
    public function getUnknownDevicesAuth()
    {
        $devicesUnknownAuth = $this->em->getRepository(TrackerAuthUnknown::class)->findAll();

        return array_map(
            function (TrackerAuthUnknown $deviceUnknownAuth) {
                return $deviceUnknownAuth->toArray();
            },
            $devicesUnknownAuth
        );
    }

    /**
     * @param $payloadFromDevice
     * @param Device $device
     * @param string $imei
     * @param string $socketId
     * @return TrackerAuth
     */
    public function saveAuth($payloadFromDevice, Device $device, string $imei, string $socketId): TrackerAuth
    {
        $trackerAuth = new TrackerAuth();
        $trackerAuth->setDevice($device);
        $trackerAuth->setPayload($payloadFromDevice);
        $trackerAuth->setSocketId($socketId);
        $trackerAuth->setImei($imei);

        $this->em->persist($trackerAuth);
        $this->em->flush();

        return $trackerAuth;
    }

    public function saveRecords(
        $data,
        TrackerPayload $trackerPayload,
        Device $device
    ): array {
        $result = ['ids' => [], 'data' => []];
        $firstDeviceInstallDate = $device->getInstallDateFromFirstInstallation();
        $prevTh = null;
        $trackerHistory = null;
        /** @var DeviceDataInterface $record */
        foreach ($data as $record) {
            if (!$record->getDateTime()) {
                continue;
            }

            if (!$this->recordExistsForDevice($device, $record)) {
                if ($this->isTrackerRecordDateTimeInvalid($record)) {
                    continue;
                }

//                $this->em->getConnection()->beginTransaction();

                try {
                    $trackerHistory = $this->saveHistoryFromRecord($record, $trackerPayload, $device, $prevTh);
                    $this->updateThEngineOnTime($trackerHistory, $device, $prevTh);
                    $this->updateByExtraData($device, $trackerHistory);
                    $this->em->flush();
                } catch (\Throwable $exception) {
                    dump($exception);
                    $this->logger->error('Can not save TH: ' . $exception->getMessage(), [
                        'deviceId' => $device->getId(),
                        'record' => $record,
                    ]);
                    $trackerHistory = null;
                    continue;
                }
                try {
                    $prevTh = $trackerHistory;
                    $result['ids'][] = $trackerHistory->getId();
                    $result['data'][] = ['record' => $record, 'th' => $trackerHistory];

                    $trackerHistoryTemp = $this->saveTempHistoryFromRecord($trackerHistory, $firstDeviceInstallDate);
                } catch (\Throwable $exception) {
                    $this->logger->error('Can not save THT: ' . $exception->getMessage(), [
                        'deviceId' => $device->getId(),
                        'thId' => $trackerHistory->getId(),
                    ]);
                    $this->em->remove($trackerHistory);
                    continue;
                }
                try {
                    $this->saveSensorDataFromRecord(
                        $record,
                        $trackerPayload,
                        $trackerHistory,
                        $device
                    );
                } catch (\Throwable $exception) {
                    $this->logger->error('Can not save sensor data for TH: ' . $exception->getMessage(), [
                        'deviceId' => $device->getId(),
                        'thId' => $trackerHistory->getId(),
                        'tpId' => $trackerPayload->getId(),
                    ]);
                    continue;
                }

                $this->em->flush();
//                $this->em->getConnection()->commit();
            }
        }

        $this->getUpdatedLastDeviceRecord($device, $trackerHistory);

        return $result;
    }

    private function getUpdatedLastDeviceRecord(Device $device, ?TrackerHistory $trackerHistory): ?TrackerHistoryLast
    {
        if ($trackerHistory) {
            $lastTrackerHistory = $this->em->getRepository(TrackerHistoryLast::class)
                ->findOneBy(['device' => $device, 'vehicle' => $device->getVehicle()]);
            if ($lastTrackerHistory) {
                if ($trackerHistory->getTs() > $lastTrackerHistory->getTs()) {
                    $lastTrackerHistory->fromTrackerHistory($trackerHistory);
                }
            } else {
                $lastTrackerHistory = new TrackerHistoryLast();
                $lastTrackerHistory->fromTrackerHistory($trackerHistory);
                $this->em->persist($lastTrackerHistory);
                $device->setLastTrackerRecord($lastTrackerHistory);
            }

            $this->em->flush();
        }

        return $lastTrackerHistory ?? null;
    }

    private function updateThEngineOnTime(TrackerHistory $trackerHistory, Device $device, ?TrackerHistory $prevTh): void
    {
        if (in_array($device->getVendorName(), [DeviceVendor::VENDOR_TOPFLYTECH])) {
            $trackerHistory->setEngineOnTime(
                $this->engineOnTimeService->getEngineOnTimeForTh($device, $trackerHistory, $prevTh)
                ?? $trackerHistory->getEngineOnTime()
            );
        }
    }

    public function handleDeviceData(
        ?TrackerAuth $trackerAuth,
        ?Device $device,
        string $payloadFromDevice,
        DecoderInterface $decoder,
        string $imei,
        $textPayload = null
    ): array {
        if ($device) {
            try {
                $payload = $textPayload ?: $payloadFromDevice;
                $command = $device->getTrackerCommandToSend();
                $this->updateDeviceModelByProtocol($decoder, $payload, $device);
                $this->updateDeviceLastDataReceivedAt($device);
                $data = $decoder->orderByDateTime($decoder->decodeData($payload, $device));

                if ($this->isDeniedToSaveDeviceData($device, $data)) {
                    return $this->getResponseForDeniedDeviceData($device, $payload, $data, $imei);
                }

                $trackerPayload = $this->savePayload($payloadFromDevice, $trackerAuth, $device);
                $trackerHistoryData = $this->saveRecords($data, $trackerPayload, $device);

                //remove after change engine hours calc logic
                if (!empty($trackerHistoryData['ids'])
                    && !in_array($device->getVendorName(), [DeviceVendor::VENDOR_TOPFLYTECH])
                ) {
                    $this->engineOnTimeService->updateEngineOnTime($device, $trackerHistoryData);
                }

                $this->dispatchEvents($device, $data, $trackerHistoryData);
            } catch (\Exception $e) {
                if ($this->em->getConnection()->isTransactionActive()) {
                    $this->em->getConnection()->rollback();
                }

                throw $e;
            }

            return [
                'imei' => $imei,
                'deviceId' => $device->getId(),
                'device' => $device->toArray(['id', 'status']),
                'vehicleId' => $device->getVehicleId(),
                'response' => $this->getDataResponse($data, $payload, $device),
                'trackerHistoryLast' => $this->getTHLastResponseData($device),
                'trackerHistoryData' => $this->getTHResponseData($trackerHistoryData),
                'command' => $command
            ];
        }

        return [
            'error' => mb_convert_encoding("Device with imei: $imei is not found", 'UTF-8', 'UTF-8'),
        ];
    }

    /**
     * @param TrackerAuth|null $trackerAuth
     * @param Device|null $device
     * @param string $payloadFromDevice
     * @param DecoderInterface $decoder
     * @param string $imei
     * @param null $textPayload
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function handleDeviceDataOnlyPayload(
        ?TrackerAuth $trackerAuth,
        ?Device $device,
        string $payloadFromDevice,
        DecoderInterface $decoder,
        string $imei,
        $textPayload = null
    ): array {
        if ($device) {
            try {
                $payload = $textPayload ?: $payloadFromDevice;
                $command = $device->getTrackerCommandToSend();
                $this->updateDeviceModelByProtocol($decoder, $payload, $device);
                $this->updateDeviceLastDataReceivedAt($device);
                $data = $decoder->orderByDateTime($decoder->decodeData($payload, $device));

                if ($this->isDeniedToSaveDeviceData($device, $data)) {
                    return $this->getResponseForDeniedDeviceData($device, $payload, $data, $imei);
                }

                $trackerPayload = $this->savePayloadTemp($payloadFromDevice, $trackerAuth, $device);
            } catch (\Exception $e) {
                if ($this->em->getConnection()->isTransactionActive()) {
                    $this->em->getConnection()->rollback();
                }

                throw $e;
            }

            return [
                'imei' => $imei,
                'deviceId' => $device->getId(),
                'device' => $device->toArray(['id', 'status']),
                'vehicleId' => $device->getVehicleId(),
                'response' => $this->getDataResponse($data, $payload, $device),
                'trackerHistoryLast' => [],
                'trackerHistoryData' => [],
                'command' => $command
            ];
        }

        return [
            'error' => mb_convert_encoding("Device with imei: $imei is not found", 'UTF-8', 'UTF-8'),
        ];
    }

    /**
     * @param Device $device
     * @param array $data
     * @param array $trackerHistoryData
     */
    public function dispatchEvents(Device $device, array $data, array $trackerHistoryData)
    {
        if ($data && $trackerHistoryData && !empty($trackerHistoryData['ids'])) {
//            $lastTrackerHistory = $device->getLastTrackerHistory();

            try {
                $this->eventDispatcher->dispatch(
                    new CheckAreaEvent($device, $trackerHistoryData),
                    CheckAreaEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceFinishedReceiveDataEvent($device, $trackerHistoryData['ids']),
                    DeviceFinishedReceiveDataEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceVoltageEvent($device, $trackerHistoryData['ids']),
                    DeviceVoltageEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceTowingEvent($device, $trackerHistoryData['ids'], $trackerHistoryData),
                    DeviceTowingEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DevicePanicButtonEvent($device, $trackerHistoryData),
                    DevicePanicButtonEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new OverSpeedingEvent($device, $trackerHistoryData['ids'], $trackerHistoryData),
                    OverSpeedingEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceExceedingSpeedLimitEvent($device, $trackerHistoryData['ids'], $trackerHistoryData),
                    DeviceExceedingSpeedLimitEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceLongDrivingEvent($device, $trackerHistoryData['ids'], $trackerHistoryData),
                    DeviceLongDrivingEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceLongStandingEvent($device, $trackerHistoryData['ids'], $trackerHistoryData),
                    DeviceLongStandingEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceMovingEvent($device, $trackerHistoryData['ids'], $trackerHistoryData),
                    DeviceMovingEvent::NAME
                );
//                $this->eventDispatcher->dispatch(
//                    new DeviceEngineOnTimeEvent($device, $trackerHistoryData, $lastTrackerHistory),
//                    DeviceEngineOnTimeEvent::NAME
//                );
                $this->eventDispatcher->dispatch(
                    new DriverSensorIdReceivedEvent($device, $trackerHistoryData),
                    DriverSensorIdReceivedEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceIOEvent($device, $trackerHistoryData['ids']),
                    DeviceIOEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceMovingWithoutDriverEvent($device, $trackerHistoryData),
                    DeviceMovingWithoutDriverEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceBatteryEvent($device, $trackerHistoryData['ids']),
                    DeviceBatteryEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceTodayDataEvent($device, $trackerHistoryData),
                    DeviceTodayDataEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceJammerReceivedEvent($device, $trackerHistoryData),
                    DeviceJammerReceivedEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new EngineHistoryEvent($device, $trackerHistoryData),
                    EngineHistoryEvent::NAME
                );
                $this->eventDispatcher->dispatch(
                    new DeviceAccidentReceivedEvent($device, $trackerHistoryData),
                    DeviceAccidentReceivedEvent::NAME
                );
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * @param Device $device
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function getQueryForDataLogByDevice(Device $device, $dateFrom, $dateTo)
    {
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();;
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);

        return $this->getQueryDataForLogs($device, $dateFrom, $dateTo);
    }

    /**
     * @param string $socketId
     * @param string|null $payload
     * @param string|null $textPayload
     * @param string|null $imei
     * @return TrackerAuth|null
     */
    public function getTrackerAuth(
        string $socketId,
        ?string $payload = null,
        ?string $textPayload = null,
        ?string $imei = null
    ): ?TrackerAuth {
        $trackerAuth = $imei ? $this->em->getRepository(TrackerAuth::class)->findOneByImei($imei) : null;

        return $trackerAuth ?? $this->em->getRepository(TrackerAuth::class)->findOneBySocketId($socketId);
    }

    /**
     * @param Device $device
     * @param \DateTimeInterface|null $date
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function clickMobilePanicButton(Device $device, ?\DateTimeInterface $date = null)
    {
        $trackerHistoryData = $this->em
            ->getRepository(TrackerHistory::class)
            ->getLastOccurredTrackerHistoryByDevice($device, $date);

        $this->eventDispatcher->dispatch(
            new DevicePanicButtonEvent($device, $trackerHistoryData, DevicePanicButtonEvent::SOURCE_MOBILE),
            DevicePanicButtonEvent::NAME
        );
    }

    /**
     * @param string $payloadFromDevice
     * @param string $imei
     * @param string $socketId
     * @return TrackerAuthUnknown
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveUnknownAuth(string $payloadFromDevice, string $imei, string $socketId): TrackerAuthUnknown
    {
        $trackerUnknownAuth = $this->em->getRepository(TrackerAuthUnknown::class)->findOneBy(['imei' => $imei]);

        if (!$trackerUnknownAuth) {
            $trackerUnknownAuth = new TrackerAuthUnknown();
            $trackerUnknownAuth->setImei($imei);
            $trackerUnknownAuth->setVendor(
                $this->em->getRepository(DeviceVendor::class)->findByVendorName($this->getVendorName())
            );
            $this->em->persist($trackerUnknownAuth);
        } else {
            $trackerUnknownAuth->setUpdatedAt(new \DateTime());
        }

        $trackerUnknownAuth->setPayload(
            (strlen($payloadFromDevice) > 255) ? substr($payloadFromDevice, 0, 255) : $payloadFromDevice
        );
        $trackerUnknownAuth->setSocketId($socketId);

        $this->em->flush();

        return $trackerUnknownAuth;
    }

    /**
     * @param string $payloadFromDevice
     * @param string $imei
     * @return TrackerPayloadUnknown|null
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @todo temp method for testing, remove for future
     */
    public function saveUnknownPayload(string $payloadFromDevice, string $imei): ?TrackerPayloadUnknown
    {
        if (!$this->isValidUnknownPayload($payloadFromDevice, $imei)) {
            return null;
        }

        $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);
        $trackerPayloadUnknown = new TrackerPayloadUnknown();
        $trackerPayloadUnknown->setImei($imei);
        $trackerPayloadUnknown->setPayload($payloadFromDevice);
        $trackerPayloadUnknown->setDevice($device);

        $this->em->persist($trackerPayloadUnknown);
        $this->em->flush();

        return $trackerPayloadUnknown;
    }

    /**
     * @param Device $device
     * @param DrivingBehavior $drivingBehavior
     * @return DrivingBehavior
     */
    public function updateDrivingBehaviorWithCorrectCoordinates(
        Device $device,
        DrivingBehavior $drivingBehavior
    ): DrivingBehavior {
        if (!GeoHelper::hasCoordinatesWithCorrectValue($drivingBehavior->getLat(), $drivingBehavior->getLng())) {
            $ltr = $device->getLastTrackerRecord();

            if ($ltr) {
                $drivingBehavior->setLat($ltr->getLat());
                $drivingBehavior->setLng($ltr->getLng());
            }
        }

        return $drivingBehavior;
    }

    /**
     * @param array $trackerHistoryData
     * @return array
     * @throws \Exception
     */
    public function getTHResponseData(array $trackerHistoryData): array
    {
        return array_map(function (array $trackerHistoryDatum) {
            /** @var TrackerHistory $th */
            $th = $trackerHistoryDatum['th'];
            $th = $th ? $th->toArray(['id', 'lng', 'lat', 'tsISO8601']) : [];

            if ($th) {
                $th['ts'] = $th['tsISO8601'];
                $th = array_filter($th, function ($key) {
                    return in_array($key, ['id', 'lng', 'lat', 'ts']);
                }, ARRAY_FILTER_USE_KEY);
            }

            return $th;
        }, $trackerHistoryData['data']);
    }

    /**
     * @param Device $device
     * @return array|null
     */
    public function getTHLastResponseData(Device $device): ?array
    {
        $keys = [
            'angle',
            'speed',
            'movement',
            'temperatureLevel',
            'engineHours',
            'mileage',
            'externalVoltage',
            'ignition'
        ];
        $thLast = $device->getLastTrackerRecord()?->toArray($keys);

        if ($thLast) {
            $thLast = array_filter($thLast, function ($key) use ($keys) {
                return in_array($key, $keys);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $thLast;
    }

    /**
     * @param int $deviceId
     * @param $startedAt
     * @param $finishedAt
     * @param bool $addZeroIgnitionFix
     * @return void
     * @throws NotSupported
     */
    public function updateIgnitionBySpeedFixFlag(
        int $deviceId,
        $startedAt,
        $finishedAt,
        bool $addZeroIgnitionFix = false
    ): void {
        $query = $this->em->getRepository(TrackerHistory::class)
            ->getQueryToUpdateIgnitionBySpeedFixFlag($deviceId, $startedAt, $finishedAt);

        if ($addZeroIgnitionFix) {
            $query2 = clone $query;
            $query2->set('th.ignition', 0)->andWhere('th.speed = 0');
            $query2->getQuery()->execute();
        }

        $query->set('th.ignition', 1)->andWhere('th.speed > 0');
        $query->getQuery()->execute();
    }

    /**
     * @param DecoderInterface $decoder
     * @param $textPayload
     * @param Device $device
     * @return Device
     */
    public function updateDeviceModelByProtocol(DecoderInterface $decoder, $textPayload, Device $device): Device
    {
        return $device;
    }

    /**
     * @param string $payload
     * @param string|null $socketId
     * @return mixed
     */
    public function runDBPayloadProcess(string $payload, ?string $socketId = null)
    {
        $this->setDataSaveMode(self::DATA_SAVE_MODE_ALL);

        return $this->parseFromTcp($this->encodePayloadFromDB($payload), $socketId);
    }

    /**
     * @param string $payload
     * @return mixed
     */
    public function encodePayloadFromDB(string $payload)
    {
        return $payload;
    }

    /**
     * @return int
     */
    public function getDataSaveMode(): int
    {
        return $this->dataSaveMode;
    }

    /**
     * @param int $dataSaveMode
     */
    public function setDataSaveMode(int $dataSaveMode): void
    {
        $this->dataSaveMode = $dataSaveMode;
    }

    public function getRequestLogId(): ?int
    {
        return $this->requestLogId;
    }

    public function setRequestLogId(?int $requestLogId): void
    {
        $this->requestLogId = $requestLogId;
    }

    /**
     * @return int
     * @todo rewrite tests according to this condition
     */
    public static function getAllowedRecordTimestamp(): int
    {
        return (new Carbon())->subMonths(1)->getTimestamp();
    }

    /**
     * @param string $vendorName
     * @param string|null $payload
     * @return DecoderInterface|null
     */
    public static function getTcpDecoder(string $vendorName, ?string $payload): ?DecoderInterface
    {
        switch ($vendorName) {
            case DeviceVendor::VENDOR_TOPFLYTECH:
                return new TcpDecoderTopflytech();
            case DeviceVendor::VENDOR_TELTONIKA:
                return new TcpDecoderTeltonika();
            case DeviceVendor::VENDOR_ULBOTECH:
                return new TcpDecoderUlbotech();
            case DeviceVendor::VENDOR_PIVOTEL:
                return $payload ? new TcpDecoderPivotel($payload) : null;
            case DeviceVendor::VENDOR_TRACCAR:
                return $payload ? new TraccarApiDecoder($payload) : null;
            case DeviceVendor::VENDOR_STREAMAX:
                return $payload ? new StreamaxApiDecoder($payload) : null;
            default:
                return null;
        }
    }
}
