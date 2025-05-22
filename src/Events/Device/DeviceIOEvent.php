<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceIOEvent extends Event
{
    public const NAME = 'app.event.device.io';

    protected $device;
    protected $trackerHistoryIds;

    /**
     * DeviceEngineOnTimeEvent constructor.
     * @param Device $device
     * @param array|null $trackerHistoryIds
     */
    public function __construct(Device $device, ?array $trackerHistoryIds)
    {
        $this->device = $device;
        $this->trackerHistoryIds = $trackerHistoryIds;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return array|null
     */
    public function getTrackerHistoryIds(): ?array
    {
        return $this->trackerHistoryIds;
    }
}
