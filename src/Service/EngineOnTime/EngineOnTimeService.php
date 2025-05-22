<?php

namespace App\Service\EngineOnTime;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Service\BaseService;
use App\Service\EngineOnTime\Vendor\BaseVendor;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

class EngineOnTimeService extends BaseService
{
    public EntityManager $em;
    public LoggerInterface $logger;
    public MemoryDbService $memoryDb;

    private function formatTrackerHistoryData(TrackerHistory $lastTrackerHistory): ?array
    {
        return [
            'id' => $lastTrackerHistory->getId(),
            'ts' => $lastTrackerHistory->getTs(),
            'engineOnTime' => $lastTrackerHistory->getEngineOnTime(),
            'ignition' => $lastTrackerHistory->getIgnition(),
        ];
    }

    private function formatLastTrackerHistoryData(TrackerHistoryLast $lastTrackerHistory): ?array
    {
        return [
            'id' => $lastTrackerHistory->getId(),
            'ts' => $lastTrackerHistory->getTs(),
            'engineOnTime' => $lastTrackerHistory->getEngineOnTime(),
            'ignition' => $lastTrackerHistory->getIgnition(),
        ];
    }

    public function __construct(EntityManager $em, LoggerInterface $logger, MemoryDbService $memoryDb)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->memoryDb = $memoryDb;
    }

    public function updateEngineOnTime(
        Device $device,
        array $trackerDataSet,
        bool $isFromCommand = false,
        ?array $lastTrackerHistoryData = null
    ) {
        $lastTrackerHistoryData = $lastTrackerHistoryData
            ?: ($device->getLastTrackerRecord()
                ? $this->formatLastTrackerHistoryData($device->getLastTrackerRecord())
                : null
            );
        $deviceVendorName = $device->getVendorName();
        $deviceModelName = $device->getModelName();
        $vehicle = $device->getVehicle();

        if (!$vehicle || !$deviceVendorName || !$deviceModelName) {
            return;
        }

        try {
            if (!$vehicleEngineOnTime = $this->memoryDb->get(VehicleRedisModel::getEngineOnTimeKey($vehicle))) {
                $vehicleEngineOnTime = $vehicle->getEngineOnTime();
                $this->memoryDb->set(VehicleRedisModel::getEngineOnTimeKey($vehicle), $vehicleEngineOnTime);
            }

            $trackerHistories = array_map(function ($item) use ($isFromCommand) {
                return ($isFromCommand) ? $item['th'] : $this->formatTrackerHistoryData($item['th']);
            }, $trackerDataSet['data']);
            $vendorModel = BaseVendor::resolve($deviceVendorName, $this->em);
            $engineOnTime = $vendorModel->calc(
                $device, $trackerHistories, $vehicleEngineOnTime, $lastTrackerHistoryData
            );
            $this->memoryDb->set(VehicleRedisModel::getEngineOnTimeKey($vehicle), $engineOnTime);
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
            return;
        }
    }

    public function getEngineOnTimeForTh(
        Device $device,
        TrackerHistory $trackerHistory,
        ?TrackerHistory $prevTrackerHistory
    ): ?int {
        $deviceVendorName = $device->getVendorName();
        $vehicle = $device->getVehicle();

        if (!$vehicle || !$deviceVendorName || !$device->getModelName()) {
            return null;
        }

        try {
            if (!$vehicleEngineOnTime = $this->memoryDb->get(VehicleRedisModel::getEngineOnTimeKey($vehicle))) {
                $vehicleEngineOnTime = $vehicle->getEngineOnTime();
                $this->memoryDb->set(VehicleRedisModel::getEngineOnTimeKey($vehicle), $vehicleEngineOnTime);
            }

            $vendorModel = BaseVendor::resolve($deviceVendorName, $this->em);
            $engineOnTime = $vendorModel->calcForTh(
                $trackerHistory, $prevTrackerHistory, $vehicleEngineOnTime, $device->getLastTrackerRecord()
            );
            $this->memoryDb->set(VehicleRedisModel::getEngineOnTimeKey($vehicle), $engineOnTime);

            return $engineOnTime;
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
            return null;
        }
    }
}
