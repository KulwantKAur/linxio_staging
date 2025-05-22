<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DevicePanicButtonEvent extends Event
{
    const NAME = 'app.event.device.panic_button';
    const SOURCE_MOBILE = 'mobile';
    const SOURCE_DEVICE = 'device';

    protected $device;
    protected $trackerHistoryData;
    protected $source;

    public function __construct(Device $device, ?array $trackerHistoryData, string $source = self::SOURCE_DEVICE)
    {
        $this->device = $device;
        $this->trackerHistoryData = $trackerHistoryData;
        $this->source = $source;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getTrackerHistoryData(): ?array
    {
        return $this->trackerHistoryData;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
