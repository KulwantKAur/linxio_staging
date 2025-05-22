<?php

namespace App\Service\Traccar;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Tracker\TraccarEventHistory;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\User;
use App\Enums\SocketEventEnum;
use App\Events\Device\DevicePanicButtonEvent;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Traccar\Model\TraccarApiDecoder;
use App\Service\Traccar\Model\TraccarData;
use App\Service\Traccar\Model\TraccarDevice;
use App\Service\Traccar\Model\TraccarEvent;
use App\Service\Traccar\Model\TraccarPosition;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\TeltonikaTrackerService;
use App\Service\Tracker\TrackerService;
use App\Service\TrackerProvider\TrackerProviderEvent;
use App\Service\TrackerProvider\TrackerProviderService;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TraccarService extends TrackerService
{
    public const TRACCAR_API_PATH = '/api';

    /** @var EntityManager $em */
    public $em;
    public $eventDispatcher;
    public $notificationDispatcher;
    public $logger;
    public $httpClient;
    public $traccarWebUrl;
    public $trackerProviderService;
    public $teltonikaTrackerService;

    /**
     * @param EntityManager $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventDispatcher $notificationDispatcher
     * @param LoggerInterface $logger
     * @param string $traccarWebUrl
     * @param string $traccarWebUser
     * @param string $traccarWebPass
     * @param TrackerProviderService $trackerProviderService
     * @param TeltonikaTrackerService $teltonikaTrackerService
     * @param EngineOnTimeService $engineOnTimeService
     * @param MemoryDbService $memoryDb
     * @param BillingEntityHistoryService $billingEntityHistoryService
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        string $traccarWebUrl,
        string $traccarWebUser,
        string $traccarWebPass,
        TrackerProviderService $trackerProviderService,
        TeltonikaTrackerService $teltonikaTrackerService,
        EngineOnTimeService $engineOnTimeService,
        MemoryDbService $memoryDb,
        BillingEntityHistoryService $billingEntityHistoryService
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->traccarWebUrl = $traccarWebUrl;
        $this->httpClient = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'base_uri' => $traccarWebUrl,
            'timeout' => 5,
            'auth' => [$traccarWebUser, $traccarWebPass],
        ]);
        $this->trackerProviderService = $trackerProviderService;
        $this->teltonikaTrackerService = $teltonikaTrackerService;
        $this->engineOnTimeService = $engineOnTimeService;
        $this->memoryDb = $memoryDb;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
    }

    /**
     * @param array $data
     * @return array
     */
    private function addQueryParams(array $data): array
    {
        return ['query' => [$data]];
    }

    /**
     * @param string $urlPath
     * @param string $action
     * @param array|null $params
     * @return array|\stdClass|null|mixed
     * @throws \Exception
     */
    private function request(string $urlPath, string $action = 'GET', ?array $params = [])
    {
        try {
            $result = $this->httpClient->request($action, self::TRACCAR_API_PATH . $urlPath, $params);
            $response = $result->getBody()->getContents();
            $data = $response ? json_decode($response) : [];
        } catch (ClientException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case 400:
                    return null;
                default:
                    break;
            }

            $this->logger->error($e->getMessage(), ['name' => TraccarService::class]);
            throw new \Exception($e->getMessage());
        } catch (RequestException $e) {
            $this->logger->error($e->getMessage(), ['name' => TraccarService::class]);
            throw new \Exception($e->getMessage());
        }

        return $data;
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
                case ($event instanceof DevicePanicButtonEvent):
                    $trackerHistoryData = $event->getTrackerHistoryData();

                    $eventData = (new TrackerProviderEvent(
                        SocketEventEnum::PanicButton->value,
                        SocketEventEnum::SourceApi,
                        new \DateTime(),
                        ['trackerHistoryData' => $trackerHistoryData]
                    ))->toArray();
                    break;
                case ($event instanceof TraccarEvent):
                    $eventData = (new TrackerProviderEvent(
                        $event->getType(),
                        SocketEventEnum::SourceTraccar,
                        $event->getEventTime()
                    ))->toArray();
                    break;
                default:
                    $eventData = (new TrackerProviderEvent(
                        SocketEventEnum::Undefined->value,
                        SocketEventEnum::SourceTraccar
                    ))->toArray();
                    break;
            }

            $eventsData[] = $eventData;
        }

        return $eventsData;
    }

    /**
     * @param Device $device
     * @param string $payload
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function resolveDataBySaveMode(Device $device, string $payload): array
    {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD => $this->handleDeviceDataOnlyPayload(
                null, $device, $payload, new TraccarApiDecoder($payload), $device->getImei()
            ),
            default => $this->handleDeviceData(
                null, $device, $payload, new TraccarApiDecoder($payload), $device->getImei()
            )
        };
    }

    /**
     * @param Device $device
     * @param string $payload
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function resolveEventDataBySaveMode(Device $device, string $payload): array
    {
        return match ($this->getDataSaveMode()) {
            self::DATA_SAVE_MODE_PAYLOAD => $this->handleDeviceEventDataOnlyPayload(
                $device, $payload, new TraccarApiDecoder($payload), $device->getImei()
            ),
            default => $this->handleDeviceEventData(
                $device, $payload, new TraccarApiDecoder($payload), $device->getImei()
            )
        };
    }

    /**
     * @param string $imei
     * @return TraccarDevice|null
     * @throws \Exception
     */
    public function deviceByImei(string $imei): ?TraccarDevice
    {
        $data = $this->devices(['uniqueId' => $imei]);

        return $data ? new TraccarDevice(array_shift($data)) : null;
    }

    /**
     * @param int $id
     * @return TraccarDevice|null
     * @throws \Exception
     */
    public function deviceById(int $id): ?TraccarDevice
    {
        $data = $this->devices(['id' => $id]);

        return $data ? new TraccarDevice(array_shift($data)) : null;
    }

    /**
     * @param array $params
     * @return array|mixed
     * @throws \Exception
     */
    public function devices(array $params)
    {
        return $this->request('/devices', 'GET', ['query' => $params]);
    }

    /**
     * @param Device $device
     * @return TraccarDevice
     * @throws \Exception
     */
    public function createDeviceFromDevice(Device $device): TraccarDevice
    {
        $data = $this->request('/devices', 'POST', [
            'body' => json_encode($device->toTraccarEntity())
        ]);

        if (!$data) {
            throw new \Exception('Can not create device in Traccar service');
        }

        return new TraccarDevice($data);
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Exception
     */
    public function createDevice(array $data)
    {
        return $this->request('/devices', 'POST', [
            'body' => $data
        ]);
    }

    /**
     * @param int $id
     * @return array|mixed
     * @throws \Exception
     */
    public function deleteDeviceById(int $id)
    {
        return $this->request('/devices/' . $id, 'DELETE', []);
    }

    /**
     * @param TraccarDevice $traccarDevice
     * @return array|mixed
     * @throws \Exception
     */
    public function setDeviceDisabled(TraccarDevice $traccarDevice)
    {
        $traccarDevice->setDisabled(true);

        return $this->request('/devices/' . $traccarDevice->getId(), 'PUT', [
            'json' => $traccarDevice->toAPIArray()
        ]);
    }

    /**
     * @param TraccarDevice $traccarDevice
     * @return array|mixed
     * @throws \Exception
     */
    public function setDeviceEnabled(TraccarDevice $traccarDevice)
    {
        $traccarDevice->setDisabled(false);

        return $this->request('/devices/' . $traccarDevice->getId(), 'PUT', [
            'json' => $traccarDevice->toAPIArray()
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function positions()
    {
        return $this->request('/positions');
    }

    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function reportsEvents(array $params)
    {
        return $this->request('/reports/events', 'GET', ['query' => $params]);
    }

    /**
     * @param array $data
     * @return array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function handleTraccarPosition(array $data): ?array
    {
        $data = TraccarData::setSourceToRawData($data, TraccarData::POSITION_SOURCE);
        $positionData = TraccarData::getPositionDataFromRawData($data);

        if (!$positionData) {
            return null;
        }

        $deviceId = TraccarData::getDeviceIdFromRawPositionData($positionData);

        if ($deviceId) {
            $payload = json_encode($data);
            $device = $this->em->getRepository(Device::class)->getDeviceByTraccarDeviceId($deviceId);

            if (!$device) {
                return null;
            }

            $data = $this->resolveDataBySaveMode($device, $payload);
            $trackerHistoryData = $data['trackerHistoryData'];
            $trackerHistoryLast = $data['trackerHistoryLast'];

            if ($trackerHistoryData && $device->getClientId()) {
                $this->trackerProviderService
                    ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
            }
        }

        return $data ?? null;
    }

    /**
     * @param array $data
     * @return TraccarEvent|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function handleTraccarEvent(array $data): ?array
    {
        $data = TraccarData::setSourceToRawData($data, TraccarData::EVENT_SOURCE);
        $eventData = TraccarData::getEventDataFromRawData($data);

        if (!$eventData) {
            return null;
        }

        $deviceId = TraccarData::getDeviceIdFromRawEventData($eventData);

        if ($deviceId) {
            $payload = json_encode($data);
            $device = $this->em->getRepository(Device::class)->getDeviceByTraccarDeviceId($deviceId);

            if (!$device) {
                return null;
            }

            $data = $this->resolveEventDataBySaveMode($device, $payload);
            $eventsData = $data['eventsData'];

            if ($eventsData && $device->getClientId()) {
//                $this->trackerProviderService->trackerEventNotification($device, $eventsData);
            }
        }

        return $data ?? null;
    }

    /**
     * @inheritDoc
     */
    public function saveHistoryFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        Device $device,
        ?TrackerHistory $prevTh
    ): TrackerHistory {
        $dataSource = $data->getSource();

        if ($dataSource != TraccarData::POSITION_SOURCE) {
            throw new \Exception('Unsupported traccar data source: ' . $dataSource);
        }

        $trackerHistory = new TrackerHistory();
        /** @var TraccarPosition $traccarPosition */
        $traccarPosition = $data->getPositionData();
        $trackerHistory->setTs($data->getDateTime());
        $trackerHistory->setLat($traccarPosition->getLatitude());
        $trackerHistory->setLng($traccarPosition->getLongitude());
        $trackerHistory->setAlt($traccarPosition->getAltitude());
        $trackerHistory->setSpeed($traccarPosition->getSpeed());
        $trackerHistory->setAngle($traccarPosition->getCourse());
        $trackerHistory->setTraccarPositionId($traccarPosition->getId());
        $trackerHistory = TraccarData::fillTrackerHistoryByPositionAttributes(
            $traccarPosition, $trackerHistory, $device
        );
        $trackerHistory->setDevice($device);
        $trackerHistory->setTeam($device->getTeam());
        $trackerHistory->setVehicle($device->getVehicle());
        $trackerHistory->setDriver($device->getVehicleDriver());
        $trackerHistory->setTrackerPayload($trackerPayload);
        $trackerHistory->setIsSOSButton($data->isPanicButton());
        $trackerHistory->setSatellites($data->getSatellites());

        $this->em->persist($trackerHistory);
        $this->em->flush();

        return $trackerHistory;
    }

    /**
     * @param DeviceDataInterface $data
     * @param TrackerPayload $trackerPayload
     * @param Device $device
     * @return TraccarEventHistory
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveEventHistoryFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        Device $device
    ): TraccarEventHistory {
        $traccarEvent = $data->getEventData();
        $traccarEventHistory = new TraccarEventHistory();
        $traccarEventHistory->fromTraccarEvent($traccarEvent);
        $traccarEventHistory->setDevice($device);
        $traccarEventHistory->setVehicle($device->getVehicle());
        $traccarEventHistory->setDriver($device->getVehicleDriver());
        $traccarEventHistory->setPayload($trackerPayload);
        $this->em->persist($traccarEventHistory);
        $this->em->flush();

        return $traccarEventHistory;
    }

    public function handleDeviceEventData(
        ?Device $device,
        string $payload,
        DecoderInterface $decoder,
        string $imei
    ): array {
        if ($device) {
//            $this->em->getConnection()->beginTransaction();

            try {
//                $command = $device->getTrackerCommandToSend();
                $trackerPayload = $this->savePayload($payload, null, $device);
                $this->updateDeviceLastDataReceivedAt($device);
                $data = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
                $events = $this->saveExtraData($data, $device, $trackerPayload);
//                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
//                if ($this->em->getConnection()->isTransactionActive()) {
//                    $this->em->getConnection()->rollBack();
//                }
                throw $e;
            }

            return [
                'imei' => $imei,
                'response' => $this->getDataResponse($data, $payload, $device),
                'eventsData' => $this->updateResponseWithEvents($events),
                'command' => null
            ];
        }

        return [
            'error' => "Device with imei: $imei is not found",
        ];
    }

    public function handleDeviceEventDataOnlyPayload(
        ?Device $device,
        string $payload,
        DecoderInterface $decoder,
        string $imei
    ): array {
        if ($device) {
//            $this->em->getConnection()->beginTransaction();

            try {
                $trackerPayload = $this->savePayloadTemp($payload, null, $device);
                $this->updateDeviceLastDataReceivedAt($device);
//                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
//                if ($this->em->getConnection()->isTransactionActive()) {
//                    $this->em->getConnection()->rollBack();
//                }
                throw $e;
            }

            return [
                'imei' => $imei,
                'response' => [],
                'eventsData' => [],
                'command' => null
            ];
        }

        return [
            'error' => "Device with imei: $imei is not found",
        ];
    }

    /**
     * @param $data
     * @param Device $device
     * @param TrackerPayload $trackerPayload
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveExtraData($data, Device $device, TrackerPayload $trackerPayload): array
    {
        $events = [];

        /** @var DeviceDataInterface $record */
        foreach ($data as $record) {
            $traccarEvent = $record->getEventData();

            if (!$record->getDateTime()
                || !$traccarEvent
                || $device->traccarEventRecordExistsForDevice($record->getDateTime(), $traccarEvent->getType())
            ) {
                continue;
            }

            $traccarEventHistory = $this->saveEventHistoryFromRecord($record, $trackerPayload, $device);

            /** @var TraccarEvent|null $traccarEvent */
            if ($traccarEvent) {
                // @todo verify event types with real data
                switch ($traccarEvent->getType()) {
                    case TraccarEvent::ALARM_TYPE:
                        if ($record->isPanicButton()) {
                            $this->clickMobilePanicButton($device, $record->getDateTime());
                        }
                        break;
                    default:
                        $events[] = $traccarEvent;
                        break;
                }
            }
        }

        $this->em->flush();

        return $events;
    }

    /**
     * @inheritDoc
     */
    public function parseFromTcp(
        mixed $payload,
        ?string $socketId = null,
        ?string $imei = null,
        string $type = TraccarData::POSITION_SOURCE
    ) {
        return match ($type) {
            TraccarData::EVENT_SOURCE => $this->handleTraccarEvent($payload),
            default => $this->handleTraccarPosition($payload)
        };
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
    public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload $trackerPayload,
        TrackerHistory $trackerHistory,
        Device $device
    ): void {
        $dataSource = $data->getSource();

        if ($dataSource != TraccarData::POSITION_SOURCE) {
            return;
        }

        /** @var TraccarPosition $traccarPosition */
        $traccarPosition = $data->getPositionData();

        switch ($traccarPosition->getProtocol()) {
            case TraccarData::PROTOCOL_TELTONIKA:
                $this->teltonikaTrackerService
                    ->saveSensorDataFromRecord($data, $trackerPayload, $trackerHistory, $device);
                break;
            default:
                break;
        }

    }

    /**
     * @inheritDoc
     */
    public function getAuthResponse(bool $success, $data)
    {
        // TODO: Implement getAuthResponse() method.
    }

    /**
     * @inheritDoc
     */
    public function getDataResponse($data, $textPayload, ?Device $device = null)
    {
        // TODO: Implement getDataResponse() method.
    }

    /**
     * @inheritDoc
     */
    public function getQueryDataForLogs(Device $device, $dateFrom, $dateTo)
    {
        return $this->em->getRepository(TrackerHistory::class)
            ->getQueryDataForTraccarLogs($device, $dateFrom, $dateTo);
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
                'payload' => $dbDatum['payload'],
                'extraData' => $dbDatum['extraData'],
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
        // TODO: Implement hasImeiInDataPacket() method.
    }

    /**
     * @inheritDoc
     */
    public function saveCommandForTracker(
        Device $device,
        ?User $currentUser,
        TrackerCommandInterface $commandModel
    ): TrackerCommand {
        // TODO: Implement saveCommandForTracker() method.
    }

    /**
     * @inheritDoc
     */
    public function getVendorName(): string
    {
        return DeviceVendor::VENDOR_TRACCAR;
    }

    /**
     * @inheritDoc
     */
    public function isValidUnknownPayload(string $payload, string $imei): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function encodePayloadFromDB(string $payload): array
    {
        // @todo check, with true?
        return json_decode($payload, true);
    }
}
