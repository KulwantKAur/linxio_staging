<?php

namespace App\Events\Device;

use App\Entity\Device;

class DeviceMovingWithoutDriverEvent
{
    public const NAME = 'app.event.device.movingWithoutDriver';

    protected Device $device;
    protected array $trackerHistoryData;

    /**
     * DeviceEngineOnTimeEvent constructor.
     * @param Device $device
     * @param array $trackerHistoryData
     */
    public function __construct(
        Device $device,
        array $trackerHistoryData
    ) {
        $this->device = $device;
        $this->trackerHistoryData = $trackerHistoryData;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return array
     */
    public function getTrackerHistoryData(): array
    {
        return $this->trackerHistoryData;
    }
}
