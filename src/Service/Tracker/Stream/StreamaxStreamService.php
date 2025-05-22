<?php

namespace App\Service\Tracker\Stream;

use App\Entity\Device;
use App\Entity\DeviceCameraEvent;
use App\Entity\DeviceCameraEventFile;
use App\Entity\DeviceVendor;
use App\Service\Device\DeviceStreamService;
use App\Service\Streamax\Model\StreamaxAlarmFile;
use App\Service\Streamax\Model\StreamaxFile;
use App\Service\Streamax\StreamaxService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;

class StreamaxStreamService extends TrackerStreamService
{
    private const API_STREAM_URL_TTL = 20;

    private function getPreparedAlarmsFiles(array $cameraEvents): array
    {
        $alarmIds = [];
        $devices = [];
        $streamaxIntegrations = [];
        $alarmsFilesData = [];

        /** @var DeviceCameraEvent $cameraEvent */
        foreach ($cameraEvents as $cameraEvent) {
            if ($cameraEvent->getDeviceVendorName() === DeviceVendor::VENDOR_STREAMAX) {
                $alarmIds[] = $cameraEvent->getRemoteId();
                $devices[] = $cameraEvent->getDevice();
            }
        }

        $devices = array_unique($devices);

        foreach ($devices as $device) {
            $streamaxIntegrations[] = $device->getStreamaxIntegration();
        }

        $streamaxIntegrations = array_unique($streamaxIntegrations);

        foreach ($streamaxIntegrations as $streamaxIntegration) {
            $alarmsFilesData = array_merge(
                $alarmsFilesData, $this->streamaxService->alarmsFilesFiltered($alarmIds, $streamaxIntegration)
            );
        }

        // @todo revert line below if we decline multiple accounts integrations
//        $alarmsFilesData = $this->streamaxService->alarmsFiles($alarmIds, null);

        return $alarmsFilesData;
    }

    /**
     * @param StreamaxService $streamaxService
     * @param EntityManager $em
     */
    public function __construct(
        private StreamaxService $streamaxService,
        private PaginatorInterface $paginator,
        protected EntityManager $em,
    ) {
        parent::__construct($paginator, $em);
    }

    /**
     * @inheritDoc
     */
    public function getStreamData(Device $device): ?array
    {
        $result = $this->streamaxService->deviceLiveStreamData($device);
        // @todo remove after verification
//        $result = ['data' => [
//            [
//            "url" => "https://{baseUrl}/video/c5dasda-3adf-3fa3-erwq3.flv",
//            "session" => "123wqe-12wqs-5frtg-5gdhk",
//            "channel" => 1
//            ]
//        ]];

        if ($result && isset($result['data'])) {
            foreach ($result['data'] as &$datum) {
                $datum['type'] = match ($datum['channel']) {
                    1 => DeviceStreamService::TYPE_OUTWARD,
                    2 => DeviceStreamService::TYPE_DMS,
                    3 => DeviceStreamService::TYPE_3,
                    4 => DeviceStreamService::TYPE_4,
                    5 => DeviceStreamService::TYPE_5,
                    6 => DeviceStreamService::TYPE_6,
                    default => null
                };
                $datum = (new StreamData(
                    $datum['type'],
                    $datum['url'],
                    true,
                    Carbon::now()->addSeconds(self::API_STREAM_URL_TTL)
                ))->toArray();
            }

            return $result['data'];
        }

        return parent::getStreamData($device);
    }

    /**
     * @param array $cameraEventFiles
     * @return array
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateEventFilesLinksFromHistory(array $cameraEventFiles): array
    {
        $cameraEvents = [];
        /** @var DeviceCameraEventFile $cameraEventFile */
        foreach ($cameraEventFiles as $cameraEventFile) {
            $cameraEvents[] = $cameraEventFile->getEvent();
        }

        $this->updateEventFilesLinks(array_unique($cameraEvents));

        return $cameraEventFiles;
    }

    /**
     * @param array $cameraEvents
     * @return array
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateEventFilesLinks(array $cameraEvents): array
    {
        $alarmsFilesData = $this->getPreparedAlarmsFiles($cameraEvents);

        /** @var DeviceCameraEvent $cameraEvent */
        foreach ($cameraEvents as $cameraEvent) {
            $alarmsArrayKey = array_search($cameraEvent->getRemoteId(), array_column($alarmsFilesData, 'alarmId'));

            if ($alarmsArrayKey !== false && isset($alarmsFilesData[$alarmsArrayKey])) {
                $cameraEventFiles = $cameraEvent->getFiles();
                $alarmFilesData = new StreamaxAlarmFile($alarmsFilesData[$alarmsArrayKey]);

                foreach ($cameraEventFiles as $cameraEventFile) {
                    $alarmFile = null;

                    foreach ($alarmFilesData->getFiles() as $alarmFilesDatum) {
                        $alarmFile = new StreamaxFile($alarmFilesDatum);

                        if ($cameraEventFile->getRemoteId() === $alarmFile->getFileId()) {
                            break;
                        }
                    }

                    if ($alarmFile) {
                        $cameraEventFile->setUrl($alarmFile->getUrl());
                    }
                }

            }
        }

        $this->em->flush();

        return $cameraEvents;
    }

    /**
     * @param Device $device
     * @return bool
     * @throws \Exception
     */
    public function wakeupDevice(Device $device): bool
    {
        $result = $this->streamaxService->wakeupDevice($device->getImei(), $device->getStreamaxIntegration());

        return boolval($result);
    }

    /**
     * @param Device $device
     * @param string $text
     * @return bool
     * @throws \Exception
     */
    public function sendTTSToDevice(Device $device, string $text): bool
    {
        $result = $this->streamaxService->TTSToDevices([$device->getImei()], $text, $device->getStreamaxIntegration());

        return boolval($result);
    }
}