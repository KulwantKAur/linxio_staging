<?php

namespace App\Service\Tracker;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\DrivingBehavior;
use App\Entity\Tracker\TrackerAuth;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\User;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\Parser\Ulbotech\Data;
use App\Service\Tracker\Parser\Ulbotech\Model\HarshDriverBehavior;
use App\Service\Tracker\Parser\Ulbotech\Model\HeartBeat;
use App\Service\Tracker\Parser\Ulbotech\TcpEncoder;
use App\Service\Tracker\Parser\Ulbotech\TcpDecoder;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @todo add driving behavior service?
 */
class UlbotechTrackerService extends TrackerService
{
    public $em;
    public $eventDispatcher;
    public $notificationDispatcher;
    public $logger;

    /**
     * @param Device|null $device
     * @param string $payload
     * @param TcpDecoder $decoder
     * @param string $imei
     * @param string|null $data
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function resolveDataBySaveMode(
        ?Device $device,
        string $payload,
        TcpDecoder $decoder,
        string $imei,
        ?string $data,
    ): array {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD => $this->handleDeviceDataOnlyPayload(null, $device, $payload, $decoder, $imei, $data),
            default => $this->handleDeviceData(null, $device, $payload, $decoder, $imei, $data),
        };
    }

    /**
     * UlbotechTrackerService constructor.
     * @param EntityManager $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param LoggerInterface $logger
     * @param EngineOnTimeService $engineOnTimeService
     * @param MemoryDbService $memoryDb
     * @param BillingEntityHistoryService $billingEntityHistoryService
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        EngineOnTimeService $engineOnTimeService,
        MemoryDbService $memoryDb,
        BillingEntityHistoryService $billingEntityHistoryService
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
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
        $data = $decoder->decodePayload($payload);

        if ($decoder->isAuthentication($data)) {
            $response = $this->authorizeDevice($payload, $socketId, $decoder, $data);
        } else {
            $imei = $this->getImeiByPayload($data);
            $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);
            $response = $this->resolveDataBySaveMode($device, $payload, $decoder, $imei, $data);
        }

        return $response;
    }

    /**
     * @param string $socketId
     * @param string $payload
     * @param string $textPayload
     * @param string|null $imei
     * @return TrackerAuth|object|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTrackerAuth(
        string $socketId,
        ?string $payload = null,
        ?string $textPayload = null,
        ?string $imei = null
    ): ?TrackerAuth {
        $heartBeat = HeartBeat::createFromTextPayload($textPayload);
        $imei = $heartBeat->getImei();
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
     * @param string $textPayload
     * @return string
     * @throws \Exception
     */
    public function getImeiByPayload(string $textPayload): string
    {
        $heartBeat = HeartBeat::createFromTextPayload($textPayload);

        return $heartBeat->getImei();
    }

    /**
     * @param DeviceDataInterface $record
     * @param TrackerPayload $trackerPayload
     * @param Device $device
     * @return TrackerHistory
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveHistoryFromRecord(
        DeviceDataInterface $record,
        TrackerPayload $trackerPayload,
        Device $device,
        ?TrackerHistory $prevTh
    ): TrackerHistory {
        /** @var Data $record */
        $gpsData = $record->getGpsData();
        $analogData = $record->getAnalogData();
        $statusData = $record->getStatusData();
        $movement = $statusData ? $statusData->getMove() : null;
        $ignition = $statusData ? $statusData->getEngineOn() : null;

        if ($device->isFixWithSpeed() && ($gpsData->getSpeed() == 0)) {
            $movement = 0;
            $ignition = 0;
        }

        $trackerHistory = new TrackerHistory();
        $trackerHistory->setTrackerPayload($trackerPayload);
        $trackerHistory->setDevice($device);
        $trackerHistory->setTeam($device->getTeam());
        $trackerHistory->setTs($record->getDateTime());
        $trackerHistory->setLat($gpsData->getLatitude());
        $trackerHistory->setLng($gpsData->getLongitude());
        $trackerHistory->setVehicle($device->getVehicle());
        $trackerHistory->setDriver($device->getVehicle()?->getDriver());
        $trackerHistory->setAngle($gpsData->getAngle());
        $trackerHistory->setSpeed($gpsData->getSpeed());
        $trackerHistory->setOdometer($record->getMileage(), $device->getLastTrackerRecord());
        $trackerHistory->setBatteryVoltage($analogData?->getDeviceBackupBatteryVoltage());
        $trackerHistory->setExternalVoltage($analogData?->getExternalPowerVoltage());
        $trackerHistory->setTemperatureLevel($analogData?->getDeviceTemperature());
        $trackerHistory->setMovement($movement);
        $trackerHistory->setIgnition($ignition);
        $trackerHistory->setEngineOnTime($record->getEngineOnTime());
        $trackerHistory->setIsSOSButton($record->isPanicButton());

