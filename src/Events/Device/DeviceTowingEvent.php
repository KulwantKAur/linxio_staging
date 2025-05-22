<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceTowingEvent extends Event
{
    const NAME = 'app.event.device.towing';
    protected $device;
    protected $trackerHistoryIDs;
    protected ?array $trackerHistoryData;

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