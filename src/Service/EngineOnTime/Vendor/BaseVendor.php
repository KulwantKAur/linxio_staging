<?php

namespace App\Service\EngineOnTime\Vendor;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use Doctrine\ORM\EntityManager;

class BaseVendor
{
    public static EntityManager $em;

    /**
     * @param Device $device
     * @param array $trackerHistories
     * @param int|null $vehicleEngineOnTime
     * @param array|null $lastTrackerHistoryData
     * @return int
     * @throws \Doctrine\ORM\Exception\NotSupported
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
            $previousTH = ($key == 0)
                ? $this->handleLastTHWithDateTime($lastTrackerHistoryData, $th['ts'])
                : $trackerHistories[$key - 1];

            if ($previousTH && $previousTH['ignition'] == 1) {
                $engineOnTime += $th['ts']->getTimestamp() - $previousTH['ts']->getTimestamp();
            }

            self::$em->getRepository(TrackerHistory::class)
                ->updateEngineOnTime($th['id'], $engineOnTime + $prevEngineOnTime);
        }

        return $prevEngineOnTime + $engineOnTime;
    }

    public function calcForTh(
        TrackerHistory $trackerHistory,
        ?TrackerHistory $prevTrackerHistory,
        ?int $vehicleEngineOnTime,
        ?TrackerHistoryLast $lastTrackerHistory
    ): int {
        $engineOnTime = 0;
        $prevEngineOnTime = $vehicleEngineOnTime;

        $previousTH = $prevTrackerHistory ?: $lastTrackerHistory;

        if (
            $previousTH &&
            $previousTH->getIgnition() == 1
        ) {
            $thTs = $this->getValidTsForEngineOnTime($trackerHistory);
            $previousThTs = $this->getValidTsForEngineOnTime($previousTH);

            /**
             * Just to make sure when we have delays while processing the data, especially when record has 1980 date.
             * Instead of deducting we will add absolute value,
             */
            $engineOnTime += abs($thTs - $previousThTs);
        }

        return $prevEngineOnTime + $engineOnTime;
    }

    /**
     * Sometimes, the tracker sends a payload like:
     * 252514005901d6086908406913337300780e1014012c0000a0c00005000000000000000000000000000000000069000001b27401800106000012ffffffffffffffffffffffffffffffff03122319ffff00000025ffffffffff
     *
     * The date in this payload might be something like 1980-01-06 00:00:00.
     *
     * Since our engine-on time calculation depends on date differences, using a wrong date like 1980 would break the logic.
     *
     * This method make sure
     * - If the tracker reports a date from **1980**,
     * - Instead, we use `created_at` (the time when we process this row).
     * - This ensures we can correctly calculate engine hours.
     *
     * This issue usually happens when the device **resets** or **loses battery power**.
     */
    private function getValidTsForEngineOnTime(TrackerHistory|TrackerHistoryLast $trackerHistory): int
    {
        $ts = $trackerHistory->getTs();

        if ($ts->format('Y') == '1980') {
            $dateTime = $trackerHistory->getCreatedAt();
        } else {
            $dateTime = $ts;
        }

        return $dateTime->getTimestamp();
    }

    public function handleLastTHObject(
        ?TrackerHistoryLast $lastTrackerHistory,
        \DateTimeInterface $thDateTime
    ) {
        return $lastTrackerHistory
            ? (($lastTrackerHistory->getTs() < $thDateTime) ? $lastTrackerHistory : null)
            : null;
    }

    /**
     * @param array|null $lastTrackerHistoryData
     * @param \DateTimeInterface $thDateTime
     * @return array|null
     */
    public function handleLastTHWithDateTime(
        ?array $lastTrackerHistoryData,
        \DateTimeInterface $thDateTime
    ) {
        return $lastTrackerHistoryData
            ? (($lastTrackerHistoryData['ts'] < $thDateTime) ? $lastTrackerHistoryData : null)
            : null;
    }

    /**
     * @param string $vendorName
     * @param EntityManager $em
     * @return BaseVendor
     * @throws \Exception
     */
    public static function resolve(string $vendorName, EntityManager $em): self
    {
        self::$em = $em;

        return match ($vendorName) {
            DeviceVendor::VENDOR_TELTONIKA => new Teltonika(),
            DeviceVendor::VENDOR_ULBOTECH => new Ulbotech(),
            default => new self(),
        };
    }
}
