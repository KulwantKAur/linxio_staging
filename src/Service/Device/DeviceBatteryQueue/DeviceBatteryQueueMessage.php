<?php

namespace App\Service\Device\DeviceBatteryQueue;

use App\Entity\Device;

class DeviceBatteryQueueMessage
{
    private $device;
    private $trackerHistoryId;

    public function __construct(
        Device $device,
        int $trackerHistoryId
    ) {
        $this->device = $device;
        $this->trackerHistoryId = $trackerHistoryId;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'device_id' => $this->device->getId(),
                'tracker_history_id' => $this->trackerHistoryId
            ]
        );
    }
}
