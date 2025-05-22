<?php

namespace App\Service\Device\DeviceIOQueue;

use App\Entity\Device;

class IOQueueMessage
{
    private $device;
    private $trackerHistoryIds;

    /**
     * @param Device $device
     * @param array|null $trackerHistoryIds
     */
    public function __construct(
        Device $device,
        ?array $trackerHistoryIds
    ) {
        $this->device = $device;
        $this->trackerHistoryIds = $trackerHistoryIds;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'device_id' => $this->device->getId(),
            'tracker_history_ids' => $this->trackerHistoryIds
        ]);
    }
}