        $this->em->persist($trackerHistory);
        $this->em->flush();

        return $trackerHistory;
    }

    /**
     * @param DeviceDataInterface $data
     * @param TrackerPayload $trackerPayload
     * @param TrackerHistory $trackerHistory
     * @param Device $device
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        TrackerHistory $trackerHistory,
        Device $device
    ): void {
        /** @var Data $data */
        $driverBehaviorData = $data->getDriverBehaviorData();
        $drivingBehavior = null;

        if ($driverBehaviorData) {
            foreach ($driverBehaviorData as $key => $datum) {
                if ($datum == 1) {
                    $drivingBehavior = $this->mapDrivingBehavior($drivingBehavior, $key);
                }
            }

            if ($drivingBehavior) {
                $drivingBehavior->fromTrackerHistory($trackerHistory);
                $drivingBehavior = $this->updateDrivingBehaviorWithCorrectCoordinates($device, $drivingBehavior);

                $this->em->persist($drivingBehavior);
                $this->em->flush();
            }
        }
    }

    /**
     * @param DrivingBehavior|null $drivingBehavior
     * @param string $name
     * @return DrivingBehavior
     */
    public function mapDrivingBehavior(?DrivingBehavior $drivingBehavior, string $name)
    {
        $drivingBehavior = $drivingBehavior ?? new DrivingBehavior();

        switch ($name) {
            case HarshDriverBehavior::RAPID_ACCELERATION:
                $drivingBehavior->setHarshAcceleration(1);
                $drivingBehavior->setTypeId(HarshDriverBehavior::RAPID_ACCELERATION_KEY);
                break;
            case HarshDriverBehavior::ROUGH_BRAKING:
                $drivingBehavior->setHarshBraking(1);
                $drivingBehavior->setTypeId(HarshDriverBehavior::ROUGH_BRAKING_KEY);
                break;
            case HarshDriverBehavior::HARSH_COURSE:
                $drivingBehavior->setHarshCornering(1);
                $drivingBehavior->setTypeId(HarshDriverBehavior::HARSH_COURSE_KEY);
                break;
            case HarshDriverBehavior::NO_WARM_UP:
                $drivingBehavior->setTypeId(HarshDriverBehavior::NO_WARM_UP_KEY);
                break;
            case HarshDriverBehavior::LONG_IDLE:
                $drivingBehavior->setTypeId(HarshDriverBehavior::LONG_IDLE_KEY);
                break;
            case HarshDriverBehavior::FATIGUE_DRIVING:
                $drivingBehavior->setTypeId(HarshDriverBehavior::FATIGUE_DRIVING_KEY);
                break;
            case HarshDriverBehavior::ROUGH_TERRAIN:
                $drivingBehavior->setTypeId(HarshDriverBehavior::ROUGH_TERRAIN_KEY);
                break;
            case HarshDriverBehavior::HIGH_RPM:
                $drivingBehavior->setTypeId(HarshDriverBehavior::HIGH_RPM_KEY);
                break;
        }

        return $drivingBehavior;
    }

    /**
     * @inheritDoc
     */
    public function getAuthResponse(bool $success, $data = null)
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
     * @param string $payloadFromDevice
     * @return mixed|void
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
            ->getQueryDataForUlbotechLogs($device, $dateFrom, $dateTo);
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
                'payload' => (new TcpDecoder())->decodePayload($dbDatum['payload']),
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
        return true;
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
        return DeviceVendor::VENDOR_ULBOTECH;
    }

    /**
     * @inheritDoc
     */
    public function isValidUnknownPayload(string $payload, string $imei): bool
    {
        return true;
    }

    /**
     * @param int $deviceId
     * @param $startedAt
     * @param $finishedAt
     * @param bool $addZeroIgnitionFix
     * @inheritDoc
     */
    public function updateIgnitionBySpeedFixFlag(int $deviceId, $startedAt, $finishedAt, bool $addZeroIgnitionFix = false): void
    {
        $query = $this->em->getRepository(TrackerHistory::class)
            ->getQueryToUpdateIgnitionBySpeedFixFlag($deviceId, $startedAt, $finishedAt);

        if ($addZeroIgnitionFix) {
            $query2 = clone $query;
            $query2->set('th.ignition', 1)->andWhere('th.speed > 0');
            $query2->getQuery()->execute();
        }

        $query->set('th.ignition', 0)->andWhere('th.speed = 0');
        $query->getQuery()->execute();
    }
}