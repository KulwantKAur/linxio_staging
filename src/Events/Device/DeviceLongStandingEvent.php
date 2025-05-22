<?php

namespace App\Events\Device;

use App\Entity\Device;

class DeviceLongStandingEvent
{
    public const NAME = 'app.event.device.longStanding.alert';
    protected Device $device;
    protected ?array $trackerHistoryIDs;
    protected ?array $trackerHistoryData;
    /**
     * @param Device $device
     * @param array|null $trackerHistoryIDs
     * @param array|null $trackerHistoryData
     */
    public function __construct(Device $device, ?array $trackerHistoryIDs, ?array $trackerHistoryData)
    {
        $this->device = $device;
        $this->trackerHistoryIDs = $trackerHistoryIDs;
        $this->trackerHistoryData = $trackerHistoryData;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getTrackerHistoryIDs(): ?array
    {
        return $this->trackerHistoryIDs;
    }

    public function getTrackerHistoryData(): ?array
    {
        return $this->trackerHistoryData;
    }
}
