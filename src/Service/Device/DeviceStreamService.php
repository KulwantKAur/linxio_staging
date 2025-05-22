<?php

namespace App\Service\Device;

use App\Entity\Device;
use App\Entity\DeviceCameraEvent;
use App\Entity\DeviceCameraEventFile;
use App\Entity\DeviceCameraEventType;
use App\Entity\DeviceVendor;
use App\Entity\User;
use App\Service\BaseService;
use App\Service\Tracker\Stream\TrackerStreamFactory;
use App\Service\Tracker\Stream\TrackerStreamService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceStreamService extends BaseService
{
    public const TYPE_OUTWARD = 'outward';
    public const TYPE_DMS = 'dms';
    public const TYPE_3 = 'ext1';
    public const TYPE_4 = 'ext2';
    public const TYPE_5 = 'ext3';
    public const TYPE_6 = 'ext4';
    public const TYPE_ALL = 'all';

    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface        $logger,
        private TranslatorInterface    $translator,
        private TrackerStreamFactory   $trackerStreamFactory,
        private ?TrackerStreamService  $trackerStreamService,
    ) {
    }

    /**
     * @param Device $device
     * @throws \Exception
     */
    private function initTrackerServicesByDevice(Device $device)
    {
        $this->trackerStreamService = null;
        $this->trackerStreamService = $this->trackerStreamFactory->getInstance($device->getVendorName(), $device);
    }

    /**
     * @param string $vendorName
     * @throws \Exception
     */
    private function initTrackerServicesByVendorName(string $vendorName)
    {
        $this->trackerStreamService = null;
        $this->trackerStreamService = $this->trackerStreamFactory->getInstance($vendorName);
    }

    /**
     * @param array $cameraEvents
     * @return array
     * @throws \Exception
     */
    private function updateEventsByVendor(array $cameraEvents): array
    {
        $this->initTrackerServicesByVendorName(DeviceVendor::VENDOR_STREAMAX);
        $cameraEvents = $this->trackerStreamService->updateEventFilesLinks($cameraEvents);
        // @todo update by each vendor if it's needed

        return $cameraEvents;
    }

    /**
     * @param array $cameraEventFiles
     * @return array
     * @throws \Exception
     */
    private function updateEventFilesByVendor(array $cameraEventFiles): array
    {
        $this->initTrackerServicesByVendorName(DeviceVendor::VENDOR_STREAMAX);
        $cameraEventFiles = $this->trackerStreamService->updateEventFilesLinksFromHistory($cameraEventFiles);
        // @todo update by each vendor if it's needed

        return $cameraEventFiles;
    }

    /**
     * @param Device $device
     * @return array|null
     */
    public function getVideoData(Device $device): ?array
    {
        try {
            $this->initTrackerServicesByDevice($device);

            return $this->trackerStreamService->getStreamData($device);
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw $e;
        }
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return PaginationInterface|null
     */
    public function getCameraEvents(array $data, User $currentUser): ?PaginationInterface
    {
        try {
            $deviceId = $params['deviceId'] ?? null;
            $device = $deviceId ? $this->em->getRepository(Device::class)->find($deviceId) : null;

            if ($device) {
                $this->initTrackerServicesByDevice($device);
            }

            $eventsPagination = $this->trackerStreamService->getCameraEvents($data, $currentUser);
            $items = $this->updateEventsByVendor($eventsPagination->getItems());
            $eventsPagination->setItems($this->formatNestedItemsToArray(
                $items,
                DeviceCameraEvent::DEFAULT_DISPLAY_VALUES,
                $currentUser
            ));

            return $eventsPagination;
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function getCameraEventTypes(): array
    {
        return $this->em->getRepository(DeviceCameraEventType::class)->findAll();
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return PaginationInterface|null
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getCamerasHistory(array $data, User $currentUser): ?PaginationInterface
    {
        try {
            $deviceId = $params['deviceId'] ?? null;
            $device = $deviceId ? $this->em->getRepository(Device::class)->find($deviceId) : null;

            if ($device) {
                $this->initTrackerServicesByDevice($device);
            }

            $eventFilesPagination = $this->trackerStreamService->getCamerasHistory($data, $currentUser);
            $items = $this->updateEventFilesByVendor($eventFilesPagination->getItems());
            $eventFilesPagination->setItems($this->formatNestedItemsToArray(
                $items,
                array_merge(DeviceCameraEventFile::DEFAULT_DISPLAY_VALUES, ['eventType']),
                $currentUser
            ));

            return $eventFilesPagination;
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw $e;
        }
    }

    /**
     * @param Device $device
     * @return bool
     * @throws \Exception
     */
    public function wakeupDevice(Device $device): bool
    {
        try {
            $this->initTrackerServicesByDevice($device);

            return $this->trackerStreamService->wakeupDevice($device);
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw $e;
        }
    }

    /**
     * @param Device $device
     * @param string $text
     * @return bool
     * @throws \Exception
     */
    public function sendTTSToDevice(Device $device, string $text): bool
    {
        try {
            $this->initTrackerServicesByDevice($device);

            return $this->trackerStreamService->sendTTSToDevice($device, $text);
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw $e;
        }
    }

    /**
     * @return array[]
     */
    public function getCameraTypes(): array
    {
        return [[
            'id' => DeviceCameraEventFile::CAMERA_TYPE_OUTWARD_ID,
            'name' => DeviceStreamService::TYPE_OUTWARD,
            'label' => $this->translator->trans('camera_type.outward'),
        ],[
            'id' => DeviceCameraEventFile::CAMERA_TYPE_DMS_ID,
            'name' => DeviceStreamService::TYPE_DMS,
            'label' => $this->translator->trans('camera_type.dms'),
        ],[
            'id' => DeviceCameraEventFile::CAMERA_TYPE_3_ID,
            'name' => DeviceStreamService::TYPE_3,
            'label' => $this->translator->trans('camera_type.ext1'),
        ],[
            'id' => DeviceCameraEventFile::CAMERA_TYPE_4_ID,
            'name' => DeviceStreamService::TYPE_4,
            'label' => $this->translator->trans('camera_type.ext2'),
        ],[
            'id' => DeviceCameraEventFile::CAMERA_TYPE_5_ID,
            'name' => DeviceStreamService::TYPE_5,
            'label' => $this->translator->trans('camera_type.ext3'),
        ],[
            'id' => DeviceCameraEventFile::CAMERA_TYPE_6_ID,
            'name' => DeviceStreamService::TYPE_6,
            'label' => $this->translator->trans('camera_type.ext4'),
        ]];
    }
}
