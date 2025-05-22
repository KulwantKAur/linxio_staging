<?php

namespace App\Events\Area;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class CheckAreaEvent extends Event
{
    const NAME = 'app.event.check.area';
    protected $device;
    protected $trackerHistoryData;

    public function __construct(Device $device, array $trackerHistoryData)
    {
        $this->device = $device;
        $this->trackerHistoryData = $trackerHistoryData;
    }

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