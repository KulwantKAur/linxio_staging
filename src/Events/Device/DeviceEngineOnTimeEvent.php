<?php

namespace App\Events\Device;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceEngineOnTimeEvent extends Event
{
    public const NAME = 'app.event.device.engine_on_time';

    protected $device;
    protected $trackerHistoryIds;
    protected $trackerHistoryData;
    protected $lastTrackerHistory;

    /**
     * DeviceEngineOnTimeEvent constructor.
     * @param Device $device
     * @param array $trackerHistoryData
     * @param TrackerHistory|null $lastTrackerHistory
     */
    public function __construct(
        Device $device,
        array $trackerHistoryData,
        ?TrackerHistory $lastTrackerHistory
    ) {
        $this->device = $device;
        $this->trackerHistoryIds = $trackerHistoryData['ids'];
        $this->trackerHistoryData = $trackerHistoryData;
        $this->lastTrackerHistory = $lastTrackerHistory;
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

    /**
     * @return TrackerHistory|null
     */
    public function getLastTrackerHistory(): ?TrackerHistory
    {
        return $this->lastTrackerHistory;
    }

    /**
     * @return array
     */
    public function getTrackerHistoryData(): array
    {
        return $this->trackerHistoryData;
    }
}
