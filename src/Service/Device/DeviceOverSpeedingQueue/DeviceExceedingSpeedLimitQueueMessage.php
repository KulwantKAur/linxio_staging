<?php

namespace App\Service\Device\DeviceOverSpeedingQueue;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Service\Device\Consumer\MessageHelper;

class DeviceExceedingSpeedLimitQueueMessage
{
    private $device;
    private $thId;

    public function __construct(
        Device $device,
        TrackerHistory $trackerHistory
    ) {
        $this->device = $device;
        $this->thId = $trackerHistory->getId();
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'device_id' => $this->device->getId(),
                'th_id' => $this->thId
            ]
        );
    }
}
