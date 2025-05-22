<?php

namespace App\Service\Device\EngineHistoryQueue;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;

class EngineHistoryQueueMessage
{
    /**
     * @param Vehicle $vehicle
     * @param TrackerHistory $trackerHistory
     */
    public function __construct(
        private readonly Vehicle $vehicle,
        private readonly TrackerHistory $trackerHistory
    ) {}

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'vehicle_id' => $this->vehicle->getId(),
                'device_id' => $this->vehicle->getDeviceId(),
                'driver_id' => $this->vehicle->getDriverId(),
                'tracker_history_id' => $this->trackerHistory->getId(),
                'ignition' => $this->trackerHistory->getIgnition(),
                'movement' => $this->trackerHistory->getMovement(),
                'timestamp' => $this->trackerHistory->getTs()->getTimestamp(),
                'speed' => $this->trackerHistory->getSpeed(),
                'is_fix_with_speed' => $this->trackerHistory->getDevice()?->isFixWithSpeed(),
            ]
        );
    }
}
