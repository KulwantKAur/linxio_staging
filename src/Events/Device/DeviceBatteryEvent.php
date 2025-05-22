<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceBatteryEvent extends Event
{
    const NAME = 'app.event.device.battery.alert';
    protected $device;
    protected $trackerHistoryIDs;

    public function __construct(Device $device, ?array $trackerHistoryIDs)
    {
        $this->device = $device;
        $this->trackerHistoryIDs = $trackerHistoryIDs;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getTrackerHistoryIDs(): ?array
    {
        return $this->trackerHistoryIDs;
    }
}