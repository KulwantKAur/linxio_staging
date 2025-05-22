<?php

namespace App\EventListener\Device;

use App\Entity\Client;
use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\EntityHistory;
use App\Entity\Setting;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\Exceptions\ValidationException;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\DeviceRedisModel;
use App\Service\Setting\SettingService;
use App\Service\Streamax\StreamaxService;
use App\Service\Traccar\TraccarService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeviceEntityListener
{
    private $tokenStorage;
    private $cache = [];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        private               readonly ObjectPersister $clientObjectPersister,
        private               readonly SettingService $settingService,
        private               readonly EntityHistoryService $entityHistoryService,
        private               readonly EntityManager $entityManager,
        private               readonly TraccarService $traccarService,
        private               readonly StreamaxService $streamaxService,
        private               readonly LoggerInterface $logger,
        private               readonly MemoryDbService $memoryDb
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    private function handleStatus(Device $device, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('status')) {
            if (in_array($device->getStatus(), Device::ACTIVE_STATUSES_LIST)) {
                $lastDeviceRecord = $device->getLastTrackerRecord();

                if ($lastDeviceRecord) {
                    $device->setStatusUpdatedAt($lastDeviceRecord->getTs());
                }
            }

            $this->entityHistoryService->setEntityManager($this->entityManager);
            $createdById = $this->tokenStorage->getToken() &&
            $this->tokenStorage->getToken()->getUser() instanceof User
                ? $this->tokenStorage->getToken()->getUser()->getId() : null;
            $this->entityHistoryService->create(
                $device,
                $device->getStatus(),
                EntityHistoryTypes::DEVICE_STATUS,
                null,
                $createdById
            );
        }
    }

    private function handleUpdateVendorTraccar(Device $device): void
    {
        if ($device->getVendorName() != DeviceVendor::VENDOR_TRACCAR) {
            throw new ValidationException(
                'Device vendor for this parser type should be: ' . DeviceVendor::VENDOR_TRACCAR
            );
        }

        $traccarDevice = $this->traccarService->deviceByImei($device->getImei());

        if (!$traccarDevice) {
            $traccarDevice = $this->traccarService->createDeviceFromDevice($device);
        }
        if ($traccarDevice && $traccarDevice->isDisabled()) {
            $this->traccarService->setDeviceEnabled($traccarDevice);
        }
    }

    private function handleUpdateVendorDefault(Device $device): void
    {

    }

    private function handleUpdateVendorStreamax(Device $device): void
    {
        if ($device->getVendorName() != DeviceVendor::VENDOR_STREAMAX) {
            throw new ValidationException(
                'Device vendor for this parser type should be: ' . DeviceVendor::VENDOR_STREAMAX
            );
        }

        $streamaxDevice = $this->streamaxService->getDeviceData($device->getImei(), $device->getStreamaxIntegration());

        if (!$streamaxDevice) {
            $streamaxDevice = $this->streamaxService->createDeviceFromDevice($device);
        }
    }

    private function clearTraccarRelations(Device $device): void
    {
        $traccarDevice = $this->traccarService->deviceByImei($device->getImei());

        if ($traccarDevice && $traccarDevice->isEnabled()) {
            $this->traccarService->setDeviceDisabled($traccarDevice);
        }
    }

    private function clearStreamaxRelations(Device $device): void
    {
        if (!$device->getStreamaxIntegration()) {
            $streamaxIntegration = $this->streamaxService->getDefaultIntegration();
            $device->setStreamaxIntegration($streamaxIntegration);
        }

        $streamaxDevice = $this->streamaxService->getDeviceData($device->getImei(), $device->getStreamaxIntegration());

        if ($streamaxDevice) {
            $streamaxDevice = $this->streamaxService
                ->deleteDeviceByUniqueId($device->getImei(), $device->getStreamaxIntegration());
        }
    }

    private function preUpdateVendorRelations(Device $device): void
    {
        switch ($device->getParserType()) {
            case DeviceModel::PARSER_TRACCAR:
                $this->clearStreamaxRelations($device);
                break;
            case DeviceModel::PARSER_STREAMAX:
                $this->clearTraccarRelations($device);
                break;
            case DeviceModel::PARSER_CUSTOM:
            default:
                $this->clearTraccarRelations($device);
                $this->clearStreamaxRelations($device);
                break;
        }
    }

    private function handleParserType(Device $device, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('model')) {
            try {
                $this->preUpdateVendorRelations($device);

                match ($device->getParserType()) {
                    DeviceModel::PARSER_TRACCAR => $this->handleUpdateVendorTraccar($device),
                    DeviceModel::PARSER_STREAMAX => $this->handleUpdateVendorStreamax($device),
                    default => $this->handleUpdateVendorDefault($device)
                };
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['name' => TraccarService::class]);
                throw new \Exception($e->getMessage());
            }
        }
    }

    private function handlePersistVendorTraccar(Device $device): void
    {
        $traccarDevice = $this->traccarService->deviceByImei($device->getImei());

        if (!$traccarDevice) {
            $traccarDevice = $this->traccarService->createDeviceFromDevice($device);
        }

        $device->setTraccarDeviceId($traccarDevice->getId());
    }

    private function handlePersistVendorStreamax(Device $device): void
    {
        if (!$device->getStreamaxIntegration()) {
            $streamaxIntegration = $this->streamaxService->getDefaultIntegration();
            $device->setStreamaxIntegration($streamaxIntegration);
        }

        $streamaxDevice = $this->streamaxService->getDeviceData($device->getImei(), $device->getStreamaxIntegration());

        if (!$streamaxDevice) {
            $streamaxDevice = $this->streamaxService->createDeviceFromDevice($device);
        }
    }

    private function handleFixWithSpeed(Device $device): void
    {
        if ($device->isFixWithSpeedByDefault()) {
            $device->setIsFixWithSpeed(true);
        }
    }

    private function postUpdateVendorRelations(Device $device): void
    {
        switch ($device->getParserType()) {
            case DeviceModel::PARSER_TRACCAR:
                if (!$device->getTraccarDeviceId()) {
                    $traccarDevice = $this->traccarService->deviceByImei($device->getImei());

                    if ($traccarDevice) {
                        $device->setTraccarDeviceId($traccarDevice->getId());
                    }
                }

                break;
            case DeviceModel::PARSER_STREAMAX:
            case DeviceModel::PARSER_CUSTOM:
            default:
                if ($device->getTraccarDeviceId()) {
                    $device->setTraccarDeviceId(null);
                }

                break;
        }

        $this->entityManager->flush();
    }

    public function preUpdate(Device $device, PreUpdateEventArgs $event)
    {
        $this->handleStatus($device, $event);
        $this->handleContractFinishAt($device, $event);
        $this->handleParserType($device, $event);
        $this->handleChangedFields($device, $event);
    }

    public function postUpdate(Device $device)
    {
        $this->postUpdateVendorRelations($device);
    }

    public function postLoad(Device $device, PostLoadEventArgs $args)
    {
        $device->setEntityManager($this->entityManager);

        if ($this->cache['gpsStatusDuration'][$device->getTeamId()] ?? null) {
            $device->setGpsStatusDuration($this->cache['gpsStatusDuration'][$device->getTeamId()]);
        } else {
            $gpsStatusDurationSetting = $this->settingService
                ->getTeamSettingValueByKey($device->getTeam(), Setting::GPS_STATUS_DURATION);
            $gpsStatusDuration = $gpsStatusDurationSetting && $gpsStatusDurationSetting['enable']
                ? $gpsStatusDurationSetting['value']
                : Client::DEFAULT_GPS_STATUS_DURATION;
            $this->cache['gpsStatusDuration'][$device->getTeamId()] = $gpsStatusDuration;
            $device->setGpsStatusDuration($gpsStatusDuration);
        }

        if ($deviceLastDataReceived = $this->cache['lastDataReceived'][$device->getId()] ?? null) {
            $device->setLastDataReceivedAt((new \DateTime())->setTimestamp($deviceLastDataReceived));
        } else {
            if ($deviceLastDataReceived = $this->memoryDb->get(DeviceRedisModel::getLastDataReceivedKey($device))) {
                $this->cache['lastDataReceived'][$device->getId()] = $deviceLastDataReceived;
                $device->setLastDataReceivedAt((new \DateTime())->setTimestamp($deviceLastDataReceived));
            }
        }

        return $device;
    }

    public function postPersist(Device $device)
    {
        try {
            match ($device->getParserType()) {
                DeviceModel::PARSER_TRACCAR => $this->handlePersistVendorTraccar($device),
                DeviceModel::PARSER_STREAMAX => $this->handlePersistVendorStreamax($device),
                default => null
            };

            $this->handleFixWithSpeed($device);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['name' => $e->getFile()]);
            throw new \Exception($e->getMessage());
        }
    }

    public function prePersist(Device $device, PrePersistEventArgs $args)
    {
        $device->setEntityManager($this->entityManager);

        return $device;
    }

    private function handleContractFinishAt(Device $device, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('contractFinishAt')) {
            $client = $device->getClientEntity();
            if ($client) {
                $this->clientObjectPersister->replaceOne($client);
            }
        }
    }

    public function handleChangedFields(Device $device, PreUpdateEventArgs $event)
    {
        $fields = $event->getEntityChangeSet();

        $createdBy = $this->tokenStorage->getToken() &&
        $this->tokenStorage->getToken()->getUser() instanceof User
            ? $this->tokenStorage->getToken()->getUser() : null;
        $payload = [];

        foreach ($fields as $key => $field) {
            if (in_array($key, [
                'updatedAt',
                'updatedBy',
                'hw',
                'sw',
                'vehicle',
                'status',
                'team',
                'statusExt',
                'traccarDeviceId',
                'lastTrackerRecord'
            ])) {
                continue;
            }
            $payload[] = [$key => EntityHistory::preparePayload($field[1] ?? '')];
        }

        if (!$payload) {
            return;
        }

        $this->entityHistoryService->create(
            $device,
            json_encode($payload),
            EntityHistoryTypes::DEVICE_FIELDS,
            $createdBy
        );
    }
}