<?php

namespace App\Events\User\Driver;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DriverFOBIdReceivedEvent extends Event
{
    const NAME = 'app.event.driver.FOBIdReceived';

    protected $device;
    protected $trackerHistoryData;

    public function __construct(Device $device, ?array $trackerHistoryData)
    {
        $this->device = $device;
        $this->trackerHistoryData = $trackerHistoryData;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getTrackerHistoryData(): ?array
    {
        return $this->trackerHistoryData;
    }
}
