<?php

namespace App\Service\Tracker;

use App\Entity\Device;
use App\Entity\DeviceVendor;
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
use App\Service\Tracker\Parser\Pivotel\Data;
use App\Service\Tracker\Parser\Pivotel\TcpDecoder;
use App\Service\Tracker\Parser\Pivotel\TcpEncoder;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PivotelTrackerService extends TrackerService
{
    public $em;
    public $eventDispatcher;
    public $notificationDispatcher;
    public $logger;

    /**
     * @param Device|null $device
     * @param TcpDecoder $decoder
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function resolveDataBySaveMode(?Device $device, TcpDecoder $decoder): array
    {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD => $this->handleDeviceDataOnlyPayload(
                null,
                $device,
                $decoder->getPayload(),
                $decoder,
                $decoder->getImei()
            ),
            default => $this->handleDeviceData(
                null,
                $device,
                $decoder->getPayload(),
                $decoder,
                $decoder->getImei()
            )
        };
    }

    /**
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
        $xmlPositionIndex = strpos($payload, '<?xml');
        if ($xmlPositionIndex !== false) {
            $payload = substr($payload, $xmlPositionIndex);
        } else {
            return ['error' => "XML is not detected in request"];
        }

        $decoder = new TcpDecoder($payload);

        //Deny AMMT devices temporary
        if ($decoder->getDeviceType() == 'AMMT') {
            return ['error' => "Device with type AMMT is not supported yet"];
        }
        if ($socketId) {
            $authResponse = $this->authorizeDevice($payload, $socketId, $decoder);
        }

        $device = $this->em->getRepository(Device::class)->getDeviceByImei($decoder->getImei());

        return $this->resolveDataBySaveMode($device, $decoder);
    }

    /**
     * @param DeviceDataInterface $record
     * @param TrackerPayload $trackerPayload
     * @param Device $device
     * @return TrackerHistory
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveHistoryFromRecord(
        DeviceDataInterface $record,
        TrackerPayload $trackerPayload,
        Device $device,
        ?TrackerHistory $prevTh
    ): TrackerHistory {
        /** @var Data $record */
        $gpsData = $record->getGpsData();

        $trackerHistory = new TrackerHistory();
        $trackerHistory->setTrackerPayload($trackerPayload);
        $trackerHistory->setDevice($device);
        $trackerHistory->setTeam($device->getTeam());
        $trackerHistory->setTs($record->getDateTime());
        $trackerHistory->setLat($gpsData->getLatitude());
        $trackerHistory->setLng($gpsData->getLongitude());
        $trackerHistory->setVehicle($device->getVehicle());
        $trackerHistory->setDriver($device->getVehicle() ? $device->getVehicle()->getDriver() : null);
        $trackerHistory->setMovement($record->getMovement());
        $trackerHistory->setIgnition($record->getIgnition());
        $trackerHistory->setBatteryVoltagePercentage($record->getBatteryVoltagePercentage());

        $this->em->persist($trackerHistory);
        $this->em->flush();

        return $trackerHistory;
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
    public function getQueryDataForLogs(Device $device, $dateFrom, $dateTo)
    {
        return $this->em->getRepository(TrackerHistory::class)
            ->getQueryDataForPivotelLogs($device, $dateFrom, $dateTo);
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
                    'coordinates' => [
                        'lat' => $dbDatum['lat'],
                        'lng' => $dbDatum['lng']
                    ],
                ],
                'payload' => $dbDatum['payload'],
                'batteryVoltage' => isset($dbDatum['batteryVoltage']) && (floor($dbDatum['batteryVoltage']) > 100)
                    ? round($dbDatum['batteryVoltage'] / 1000, 2)
                    : $dbDatum['batteryVoltage'],
                'batteryVoltagePercentage' => $dbDatum['batteryVoltagePercentage'],
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
     * @todo move to parent class and make it common?
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
    public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        TrackerHistory $trackerHistory,
        Device $device
    ): void {

    }

    /**
     * @inheritDoc
     */
    public function getAuthResponse(bool $success, $data)
    {
    }

    /**
     * @inheritDoc
     */
    public function parseFromSms(string $payloadFromDevice)
    {
    }

    /**
     * @inheritDoc
     */
    public function getVendorName(): string
    {
        return DeviceVendor::VENDOR_PIVOTEL;
    }

    /**
     * @inheritDoc
     */
    public function isValidUnknownPayload(string $payload, string $imei): bool
    {
        return true;
    }
}