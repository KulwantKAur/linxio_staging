<?php

namespace App\Service\Device\DeviceOverSpeedingQueue;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Service\Device\Consumer\MessageHelper;

class DeviceOverSpeedingQueueMessage
{
    private $device;
    private array $trackerHistoryData;

    public function __construct(
        Device $device,
        TrackerHistory $trackerHistory
    ) {
        $this->device = $device;
        $this->trackerHistoryData = $trackerHistory->toArray(MessageHelper::getTHFields());
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'device_id' => $this->device->getId(),
                'trackerHistoryData' => $this->trackerHistoryData
            ]
        );
    }
}
