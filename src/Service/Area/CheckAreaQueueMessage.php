<?php

namespace App\Service\Area;

use App\Entity\Device;

class CheckAreaQueueMessage
{
    private $device;
    private $trackerHistoryData;

    /**
     * CheckAreaQueueMessage constructor.
     * @param Device $device
     * @param array $trackerHistoryData
     */
    public function __construct(
        Device $device,
        array $trackerHistoryData
    ) {
        $this->device = $device;
        $this->trackerHistoryData = array_map(function ($item) {
            return array_key_exists('th', $item) ? $item['th']->toArray(self::getTHFields()) : null;
        }, $trackerHistoryData['data']);
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'device_id' => $this->device->getId(),
            'tracker_history_data' => $this->trackerHistoryData,
        ]);
    }

    /**
     * @return array
     */
    public static function getTHFields(): array
    {
        return [
            'tsISO8601',
            'lat',
            'lng',
            'speed'
        ];
    }
}
