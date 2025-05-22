<?php

namespace App\Service\EngineOnTime\Vendor;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Vehicle;

/*
 * @todo change logic for if devises don't send it correctly
 */

class Ulbotech extends BaseVendor
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
        $engineOnTime = $vehicleEngineOnTime;

        /** @var TrackerHistory $th */
        foreach ($trackerHistories as $key => $th) {
            $engineOnTime = $th['engineOnTime'] ?: $engineOnTime;
            self::$em->getRepository(TrackerHistory::class)->updateEngineOnTime($th['id'], $engineOnTime);
        }

        return intval($engineOnTime);
    }
}
