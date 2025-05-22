<?php

namespace App\Service\Device\DeviceQueue\EngineOnTime\Vendor;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;

/*
 * @todo change logic for if devises don't send it correctly
 */

class Ulbotech extends BaseVendor
{
    /**
     * @param int $deviceId
     * @param array $trackerDataSet
     * @param Vehicle $vehicle
     * @param array|null $lastTrackerHistoryData
     * @return Vehicle
     */
    public function calc(
        int $deviceId,
        array $trackerDataSet,
        Vehicle $vehicle,
        ?array $lastTrackerHistoryData
    ): Vehicle {
        $engineOnTime = $vehicle->getEngineOnTime();

        foreach ($trackerDataSet as $key => $th) {
            $engineOnTime = $th['engineOnTime'] ?: $engineOnTime;

            self::$em->getRepository(TrackerHistory::class)->updateEngineOnTime($th['id'], $engineOnTime);
        }

        $vehicle->setEngineOnTime($engineOnTime);

        return $vehicle;
    }
}
