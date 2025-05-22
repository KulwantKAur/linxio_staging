<?php

namespace App\Events\Device;

use App\Entity\Device;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceMovingEvent extends Event
{
    public const NAME = 'app.event.device.moving.alert';
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
    public function getTrackerHistoryIDs(): ?array
    {
        return $this->trackerHistoryIDs;
    }

    /**
     * @return array|null
     */
    public function getTrackerHistoryData(): ?array
    {
        return $this->trackerHistoryData;
    }
}
