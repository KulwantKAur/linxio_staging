<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class EngineHistoryEvent extends Event
{
    const NAME = 'app.event.engineHistory';

    public function __construct(
        protected readonly Device $device,
        protected readonly ?array $trackerHistoryData
    ) {}

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function getTrackerHistoryData(): ?array
    {
        return $this->trackerHistoryData;
    }
}