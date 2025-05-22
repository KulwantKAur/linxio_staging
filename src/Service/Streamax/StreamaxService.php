<?php

namespace App\Service\Streamax;

use App\Entity\Device;
use App\Entity\DeviceCameraEvent;
use App\Entity\DeviceCameraEventFile;
use App\Entity\DeviceCameraEventType;
use App\Entity\DeviceVendor;
use App\Entity\DrivingBehavior;
use App\Entity\StreamaxIntegration;
use App\Entity\Team;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\Tracker\TrackerPayloadStreamax;
use App\Entity\User;
use App\Enums\SocketEventEnum;
use App\Events\User\Driver\DriverSensorIdReceivedEvent;
use App\Service\Billing\BillingEntityHistoryService;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Streamax\Consumer\StreamaxPostponedQueueMessage;
use App\Service\Streamax\Consumer\StreamaxQueueMessage;
use App\Service\Streamax\Model\StreamaxAlarm;
use App\Service\Streamax\Model\StreamaxAlarmFile;
use App\Service\Streamax\Model\StreamaxApiDecoder;
use App\Service\Streamax\Model\StreamaxData;
use App\Service\Streamax\Model\StreamaxDevice;
use App\Service\Streamax\Model\StreamaxDeviceInfo;
use App\Service\Streamax\Model\StreamaxDownloadState;
use App\Service\Streamax\Model\StreamaxFile;
use App\Service\Streamax\Model\StreamaxGPS;
use App\Service\Streamax\Model\StreamaxIButton;
use App\Service\Streamax\Model\StreamaxOBD;
use App\Service\Streamax\Model\StreamaxOnlineState;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\TrackerService;
use App\Service\TrackerProvider\TrackerProviderEvent;
use App\Service\TrackerProvider\TrackerProviderService;
use App\Util\DateHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StreamaxService extends TrackerService
{
    private const API_URL = 'https://ap-ftcloud.ifleetvision.com:20501';
    private const API_PREFIX = '/openapi';
    private const FILE_TYPE_VIDEO = 'VIDEO';
    private const FILE_TYPE_IMAGE = 'IMAGE';
    private const FILE_TYPE_BLACK_BOX = 'BLACK_BOX';
    private const EXTRA_DATA_VOLTAGE = 'voltage';

    private function formatDataByImei(array $data, string $className): array
    {
        $dataByImei = [];

        foreach ($data['data'] as $deviceDatum) {
            $modelWithImei = class_exists($className) ? new $className($deviceDatum) : null;

            if (!$modelWithImei instanceof ImeiInterface) {
                continue;
            }

            $imei = $modelWithImei->getImei();
            $dataByImei[$imei][] = $deviceDatum;
        }

        return $dataByImei;
    }

    private function request(string $urlPath, string $action = 'GET', ?array $params = [])
    {
        try {
            $result = $this->httpClient->request($action, self::API_PREFIX . $urlPath, $params);
            $response = $result->getBody()->getContents();
            $data = $response ? json_decode($response, true) : [];
        } catch (ClientException $e) {
            $this->logger->error($e->getMessage(), ['name' => self::class]);

            switch ($e->getCode()) {
                case 400:
                case 404:
                    return null;
                default:
                    break;
            }

            throw new Exception($e->getMessage());
        } catch (RequestException $e) {
            $this->logger->error($e->getMessage(), ['name' => self::class]);
            throw new Exception($e->getMessage());
        }

        return $data;
    }

    /**
     * @param StreamaxData $record
     * @param array $trackerHistories
     * @return TrackerHistory|null
     */
    private function getRelatedTrackerHistory(StreamaxData $record, array $trackerHistories): ?TrackerHistory
    {
        /** @var TrackerHistory $trackerHistory */
        foreach ($trackerHistories as $trackerHistory) {
            if ($trackerHistory->getTs()->getTimestamp() === $record->getDateTime()->getTimestamp()) {
                return $trackerHistory;
            }
        }

        return null;
    }

    /**
     * @param string $alarmId
     * @param array $alarmsFilesData
     * @return StreamaxAlarmFile|null
     */
    private function getRelatedAlarmFilesData(string $alarmId, array $alarmsFilesData): ?StreamaxAlarmFile
    {
        foreach ($alarmsFilesData as $alarmFilesData) {
            $alarmFilesDatum = new StreamaxAlarmFile($alarmFilesData);

            if ($alarmFilesDatum->getAlarmId() === $alarmId) {
                return $alarmFilesDatum;
            }
        }

        return null;
    }

    /**
     * @param Device $device
     * @param string $alarmId
     * @param StreamaxAlarm $alarmData
     * @param TrackerHistory|null $trackerHistory
     * @return DeviceCameraEvent
     * @throws \Doctrine\ORM\Exception\NotSupported
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    private function createCameraEventByAlarm(
        Device          $device,
        string          $alarmId,
        StreamaxAlarm   $alarmData,
        ?TrackerHistory $trackerHistory
    ): DeviceCameraEvent {
        $deviceVendor = $this->em->getRepository(DeviceVendor::class)
            ->findByVendorName(DeviceVendor::VENDOR_STREAMAX);
        $deviceCameraEvent = $this->em->getRepository(DeviceCameraEvent::class)
            ->getByRemoteIdAndDeviceVendor($alarmId, $deviceVendor);

        if (!$deviceCameraEvent) {
            $deviceCameraEvent = $this->createDeviceCameraEventFromFile($device, $alarmData, $trackerHistory);

            try {
                $this->em->persist($deviceCameraEvent);
                $this->em->flush();
            } catch (UniqueConstraintViolationException $exception) {
                // sleep(1); @todo add if very frequently

                if (!$this->em->isOpen()) {
                    $this->em = $this->managerRegistry->resetManager();
                }

                $device = $this->em->getReference(Device::class, $device->getId());
                $trackerHistory = $trackerHistory
                    ? $this->em->getReference(TrackerHistory::class, $trackerHistory->getId())
                    : $trackerHistory;

                return $this->createCameraEventByAlarm($device, $alarmId, $alarmData, $trackerHistory);
            } catch (\Exception $exception) {
                $this->managerRegistry->resetManager();

                throw $exception;
            }
        }

        return $deviceCameraEvent;
    }

    /**
     * @param DeviceCameraEvent $deviceCameraEvent
     * @param StreamaxAlarmFile $alarmFilesData
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    private function createCameraEventFiles(DeviceCameraEvent $deviceCameraEvent, StreamaxAlarmFile $alarmFilesData): void
    {
        foreach ($alarmFilesData->getFiles() as $alarmFilesDatum) {
            $alarmFile = new StreamaxFile($alarmFilesDatum);
            $deviceCameraEventFile = $this->em->getRepository(DeviceCameraEventFile::class)
                ->getByRemoteIdAndEvent($alarmFile->getFileId(), $deviceCameraEvent);

            if ($alarmFile->getFileType() !== self::FILE_TYPE_VIDEO || $deviceCameraEventFile) {
                continue;
            }

            $deviceCameraEventFile = $this->createDeviceCameraEventFileFromFile($deviceCameraEvent, $alarmFile);

            try {
                $this->em->persist($deviceCameraEventFile);
                $this->em->flush();
            } catch (UniqueConstraintViolationException $exception) {
                // sleep(1); @todo add if very frequently

                if (!$this->em->isOpen()) {
                    $this->em = $this->managerRegistry->resetManager();
                }

                $deviceCameraEvent = $this->em->getReference(DeviceCameraEvent::class, $deviceCameraEvent->getId());
                $this->createCameraEventFiles($deviceCameraEvent, $alarmFilesData);
            } catch (\Exception $exception) {
                $this->managerRegistry->resetManager();

                throw $exception;
            }
        }

        $deviceCameraEvent->setUpdatedAt(new \DateTime());
        $this->em->flush();
    }

    private function getCameraEventTypeByAlarmType(?int $type): ?DeviceCameraEventType
    {
        $typeName = match ($type) {
            StreamaxAlarm::TYPE_UNFASTENED_SEAT_BELT => DeviceCameraEventType::UNFASTENED_SEAT_BELT,
            StreamaxAlarm::TYPE_SHARP_RIGHT_TURN,
            StreamaxAlarm::TYPE_SHARP_LEFT_TURN => DeviceCameraEventType::HARSH_CORNERING,
            StreamaxAlarm::TYPE_RAPID_ACCELERATION => DeviceCameraEventType::HARSH_ACCELERATION,
            StreamaxAlarm::TYPE_RAPID_DECELERATION => DeviceCameraEventType::HARSH_BRAKING,
            StreamaxAlarm::TYPE_SPEEDING_ALARM,
            StreamaxAlarm::TYPE_SPEED_LIMIT_SIGN_ALARM => DeviceCameraEventType::OVERSPEEDING,
            default => null
        };

        return $typeName
            ? $this->em->getRepository(DeviceCameraEventType::class)->findOneBy(['name' => $typeName])
            : null;
    }

    /**
     * @param Device $device
     * @param StreamaxAlarm $alarmData
     * @param TrackerHistory|null $trackerHistory
     * @return DeviceCameraEvent
     */
    private function createDeviceCameraEventFromFile(
        Device          $device,
        StreamaxAlarm   $alarmData,
        ?TrackerHistory $trackerHistory,
    ): DeviceCameraEvent {
        $dce = new DeviceCameraEvent();
        $dce->setDevice($device);
        $dce->setDeviceVendor($device->getVendor());
        $dce->setVehicle($device->getVehicle());
        $dce->setTeam($device->getTeam());
        $dce->setDriver($device->getVehicleDriver());
        $dce->setTrackerHistory($trackerHistory);
        $dce->setRemoteId($alarmData->getAlarmId());
        $dce->setStartedAt($alarmData->getStartTimeAsDate());
        $dce->setFinishedAt($alarmData->getEndTimeAsDate());
        $dce->setType($this->getCameraEventTypeByAlarmType($alarmData->getAlarmType()));
        $dce->setRemoteType($alarmData->getAlarmType());

        return $dce;
    }

    /**
     * @param DeviceCameraEvent $deviceCameraEvent
     * @param StreamaxFile $file
     * @return DeviceCameraEventFile
     */
    private function createDeviceCameraEventFileFromFile(
        DeviceCameraEvent $deviceCameraEvent,
        StreamaxFile      $file,
    ): DeviceCameraEventFile {
        $dcef = new DeviceCameraEventFile();
        $dcef->setEvent($deviceCameraEvent);
        $dcef->setRemoteId($file->getFileId());
        $dcef->setStartedAt($file->getStartTimeAsDate());
        $dcef->setFinishedAt($file->getEndTimeAsDate());
        $dcef->setFileType(DeviceCameraEventFile::FILE_TYPE_VIDEO);
        $dcef->setCameraType($file->getChannel());
        $dcef->setUrl($file->getUrl());
        // @todo add closest TH
        $dcef->setTrackerHistory($deviceCameraEvent->getTrackerHistory());

        return $dcef;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getAlarmIdsFromDownloadStateData(array $data): array
    {
        $alarmIds = [];

        foreach ($data['data'] as $datum) {
            $downloadStateData = new StreamaxDownloadState($datum);
            $alarmIds[] = $downloadStateData->getAlarmId();
        }

        return $alarmIds;
    }

    /**
     * @param array $records
     * @return array
     */
    private function getAlarmIdsFromAlarmData(array $records): array
    {
        $alarmIds = [];

        /** @var StreamaxData $record */
        foreach ($records as $record) {
            $alarmData = $record->getAlarmData();
            $alarmIds[] = $alarmData->getAlarmId();
        }

        return $alarmIds;
    }

    /**
     * @param array $alarmIds
     * @param string $payload
     * @return array
     * @throws \Doctrine\ORM\Exception\NotSupported
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveFilesFromAlarmIds(
        array                $alarmIds,
        string               $payload,
        ?StreamaxIntegration $streamaxIntegration
    ): array {
        $totalResultData = [];
        $alarmsFilesData = $this->alarmsFiles($alarmIds, $streamaxIntegration);
        $deviceVendor = $this->em->getRepository(DeviceVendor::class)->findByVendorName(DeviceVendor::VENDOR_STREAMAX);

        foreach ($alarmsFilesData as $alarmFilesDatum) {
            $alarmId = $alarmFilesDatum['alarmId'];
            $cameraEvent = $this->em->getRepository(DeviceCameraEvent::class)
                ->getByRemoteIdAndDeviceVendor($alarmId, $deviceVendor);
            $alarmFilesData = $this->getRelatedAlarmFilesData($alarmId, $alarmsFilesData);

            if (!$cameraEvent || !$alarmFilesData) {
                continue;
            }

            $this->createCameraEventFiles($cameraEvent, $alarmFilesData);
            $totalResultData[] = [
                'response' => $this->getDataResponse($alarmsFilesData, $payload),
            ];
        }

        return $totalResultData;
    }

    private function getStreamaxIntegrationByTenantId(string $tenantId): StreamaxIntegration
    {
        $streamaxIntegration = $this->em->getRepository(StreamaxIntegration::class)->findOneBy(['tenantId' => $tenantId]);

        if (!$streamaxIntegration) {
            throw new NotFoundHttpException('Streamax integration is not found');
        }

        return $streamaxIntegration;
    }

    /**
     * @param Team $team
     * @return StreamaxIntegration
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    private function getStreamaxIntegrationByTeam(Team $team): StreamaxIntegration
    {
        $streamaxIntegration = $this->em->getRepository(StreamaxIntegration::class)->findOneBy(['team' => $team]);

        if (!$streamaxIntegration) {
            throw new NotFoundHttpException('Streamax integration is not found');
        }

        return $streamaxIntegration;
    }

    /**
     * @return void
     */
    private function initDefaultHttpClient()
    {
        $this->httpClient = new Client([
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                '_sign' => $this->signature,
                '_tenantId' => $this->tenantId,
            ],
            'base_uri' => self::API_URL,
            'timeout' => 5,
        ]);
    }

    private function initBEHttpClient()
    {
        $this->BEHttpClient = new Client([
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ],
            'base_uri' => $this->BEApiUrl,
            'timeout' => $this->BERequestTimeout, // @todo check if ok: 150 payloads ~ 1.5 sec
        ]);
    }

    /**
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return void
     */
    private function initHttpClient(?StreamaxIntegration $streamaxIntegration)
    {
        if (!$streamaxIntegration) {
            return;
        }

        $this->httpClient = new Client([
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                '_sign' => $streamaxIntegration->getSignature(),
                '_tenantId' => $streamaxIntegration->getTenantId(),
            ],
            'base_uri' => $streamaxIntegration->getUrl(),
            'timeout' => 5,
        ]);
    }

    /**
     * @param StreamaxAlarm $alarmData
     * @return DrivingBehavior|null
     */
    private function mapDrivingBehavior(StreamaxAlarm $alarmData): ?DrivingBehavior
    {
        if ($alarmData->isForDrivingBehavior()) {
            $drivingBehavior = new DrivingBehavior();

            match (true) {
                $alarmData->isHarshAcceleration() => $drivingBehavior->setHarshAcceleration(1),
                $alarmData->isHarshCornering() => $drivingBehavior->setHarshCornering(1),
                $alarmData->isHarshBraking() => $drivingBehavior->setHarshBraking(1),
                default => $drivingBehavior = null
            };
        }

        return $drivingBehavior ?? null;
    }

    /**
     * @param TrackerHistory $th
     * @param TrackerHistory $lastTH
     * @return TrackerHistory
     */
    private function fromTrackerHistoryGPS(TrackerHistory $th, TrackerHistory $lastTH): TrackerHistory
    {
        $th->setAlt($th->getAlt() ?? $lastTH->getAlt());
        $th->setTemperatureLevel($th->getTemperatureLevel() ?? $lastTH->getTemperatureLevel());
        $th->setBatteryVoltage($th->getBatteryVoltage() ?? $lastTH->getBatteryVoltage());

        return $th;
    }

    /**
     * @param TrackerHistory $th
     * @param TrackerHistory $lastTH
     * @return TrackerHistory
     */
    private function fromTrackerHistoryAlarm(TrackerHistory $th, TrackerHistory $lastTH): TrackerHistory
    {
        $th->setAlt($th->getAlt() ?? $lastTH->getAlt());
        $th->setAngle($th->getAngle() ?? $lastTH->getAngle());
        $th->setSpeed($th->getSpeed() ?? $lastTH->getSpeed());
        $th->setSatellites($th->getSatellites() ?? $lastTH->getSatellites());
        $th->setOdometer($th->getOdometer() ?? $lastTH->getOdometer());
        $th->setTemperatureLevel($th->getTemperatureLevel() ?? $lastTH->getTemperatureLevel());
        $th->setBatteryVoltage($th->getBatteryVoltage() ?? $lastTH->getBatteryVoltage());
        $th->setMovement($th->getMovement() ?? $lastTH->getMovement());
        $th->setIgnition($th->getIgnition() ?? $lastTH->getIgnition());

//        if (!GeoHelper::hasCoordinatesWithCorrectValue($th->getLat(), $th->getLng())) {
//            $th->setLat($lastTH->getLat());
//            $th->setLng($lastTH->getLng());
//        }

        return $th;
    }

    /**
     * @param TrackerHistory $th
     * @param TrackerHistory $lastTH
     * @return TrackerHistory
     */
    private function fromTrackerHistoryOBD(TrackerHistory $th, TrackerHistory $lastTH): TrackerHistory
    {
        $th->setAlt($th->getAlt() ?? $lastTH->getAlt());
        $th->setAngle($th->getAngle() ?? $lastTH->getAngle());
        $th->setSpeed($th->getSpeed() ?? $lastTH->getSpeed());
        $th->setSatellites($th->getSatellites() ?? $lastTH->getSatellites());
        $th->setOdometer($th->getOdometer() ?? $lastTH->getOdometer());
        $th->setTemperatureLevel($th->getTemperatureLevel() ?? $lastTH->getTemperatureLevel());
        $th->setBatteryVoltage($th->getBatteryVoltage() ?? $lastTH->getBatteryVoltage());
        $th->setMovement($th->getMovement() ?? $lastTH->getMovement());
        $th->setIgnition($th->getIgnition() ?? $lastTH->getIgnition());

//        if (!GeoHelper::hasCoordinatesWithCorrectValue($th->getLat(), $th->getLng())) {
//            $th->setLat($lastTH->getLat());
//            $th->setLng($lastTH->getLng());
//        }

        return $th;
    }

    /**
     * @param DeviceDataInterface $record
     * @param TrackerHistory $trackerHistory
     * @param Device $device
     * @return void
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    private function handleGPSData(
        DeviceDataInterface $record,
        TrackerHistory      $trackerHistory,
        Device              $device
    ): void {
        $lastTH = $device->getLastTrackerHistory();

        if ($lastTH && $trackerHistory->getTs() > $lastTH->getTs()) {
            $trackerHistory = $this->fromTrackerHistoryGPS($trackerHistory, $lastTH);
        }

        $this->em->flush();
    }

    /**
     * @param DeviceDataInterface $record
     * @param TrackerHistory $trackerHistory
     * @param Device $device
     * @return void
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    private function handleAlarmData(
        DeviceDataInterface $record,
        TrackerHistory      $trackerHistory,
        Device              $device
    ): void {
        $lastTH = $device->getLastTrackerHistory();

        if ($lastTH && $trackerHistory->getTs() > $lastTH->getTs()) {
            $trackerHistory = $this->fromTrackerHistoryAlarm($trackerHistory, $lastTH);
        }

        /** @var StreamaxAlarm $alarmData */
        $alarmData = $record->getAlarmData();

        if ($alarmData) {
            $drivingBehavior = $this->mapDrivingBehavior($alarmData);

            if ($drivingBehavior) {
                $drivingBehavior->fromTrackerHistory($trackerHistory);
                $drivingBehavior = $this->updateDrivingBehaviorWithCorrectCoordinates($device, $drivingBehavior);

                $this->em->persist($drivingBehavior);
            }
        }

        $this->em->flush();
    }

    /**
     * @param DeviceDataInterface $data
     * @param TrackerHistory $trackerHistory
     * @param Device $device
     * @return void
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    private function handleOBDData(
        DeviceDataInterface $data,
        TrackerHistory      $trackerHistory,
        Device              $device
    ): void {
        $lastTH = $device->getLastTrackerHistory();

        if ($lastTH && $trackerHistory->getTs() > $lastTH->getTs()) {
            $trackerHistory = $this->fromTrackerHistoryOBD($trackerHistory, $lastTH);
        }

        $this->em->flush();
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
            self::DATA_SAVE_MODE_PAYLOAD => $this->handleDeviceDataOnlyPayload(null, $device, $payload, new StreamaxApiDecoder($payload), $device->getImei()),
            default => $this->handleDeviceData(null, $device, $payload, new StreamaxApiDecoder($payload), $device->getImei())
        };
    }

    private function updateVoltageByExtraData(Device $device, TrackerHistory $trackerHistory)
    {
//        $batteryVoltageExtra = $this->getExtraDataByKey(self::EXTRA_DATA_VOLTAGE);
//
//        if ($batteryVoltageExtra) {
//            $trackerHistory->setBatteryVoltage($batteryVoltageExtra);
//        }
    }

    private function queueDataFromTcp(array $data, string $type): array
    {
        $class = match ($type) {
            StreamaxData::TYPE_GPS => StreamaxGPS::class,
            StreamaxData::TYPE_ALARM => StreamaxAlarm::class,
            StreamaxData::TYPE_OBD => StreamaxOBD::class,
            StreamaxData::TYPE_ONLINE_STATE => StreamaxOnlineState::class,
            StreamaxData::TYPE_IBUTTON => StreamaxIButton::class,
        };
        $dataByImei = $this->formatDataByImei($data, $class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $data['data'] = $datumByImei;
            $eventMessage = new StreamaxQueueMessage($data, $this->getRequestLogId());
            $this->streamaxProducer->publish($eventMessage);
        }

        return [];
    }

    private function handleDeviceDataFromJob(array $data, string $type): array
    {
        $dataAll = [];
        $class = match ($type) {
            StreamaxData::TYPE_GPS => StreamaxGPS::class,
            StreamaxData::TYPE_ALARM => StreamaxAlarm::class,
            StreamaxData::TYPE_OBD => StreamaxOBD::class,
            StreamaxData::TYPE_ONLINE_STATE => StreamaxOnlineState::class,
            StreamaxData::TYPE_IBUTTON => StreamaxIButton::class,
        };
        $dataByImei = $this->formatDataByImei($data, $class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $data['data'] = $datumByImei;
            $dataAll[$imei] = $data;
        }

        return $dataAll;
    }

    private function queueDownloadState(array $data): array
    {
        $eventMessage = new StreamaxPostponedQueueMessage($data, 'handleDownloadState', $this->getRequestLogId());
        $this->streamaxPostponedProducer->publish($eventMessage);

        return [];
    }

    private function handleDeviceDataFromInboundProxy(array $data, string $type): array
    {
        $class = match ($type) {
            StreamaxData::TYPE_GPS => StreamaxGPS::class,
            StreamaxData::TYPE_ALARM => StreamaxAlarm::class,
            StreamaxData::TYPE_OBD => StreamaxOBD::class,
            StreamaxData::TYPE_ONLINE_STATE => StreamaxOnlineState::class,
            StreamaxData::TYPE_IBUTTON => StreamaxIButton::class,
        };
        $dataByImei = $this->formatDataByImei($data, $class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $data['data'] = $datumByImei;
            $this->sendInboundSingleDataToBE($data);
        }

        return [];
    }

    /**
     * @todo replace with queueDownloadStateFromInboundProxy() if take long time
     */
    private function handleDownloadStateFromInboundProxy(array $data): array
    {
        $this->sendInboundSingleDataToBE($data);

        return [];
    }

    private function sendInboundSingleDataToBE(array $data): void
    {
        try {
            // @todo send to API and interrupt waiting
            $this->BEHttpClient->request('POST', '/api/streamax/inbound/direct', [
                'json' => $data,
                'headers' => [TrackerService::REQUEST_LOG_HEADER => $this->getRequestLogId()]
            ]);
        } catch (ConnectException $e) {
            $msg = $e->getMessage();
            $interruptMsg = 'cURL error 28';

            if (!str_contains($msg, $interruptMsg)) {
                $this->logger->error($e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }

    private function queueDownloadStateFromInboundProxy(array $data): array
    {
        $eventMessage = new StreamaxPostponedQueueMessage($data, 'handleDownloadState', $this->getRequestLogId());
        $this->streamaxPostponedProducer->publish($eventMessage);

        return [];
    }

    private function saveProxyPayload(array $payloadFromDevice): TrackerPayloadStreamax
    {
        $trackerPayload = new TrackerPayloadStreamax();
        $trackerPayload->setPayload(json_encode($payloadFromDevice));

        $this->em->persist($trackerPayload);
        $this->em->flush();

        return $trackerPayload;
    }

    public function __construct(
        EntityManager                 $em,
        EventDispatcherInterface      $eventDispatcher,
        NotificationEventDispatcher   $notificationDispatcher,
        LoggerInterface               $logger,
        EngineOnTimeService           $engineOnTimeService,
        MemoryDbService               $memoryDb,
        BillingEntityHistoryService   $billingEntityHistoryService,
        private string                $signature,
        private string                $tenantId,
        private string                $secretKey,
        private ?Client               $httpClient,
        private ValidatorInterface    $validator,
        public TranslatorInterface    $translator,
        public TrackerProviderService $trackerProviderService,
        public Producer               $streamaxProducer,
        public Producer               $streamaxPostponedProducer,
        public Producer               $streamaxProxyProducer,
        public ManagerRegistry        $managerRegistry,
        public readonly string        $BEApiUrl,
        private ?Client               $BEHttpClient,
        private readonly float        $BERequestTimeout = 0.02,
    ) {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->engineOnTimeService = $engineOnTimeService;
        $this->memoryDb = $memoryDb;
        $this->billingEntityHistoryService = $billingEntityHistoryService;
        $this->initDefaultHttpClient();
        $this->initBEHttpClient();
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws Exception
     */
    public function auth(array $data)
    {
        return $this->request('/auth', 'POST', [
            'form_params' => $data
        ]);
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws Exception
     */
    public function createDevice(array $data, ?StreamaxIntegration $streamaxIntegration)
    {
        $this->initHttpClient($streamaxIntegration);

        return $this->request('/v2/devices', 'POST', [
            'json' => $data
        ]);
    }

    /**
     * @param string $uniqueId
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return array|mixed
     * @throws Exception
     */
    public function deleteDeviceByUniqueId(string $uniqueId, ?StreamaxIntegration $streamaxIntegration)
    {
        $this->initHttpClient($streamaxIntegration);
        $data = [
            'uniqueIds' => $uniqueId
        ];

        return $this->request('/v2/devices/', 'DELETE', [
            'json' => $data
        ]);
    }

    /**
     * @throws Exception
     * @example {"code": 200, "success": "true", "message": "Success", "data": [{"url": "https://{baseUrl}/video/c5dasda-3adf-3fa3-erwq3.flv", "session": "123wqe-12wqs-5frtg-5gdhk", "channel": 1}]}
     *
     */
    public function deviceLiveStreamData(Device $device, string $channels = '1,2,3,4')
    {
        $this->initHttpClient($device->getStreamaxIntegration());
        $imei = $device->getImei();
        $params = [
            'channels' => $channels
        ];

        return $this->request("/v2/devices/$imei/live-links", 'GET', ['query' => $params]);
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws Exception
     */
    public function devicesList(array $data)
    {
        return $this->request('/v2/devices', 'GET', [
            'params' => $data
        ]);
    }

    /**
     * @param string $imei
     * @return array|mixed
     * @throws Exception
     */
    public function getDeviceData(string $imei, ?StreamaxIntegration $streamaxIntegration): ?StreamaxDevice
    {
        $this->initHttpClient($streamaxIntegration);
        $data = $this->request("/v2/devices/$imei");

        return isset($data['data']) ? new StreamaxDevice($data['data']) : null;
    }

    /**
     * @param Device $device
     * @return StreamaxDevice
     * @throws \Exception
     */
    public function createDeviceFromDevice(Device $device): StreamaxDevice
    {
        $data = $this->createDevice($device->toStreamaxEntity(), $device->getStreamaxIntegration());

        if (!$data) {
            throw new \Exception('Can not create device in Streamax service');
        }

        return new StreamaxDevice($device->toStreamaxEntity());
    }

    /**
     * https://ftcloud.streamax.com:20002/DOC/Webhooks
     *
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function verifyWebhookSignature(Request $request): void
    {
        $data = $request->request->all();
        $tenantId = $data['tenantId'] ?? null;
        $streamaxIntegration = $tenantId ? $this->getStreamaxIntegrationByTenantId($tenantId) : null;
        $secretKey = $streamaxIntegration?->getSecret() ?: $this->secretKey;
        $content = $request->getContent();
        $signature = $request->headers->get('X-Webhook-Signature');
        $generatedSignature = hash_hmac('sha256', $content, $secretKey);

        if ($signature != $generatedSignature) {
            $this->logger->notice('Signature verification failed', [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'signature' => $signature,
                'generatedSignature' => $generatedSignature,
            ]);
        }
    }

    public function handleFromTcpInQueue(array $data): ?array
    {
        $type = $data['type'] ?? null;

        if (!isset($data['data'])) {
            return null;
        }

        return match ($type) {
            StreamaxData::TYPE_GPS,
            StreamaxData::TYPE_ALARM,
            StreamaxData::TYPE_OBD,
            StreamaxData::TYPE_ONLINE_STATE,
            StreamaxData::TYPE_IBUTTON => $this->queueDataFromTcp($data, $type),
            StreamaxData::TYPE_DOWNLOAD_STATE => $this->queueDownloadState($data),
            default => null
        };
    }

    public function handleDataFromJob(array $data): ?array
    {
        $type = $data['type'] ?? null;

        if (!isset($data['data'])) {
            return null;
        }

        return match ($type) {
            StreamaxData::TYPE_GPS,
            StreamaxData::TYPE_ALARM,
            StreamaxData::TYPE_OBD,
            StreamaxData::TYPE_ONLINE_STATE,
            StreamaxData::TYPE_IBUTTON => $this->handleDeviceDataFromJob($data, $type),
            default => null
        };
    }

    public function handleInbound(array $data): ?array
    {
        $type = $data['type'] ?? null;

        // @todo return after fix prod
        return match ($type) {
            StreamaxData::TYPE_GPS => $this->handleGPSSingle($data),
//            StreamaxData::TYPE_ALARM => $this->handleAlarmSingle($data),
            StreamaxData::TYPE_OBD => $this->handleOBDSingle($data),
            StreamaxData::TYPE_ONLINE_STATE => $this->handleOnlineStateSingle($data),
//            StreamaxData::TYPE_DOWNLOAD_STATE => $this->queueDownloadState($data),
            StreamaxData::TYPE_IBUTTON => $this->handleIButtonSingle($data),
            default => null
        };
    }

    public function handleInboundDirect(array $data): ?array
    {
        $type = $data['type'] ?? null;

        return match ($type) {
            StreamaxData::TYPE_GPS => $this->handleGPSSingle($data),
            StreamaxData::TYPE_ALARM => $this->handleAlarmSingle($data),
            StreamaxData::TYPE_OBD => $this->handleOBDSingle($data),
            StreamaxData::TYPE_ONLINE_STATE => $this->handleOnlineStateSingle($data),
            StreamaxData::TYPE_DOWNLOAD_STATE => $this->queueDownloadState($data),
            StreamaxData::TYPE_IBUTTON => $this->handleIButtonSingle($data),
            default => null
        };
    }

    /**
     * @todo remove if handleInbound() is ok
     **/
    public function handleInboundOld(array $data): ?array
    {
        $type = $data['type'] ?? null;

        return match ($type) {
            StreamaxData::TYPE_GPS => $this->handleGPS($data),
            StreamaxData::TYPE_ALARM => $this->handleAlarm($data),
            StreamaxData::TYPE_OBD => $this->handleOBD($data),
            StreamaxData::TYPE_ONLINE_STATE => $this->handleOnlineState($data),
            StreamaxData::TYPE_DOWNLOAD_STATE => $this->queueDownloadState($data),
            StreamaxData::TYPE_IBUTTON => $this->handleIButton($data),
            default => null
        };
    }

    public function parseFromTcpInQueue(array $data): void
    {
        $eventMessage = new StreamaxQueueMessage($data, $this->getRequestLogId());
        $this->streamaxProducer->publish($eventMessage);
    }

    public function parseFromTcpInProxyQueue(array $data): void
    {
        $eventMessage = new StreamaxQueueMessage($data, $this->getRequestLogId());
        $this->streamaxProxyProducer->publish($eventMessage);
    }

    public function getDeviceByUniqueId(string $streamaxDeviceId): ?Device
    {
        return $this->em->getRepository(Device::class)->getDeviceByImei($streamaxDeviceId);
    }

    public function handleGPS(array $data): ?array
    {
        if (!isset($data['data'])) {
            return null;
        }

        $totalResultData = [];
        $dataByImei = $this->formatDataByImei($data, StreamaxGPS::class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $data['data'] = $datumByImei;
            $payload = json_encode($data);
            $device = $this->getDeviceByUniqueId($imei);

            if (!$device) {
                continue;
            }

//            $batteryVoltageFromInfo = $this->deviceInfoVoltage($device->getImei(), $device->getStreamaxIntegration());
//            $this->initExtraDataByDeviceId($device->getId());
//            $this->setExtraDataValueByKey(self::EXTRA_DATA_VOLTAGE, $batteryVoltageFromInfo);
            $resultData = $this->resolveDataBySaveMode($device, $payload);
            $trackerHistoryData = $resultData['trackerHistoryData'];
            $trackerHistoryLast = $resultData['trackerHistoryLast'];
            $totalResultData[] = $resultData;

            if ($trackerHistoryData && $device->getClientId()) {
                $this->trackerProviderService
                    ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
            }
        }

        return $totalResultData;
    }

    public function handleGPSSingle(array $data): ?array
    {
        $payload = json_encode($data);
        $model = new StreamaxGPS(reset($data['data']));
        $device = $this->getDeviceByUniqueId($model->getImei());

        if (!$device) {
            return null;
        }

        $resultData = $this->resolveDataBySaveMode($device, $payload);
        $trackerHistoryData = $resultData['trackerHistoryData'];
        $trackerHistoryLast = $resultData['trackerHistoryLast'];
        $totalResultData[] = $resultData;

        if ($trackerHistoryData && $device->getClientId()) {
            $this->trackerProviderService
                ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
        }

        return $totalResultData;
    }

    public function handleAlarm(array $data): ?array
    {
        if (!isset($data['data'])) {
            return null;
        }

        $totalResultData = [];
        $dataByImei = $this->formatDataByImei($data, StreamaxAlarm::class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $data['data'] = $datumByImei;
            $payload = json_encode($data);
            $device = $this->getDeviceByUniqueId($imei);

            if (!$device) {
                continue;
            }

            $resultData = $this->resolveDataBySaveMode($device, $payload);
            $trackerHistoryData = $resultData['trackerHistoryData'];
            $trackerHistoryLast = $resultData['trackerHistoryLast'];
            $trackerHistoryIDs = array_column($trackerHistoryData, 'id');
            $totalResultData[] = $resultData;

            if ($trackerHistoryData && $device->getClientId()) {
                $this->trackerProviderService
                    ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
            }

            $this->queueAlarmVideos($device, $payload, $trackerHistoryIDs);
            // @todo uncomment below if we need alarm events
//            $records = $data['response'];
//            $eventsData = $this->handleAlarmEvents($records, $device);
//
//            if ($eventsData && $device->getClientId()) {
//                $this->trackerProviderService->trackerEventNotification($device, $eventsData);
//            }
        }

        return $totalResultData;
    }

    public function handleAlarmSingle(array $data): ?array
    {
        $payload = json_encode($data);
        $model = new StreamaxAlarm(reset($data['data']));
        $device = $this->getDeviceByUniqueId($model->getImei());

        if (!$device) {
            return null;
        }

        $resultData = $this->resolveDataBySaveMode($device, $payload);
        $trackerHistoryData = $resultData['trackerHistoryData'];
        $trackerHistoryLast = $resultData['trackerHistoryLast'];
        $trackerHistoryIDs = array_column($trackerHistoryData, 'id');
        $totalResultData[] = $resultData;

        if ($trackerHistoryData && $device->getClientId()) {
            $this->trackerProviderService
                ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
        }

        $this->queueAlarmVideos($device, $payload, $trackerHistoryIDs);
        // @todo uncomment below if we need alarm events
//            $records = $data['response'];
//            $eventsData = $this->handleAlarmEvents($records, $device);
//
//            if ($eventsData && $device->getClientId()) {
//                $this->trackerProviderService->trackerEventNotification($device, $eventsData);
//            }

        return $totalResultData;
    }

    public function queueAlarmVideos(Device $device, string $payload, array $trackerHistoryIDs): void
    {
        $eventMessage = new StreamaxPostponedQueueMessage([
            'deviceId' => $device->getId(),
            'payload' => $payload,
            'trackerHistoryIDs' => $trackerHistoryIDs,
        ], 'processAlarmVideos');
        $this->streamaxPostponedProducer->publish($eventMessage);
    }

    public function handleOBD(array $data): ?array
    {
        if (!isset($data['data'])) {
            return null;
        }

        $totalResultData = [];
        $dataByImei = $this->formatDataByImei($data, StreamaxOBD::class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $data['data'] = $datumByImei;
            $payload = json_encode($data);
            $device = $this->getDeviceByUniqueId($imei);

            if (!$device) {
                continue;
            }

            $resultData = $this->resolveDataBySaveMode($device, $payload);
            $trackerHistoryData = $resultData['trackerHistoryData'];
            $trackerHistoryLast = $resultData['trackerHistoryLast'];
            $totalResultData[] = $resultData;

            if ($trackerHistoryData && $device->getClientId()) {
                $this->trackerProviderService
                    ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
            }

            // @todo uncomment below if we need OBD events
//            $records = $data['response'];
//            $eventsData = $this->handleOBDEvents($records, $device);
//
//            if ($eventsData && $device->getClientId()) {
//                $this->trackerProviderService->trackerEventNotification($device, $eventsData);
//            }
        }

        return $totalResultData;
    }

    public function handleOBDSingle(array $data): ?array
    {
        $payload = json_encode($data);
        $model = new StreamaxOBD(reset($data['data']));
        $device = $this->getDeviceByUniqueId($model->getImei());

        if (!$device) {
            return null;
        }

        $resultData = $this->resolveDataBySaveMode($device, $payload);
        $trackerHistoryData = $resultData['trackerHistoryData'];
        $trackerHistoryLast = $resultData['trackerHistoryLast'];
        $totalResultData[] = $resultData;

        if ($trackerHistoryData && $device->getClientId()) {
            $this->trackerProviderService
                ->trackerPositionNotification($device, $trackerHistoryData, $trackerHistoryLast);
        }

        // @todo uncomment below if we need OBD events
//            $records = $data['response'];
//            $eventsData = $this->handleOBDEvents($records, $device);
//
//            if ($eventsData && $device->getClientId()) {
//                $this->trackerProviderService->trackerEventNotification($device, $eventsData);
//            }

        return $totalResultData;
    }

    public function handleOnlineState(array $data): ?array
    {
        if (!isset($data['data'])) {
            return null;
        }

        $totalResultData = [];
        $dataByImei = $this->formatDataByImei($data, StreamaxOnlineState::class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $payload = json_encode($data);
            $device = $this->getDeviceByUniqueId($imei);

            if (!$device) {
                continue;
            }

            $this->updateDeviceLastDataReceivedAt($device);
            $totalResultData[] = [
                'imei' => $imei,
                'deviceId' => $device->getId(),
                'response' => $this->getDataResponse($data, $payload, $device),
            ];
            $decoder = new StreamaxApiDecoder($payload);
            $records = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
            $eventsData = $this->handleOnlineEvents($records, $device);

            if ($eventsData && $device->getClientId()) {
                $this->trackerProviderService->trackerEventNotification($device, $eventsData);
            }
        }

        return $totalResultData;
    }

    public function handleOnlineStateSingle(array $data): ?array
    {
        $payload = json_encode($data);
        $model = new StreamaxOnlineState(reset($data['data']));
        $device = $this->getDeviceByUniqueId($model->getImei());

        if (!$device) {
            return null;
        }

        $this->updateDeviceLastDataReceivedAt($device);
        $totalResultData[] = [
            'imei' => $model->getImei(),
            'deviceId' => $device->getId(),
            'response' => $this->getDataResponse($data, $payload, $device),
        ];
        $decoder = new StreamaxApiDecoder($payload);
        $records = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
        $eventsData = $this->handleOnlineEvents($records, $device);

        if ($eventsData && $device->getClientId()) {
            $this->trackerProviderService->trackerEventNotification($device, $eventsData);
        }

        return $totalResultData;
    }

    public function handleDownloadState(array $data): ?array
    {
        if (!isset($data['data']) || !isset($data['tenantId'])) {
            return null;
        }

        $payload = json_encode($data);
        $alarmIds = $this->getAlarmIdsFromDownloadStateData($data);
        $streamaxIntegration = $this->getStreamaxIntegrationByTenantId($data['tenantId']);

        if (!$alarmIds) {
            return null;
        }

        return $this->saveFilesFromAlarmIds($alarmIds, $payload, $streamaxIntegration);
    }

    public function handleIButton(array $data): ?array
    {
        if (!isset($data['data'])) {
            return null;
        }

        $dataByImei = $this->formatDataByImei($data, StreamaxIButton::class);

        foreach ($dataByImei as $imei => $datumByImei) {
            $payload = json_encode($data);
            $device = $this->getDeviceByUniqueId($imei);

            if (!$device) {
                continue;
            }

            $this->updateDeviceLastDataReceivedAt($device);
            $decoder = new StreamaxApiDecoder($payload);
            $records = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
            $lastRecords = ['data' => [['record' => end($records)]]];
            $this->eventDispatcher->dispatch(
                new DriverSensorIdReceivedEvent($device, $lastRecords),
                DriverSensorIdReceivedEvent::NAME
            );
        }

        return [];
    }

    public function handleIButtonSingle(array $data): ?array
    {
        $payload = json_encode($data);
        $model = new StreamaxIButton(reset($data['data']));
        $device = $this->getDeviceByUniqueId($model->getImei());

        if (!$device) {
            return null;
        }

        $this->updateDeviceLastDataReceivedAt($device);
        $decoder = new StreamaxApiDecoder($payload);
        $records = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
        $lastRecords = ['data' => [['record' => end($records)]]];
        $this->eventDispatcher->dispatch(
            new DriverSensorIdReceivedEvent($device, $lastRecords),
            DriverSensorIdReceivedEvent::NAME
        );

        return [];
    }

    /**
     * @param Device $device
     * @param string $alarmId
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function deviceEvidence(
        Device $device,
        string $alarmId,
        string $type = self::FILE_TYPE_VIDEO
    ): array {
        $this->initHttpClient($device->getStreamaxIntegration());
        $params = [
            'alarmIds' => $alarmId,
            'fileType' => $type
        ];
        $evidenceFilesData = $this->request("/evidence/file", 'GET', ['body' => json_encode($params)]);

        return $evidenceFilesData['data'] ?? [];
    }

    /**
     * @param string $alarmId
     * @return array|mixed|stdClass|null
     * @throws Exception
     */
    public function alarmFiles(string $alarmId)
    {
        $params = [
            'alarmIds' => $alarmId,
        ];

        return $this->request("/v2/alarms/file-download", 'GET', ['query' => $params]);
    }

    /**
     * @param array $alarmIds
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return array
     * @throws Exception
     */
    public function alarmsFiles(array $alarmIds, ?StreamaxIntegration $streamaxIntegration): array
    {
        $this->initHttpClient($streamaxIntegration);
        $alarmsTotalData = [];
        $alarmIds = array_filter($alarmIds, function ($alarmId) {
            return boolval($alarmId);
        });
        $alarmsIdsChunks = array_chunk($alarmIds, 50);

        foreach ($alarmsIdsChunks as $alarmsIdsChunk) {
            $params = [
                'alarmIds' => implode(',', $alarmsIdsChunk),
            ];

            $alarmsData = $this->request("/v2/alarms/file-download", 'GET', ['query' => $params]);
            $alarmsData = $alarmsData['data'] ?? [];
            $alarmsTotalData = array_merge($alarmsTotalData, $alarmsData);
        }

        return $alarmsTotalData;
    }

    /**
     * @param array $alarmIds
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return array
     * @throws Exception
     */
    public function alarmsFilesFiltered(array $alarmIds, ?StreamaxIntegration $streamaxIntegration): array
    {
        $alarmsData = $this->alarmsFiles($alarmIds, $streamaxIntegration);

        return array_filter($alarmsData, function ($item) {
            return isset($item['files']) && $item['files'];
        });
    }

    /**
     * @param array $data
     * @return StreamaxIntegration
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createIntegration(array $data): StreamaxIntegration
    {
        $streamaxIntegration = new StreamaxIntegration($data);
        $this->validate($this->validator, $streamaxIntegration);
        $this->em->persist($streamaxIntegration);
        $this->em->flush();

        return $streamaxIntegration;
    }

    /**
     * @param array $data
     * @return StreamaxIntegration
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function integrationList(): array
    {
        return $this->em->getRepository(StreamaxIntegration::class)->findAll();
    }

    /**
     * @param int $integrationId
     * @param int $deviceId
     * @return Device|null
     * @throws \Doctrine\ORM\Exception\NotSupported
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setDeviceToIntegration(int $integrationId, int $deviceId): ?Device
    {
        $streamaxIntegration = $this->em->getRepository(StreamaxIntegration::class)->find($integrationId);

        if (!$streamaxIntegration) {
            throw new NotFoundHttpException('Streamax integration is not found');
        }

        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            throw new NotFoundHttpException('Device is not found');
        }
        if ($device->getVendorName() !== DeviceVendor::VENDOR_STREAMAX) {
            throw new NotFoundHttpException('Device vendor is not Streamax');
        }

        $result = $this->deleteDeviceByUniqueId($device->getImei(), $device->getStreamaxIntegration());
        $result = $this->createDevice($device->toStreamaxEntity(), $streamaxIntegration);
        $device->setStreamaxIntegration($streamaxIntegration);
        $this->em->flush();

        return $device;
    }

    /**
     * @return StreamaxIntegration
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getDefaultIntegration(): StreamaxIntegration
    {
        return $this->getStreamaxIntegrationByTenantId($this->tenantId);
    }

    /**
     * @param string $uniqueId
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return array|mixed|stdClass|null
     * @throws Exception
     */
    public function wakeupDevice(string $uniqueId, ?StreamaxIntegration $streamaxIntegration)
    {
        $this->initHttpClient($streamaxIntegration);

        return $this->request('/v2/devices/' . $uniqueId . '/wakeup', 'POST');
    }

    /**
     * @inheritDoc
     */
    public function parseFromTcp(
        mixed   $payload,
        ?string $socketId = null,
        ?string $imei = null
    ) {
        return $this->handleInbound($payload);
    }

    public function parseFromTcpDirect(array $payload)
    {
        return $this->handleInboundDirect($payload);
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
        TrackerPayload      $trackerPayload,
        Device              $device,
        ?TrackerHistory     $prevTh
    ): TrackerHistory {
        /** @var StreamaxData $record */
        $gpsData = $record->getGpsData();
        $speed = $gpsData->getSpeed();
        $trackerHistory = new TrackerHistory();
        $trackerHistory->setTrackerPayload($trackerPayload);
        $trackerHistory->setDevice($device);
        $trackerHistory->setTeam($device->getTeam());
        $trackerHistory->setTs($record->getDateTime());
        $trackerHistory->setLat($gpsData->getLatitude());
        $trackerHistory->setLng($gpsData->getLongitude());
        $trackerHistory->setAlt($gpsData->getAltitude());
        $trackerHistory->setAngle($gpsData->getAngle());
        $trackerHistory->setVehicle($device->getVehicle());
        $trackerHistory->setDriver($device->getVehicle()?->getDriver());
        $trackerHistory->setSpeed($speed);
        $trackerHistory->setMovement($record->getMovement());
        $ignition = ($device->isFixWithSpeed() && ($speed > 0)) ? 1 : $record->getIgnition();
        $trackerHistory->setIgnition($ignition);
        $trackerHistory->setBatteryVoltage($record->getBatteryVoltageMilli());
        $trackerHistory->setOdometer(
            $record->getOdometer(),
            $device->getLastTrackerRecord(),
            $device->getVendorName()
        );
        $trackerHistory->setTemperatureLevel($record->getDeviceTemperatureMilli());
        $trackerHistory->setEngineOnTime($record->getEngineOnTime());
        // @todo uncomment to save extra data in db
//        $trackerHistory->setOBDExtraData($record->getOBDData()?->toArray());
//        $trackerHistory->setAlarmExtraData($record->getAlarmData()?->toArray());
        $trackerHistory->setIsSOSButton($record->isPanicButton());
        $trackerHistory->setSatellites($record->getSatellites());

        $this->em->persist($trackerHistory);
        $this->em->flush();

        return $trackerHistory;
    }

    /**
     * @inheritDoc
     */
    public function getVendorName(): string
    {
        return DeviceVendor::VENDOR_STREAMAX;
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
    public function parseFromSms(string $payloadFromDevice)
    {
        // TODO: Implement parseFromSms() method.
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
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getQueryDataForLogs(Device $device, $dateFrom, $dateTo)
    {
        return $this->em->getRepository(TrackerHistory::class)
            ->getQueryDataForStreamaxLogs($device, $dateFrom, $dateTo);
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
     * @inheritDoc
     */
    public function saveCommandForTracker(
        Device                  $device,
        ?User                   $currentUser,
        TrackerCommandInterface $commandModel
    ): ?TrackerCommand {
        return null;
    }

    /**
     * @param array $data
     * @param Device $device
     * @return array
     */
    public function handleAlarmEvents(
        array  $data,
        Device $device
    ): array {
        $events = [];
        /** @var StreamaxData|null $record */
        foreach ($data as $record) {
            $alarmData = $record->getAlarmData();

            if ($alarmData) {
                $events[] = (new TrackerProviderEvent(
                    SocketEventEnum::Alarm->value,
                    SocketEventEnum::SourceStreamax,
                    $record->getDateTime(),
                    ['type' => $alarmData->getAlarmType()]
                ))->toArray();
            }
        }

        return $events;
    }

    public function handleOBDEvents(
        array  $data,
        Device $device
    ): array {
        $events = [];
        /** @var StreamaxData|null $record */
        foreach ($data as $record) {
            $OBDData = $record->getOBDData();

            if ($OBDData) {
                $events[] = (new TrackerProviderEvent(
                    SocketEventEnum::Obd->value,
                    SocketEventEnum::SourceStreamax,
                    $record->getDateTime(),
                    ['type' => $OBDData->getType()]
                ))->toArray();
            }
        }

        return $events;
    }

    /**
     * @param array $data
     * @param Device $device
     * @return array
     */
    public function handleOnlineEvents(
        array  $data,
        Device $device
    ): array {
        $events = [];
        /** @var StreamaxData|null $record */
        foreach ($data as $record) {
            $onlineStateData = $record->getOnlineStateData();

            if ($onlineStateData) {
                $eventName = $onlineStateData->isOnline()
                    ? SocketEventEnum::Online->value
                    : SocketEventEnum::Offline->value;
                $events[] = (new TrackerProviderEvent(
                    $eventName,
                    SocketEventEnum::SourceStreamax,
                    $record->getDateTime()
                ))->toArray();
            }
        }

        return $events;
    }

    /**
     * @inheritDoc
     */
    public function saveSensorDataFromRecord(
        DeviceDataInterface $data,
        TrackerPayload      $trackerPayload,
        TrackerHistory      $trackerHistory,
        Device              $device
    ): void {
        match ($data->getType()) {
            StreamaxData::TYPE_GPS => $this->handleGPSData($data, $trackerHistory, $device),
            StreamaxData::TYPE_ALARM => $this->handleAlarmData($data, $trackerHistory, $device),
            StreamaxData::TYPE_OBD => $this->handleOBDData($data, $trackerHistory, $device),
            default => null
        };
    }

    /**
     * @inheritDoc
     */
    public function encodePayloadFromDB(string $payload)
    {
        // @todo check
        return json_decode($payload, true);
    }

    /**
     * @param string $uniqueId
     * @param string $message
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return array|mixed|stdClass|null
     * @throws Exception
     */
    public function TTSToDevice(string $uniqueId, string $message, ?StreamaxIntegration $streamaxIntegration)
    {
        return $this->TTSToDevices([$uniqueId], $message, $streamaxIntegration);
    }

    /**
     * @param array $uniqueIds
     * @param string $message
     * @param StreamaxIntegration|null $streamaxIntegration
     * @return array|mixed|stdClass|null
     * @throws Exception
     */
    public function TTSToDevices(array $uniqueIds, string $message, ?StreamaxIntegration $streamaxIntegration)
    {
        $this->initHttpClient($streamaxIntegration);

        $result = $this->request('/tts', 'POST', [
            'json' => [
                'uniqueIds' => implode(',', $uniqueIds),
                'message' => $message
            ]
        ]);

        if (isset($result['success']) && !$result['success']) {
            $this->logger->error($result['message'] ?? 'Unknown error', ['imei' => $uniqueIds]);
            return false;
        }

        return $result;
    }

    public function deviceInfo(
        string               $imei,
        ?StreamaxIntegration $streamaxIntegration
    ): ?StreamaxDeviceInfo {
        $this->initHttpClient($streamaxIntegration);
        $data = [
            'uniqueId' => $imei,
            'module' => 'DEVEMM',
            'operation' => 'GETDEVINFOSTATUS',
            'parameter' => new \stdClass()
        ];
        $result = $this->request("/device/config/query", 'POST', ['json' => $data]);

        return $result && $result['data'] ? new StreamaxDeviceInfo($result['data']) : null;
    }

    public function deviceInfoVoltage(
        string               $imei,
        ?StreamaxIntegration $streamaxIntegration
    ): ?float {
        $deviceInfo = $this->deviceInfo($imei, $streamaxIntegration);

        return $deviceInfo?->getVoltageMilli();
    }

    public function processAlarmVideos(int $deviceId, string $payload, array $trackerHistoryIDs): void
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            return;
        }

        $decoder = new StreamaxApiDecoder($payload);
        $records = $decoder->orderByDateTime($decoder->decodeData($payload, $device));
        $trackerHistories = $this->em->getRepository(TrackerHistory::class)
            ->getTrackerHistoriesByIds($trackerHistoryIDs);
        $alarmIds = $this->getAlarmIdsFromAlarmData($records);
        $alarmsFilesData = $this->alarmsFiles($alarmIds, $device->getStreamaxIntegration());

        /** @var StreamaxData $record */
        foreach ($records as $record) {
            $alarmData = $record->getAlarmData();

            if ($alarmData) {
                $alarmId = $alarmData->getAlarmId();
                $trackerHistory = $this->getRelatedTrackerHistory($record, $trackerHistories);
                $alarmFilesData = $this->getRelatedAlarmFilesData($alarmId, $alarmsFilesData);

                if (!$alarmFilesData) {
                    continue;
                }

                $deviceCameraEvent = $this->createCameraEventByAlarm($device, $alarmId, $alarmData, $trackerHistory);
                $this->createCameraEventFiles($deviceCameraEvent, $alarmFilesData);
            }
        }
    }

    public function updateByExtraData(Device $device, TrackerHistory $trackerHistory): void
    {
        $this->updateVoltageByExtraData($device, $trackerHistory);
    }

    public function parseFromTcpProxy(mixed $payload)
    {
        return $this->handleInboundProxy($payload);
    }

    public function handleInboundProxy(array $data): ?array
    {
        $type = $data['type'] ?? null;

        if (!isset($data['data'])) {
            return null;
        }

        $this->saveProxyPayload($data);

        return match ($type) {
            StreamaxData::TYPE_GPS,
//            StreamaxData::TYPE_ALARM,
            StreamaxData::TYPE_OBD,
            StreamaxData::TYPE_ONLINE_STATE,
            StreamaxData::TYPE_IBUTTON => $this->handleDeviceDataFromInboundProxy($data, $type),
//            StreamaxData::TYPE_DOWNLOAD_STATE => $this->handleDownloadStateFromInboundProxy($data),
            default => null
        };
    }
}
