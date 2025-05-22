<?php

namespace App\Service\Device\DeviceQueue\DeviceLongStanding;

use App\Entity\Device;
use App\Service\Device\Consumer\MessageHelper;

class DeviceLongStandingQueueMessage
{
    private Device $device;
    private ?int $trackerHistoryId;
    private ?array $trackerHistoryData;

    public function __construct(
        Device $device,
        ?int $trackerHistoryId,
        ?array $trackerHistoryData
    ) {
        $this->device = $device;
        $this->trackerHistoryId = $trackerHistoryId;
        $this->trackerHistoryData = array_map(function ($item) {
            return array_key_exists('th', $item) ? $item['th']->toArray(MessageHelper::getTHFields()) : null;
        }, $trackerHistoryData['data']);
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'device_id' => $this->device->getId(),
                'tracker_history_id' => $this->trackerHistoryId,
                'tracker_history_data' => $this->trackerHistoryData,
            ]
        );
    }
}