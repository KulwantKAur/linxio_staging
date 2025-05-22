<?php

namespace App\Service\Device\DeviceQueue\EngineOnTime\Vendor;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;

/*
 * @todo change logic for if devises don't send it correctly
 */

class Teltonika extends BaseVendor
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
        $engineOnTime = 0;
        $prevEngineOnTime = $vehicle->getEngineOnTime();
        foreach ($trackerDataSet as $key => $th) {
            if (is_null($th['engineOnTime'])) {
                continue;
            }

            $previousTH = ($key == 0)
                ? ($this->handleLastTHWithDateTime($lastTrackerHistoryData, $th['ts']))
                : $trackerDataSet[$key - 1];

            if ($previousTH) {
                if (!is_null($previousTH['engineOnTime'])) {
                    $engineOnTime += $th['engineOnTime'] - $previousTH['engineOnTime'];
                } else {
                    $engineOnTime += $th['engineOnTime'];
                }
            } else {
                $engineOnTime += $th['engineOnTime'];
            }

            self::$em->getRepository(TrackerHistory::class)
                ->updateEngineOnTime($th['id'], $engineOnTime + $prevEngineOnTime);
        }

        $vehicle->increaseEngineOnTime($engineOnTime);

        return $vehicle;
    }
}
