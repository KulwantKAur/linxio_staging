<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceJammerReceivedEvent extends Event
{
    public const NAME = 'app.event.device.jammerReceived';

    /**
     * @param Device $device
     * @param array $trackerHistoryData
     */
    public function  __construct(
        protected Device $device,
        protected array $trackerHistoryData,
    ) {}

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getTrackerHistoryData(): array
    {
        return $this->trackerHistoryData;
    }
}