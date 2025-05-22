<?php

namespace App\Service\Device\DeviceQueue\PanicButton;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;

class PanicButtonQueueMessage
{
    private $device;
    private $trackerHistory;

    public function __construct(
        Device $device,
        TrackerHistory $trackerHistory
    ) {
        $this->device = $device;
        $this->trackerHistory = $trackerHistory;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'device_id' => $this->device->getId(),
            'tracker_history_id' => $this->trackerHistory->getId()
        ]);
    }
}
