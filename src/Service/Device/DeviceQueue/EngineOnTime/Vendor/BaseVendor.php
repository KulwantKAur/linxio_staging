<?php

namespace App\Service\Device\DeviceQueue\EngineOnTime\Vendor;

use App\Entity\DeviceVendor;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManager;

class BaseVendor
{
    public static $em;

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
            $previousTH = ($key == 0)
                ? ($this->handleLastTHWithDateTime($lastTrackerHistoryData, $th['ts']))
                : $trackerDataSet[$key - 1];

            if ($previousTH && $previousTH['ignition'] == 1) {
                $engineOnTime += $th['ts']->getTimestamp() - $previousTH['ts']->getTimestamp();
            }

            self::$em->getRepository(TrackerHistory::class)
                ->updateEngineOnTime($th['id'], $engineOnTime + $prevEngineOnTime);
        }

        $vehicle->increaseEngineOnTime($engineOnTime);

        return $vehicle;
    }

    /**
     * @param array|null $lastTrackerHistoryData
     * @param \DateTimeInterface $thDateTime
     * @return array|null
     */
    public function handleLastTHWithDateTime(?array $lastTrackerHistoryData, \DateTimeInterface $thDateTime)
    {
        $lastTHDateTime = $lastTrackerHistoryData ? $lastTrackerHistoryData['ts'] : null;

        return $lastTHDateTime
            ? (($lastTHDateTime < $thDateTime) ? $lastTrackerHistoryData : null)
            : null;
    }

    /**
     * @param string $vendorName
     * @return static
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
