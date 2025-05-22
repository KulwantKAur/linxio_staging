<?php

namespace App\Service\Device\TodayDataQueue;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;

class TodayDataQueueMessage
{
    private $vehicle;
    private TrackerHistory $trackerHistory;

    public function __construct(
        ?Vehicle $vehicle,
        TrackerHistory $trackerHistory
    ) {
        $this->vehicle = $vehicle;
        $this->trackerHistory = $trackerHistory;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'vehicle_id' => $this->vehicle?->getId(),
                'tracker_history_id' => $this->trackerHistory->getId(),
                'speed' => $this->trackerHistory->getSpeed(),
                'timezone' => $this->trackerHistory->getTimeZoneName(),
                'ignition' => $this->trackerHistory->getIgnition(),
                'movement' => $this->trackerHistory->getMovement(),
                'timestamp' => $this->trackerHistory->getTs()->getTimestamp(),
                'odometer' => $this->trackerHistory->getOdometer(),
            ]
        );
    }
}
