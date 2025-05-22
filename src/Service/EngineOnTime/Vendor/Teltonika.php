<?php

namespace App\Service\EngineOnTime\Vendor;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Vehicle;

/*
 * @todo change logic for if devises don't send it correctly
 */

class Teltonika extends BaseVendor
{
    /**
     * @inheritDoc
     */
    public function calc(
        Device $device,
        array $trackerHistories,
        ?int $vehicleEngineOnTime,
        ?array $lastTrackerHistoryData
    ): int {
        $engineOnTime = 0;
        $prevEngineOnTime = $vehicleEngineOnTime;

        foreach ($trackerHistories as $key => $th) {
            if (is_null($th['engineOnTime'])) {
                continue;
            }

            $previousTH = ($key == 0)
                ? ($this->handleLastTHWithDateTime($lastTrackerHistoryData, $th['ts']))
                : $trackerHistories[$key - 1];

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

        return $prevEngineOnTime + $engineOnTime;
    }
}
