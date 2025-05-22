<?php

namespace App\Service\Device\DeviceQueue\EngineOnTime;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;

class EngineOnTimeQueueMessage
{
    private $device;
    private $lastTrackerHistory;
    private $trackerHistoryData;

    /**
     * EngineOnTimeQueueMessage constructor.
     * @param Device $device
     * @param array|null $trackerHistoryData
     * @param TrackerHistory|null $lastTrackerHistory
     * @throws \Exception
     */
    public function __construct(
        Device $device,
        ?array $trackerHistoryData,
        ?TrackerHistory $lastTrackerHistory
    ) {
        $this->device = $device;
        $this->trackerHistoryData = array_map(function ($item) {
            return array_key_exists('th', $item) ? $item['th']->toArray(self::getTHFields()) : null;
        }, $trackerHistoryData['data']);
        $this->lastTrackerHistory = $lastTrackerHistory ? $lastTrackerHistory->toArray(self::getTHFields()) : null;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'device_id' => $this->device->getId(),
            'tracker_history_data' => $this->trackerHistoryData,
            'last_tracker_history' => $this->lastTrackerHistory
        ]);
    }

    /**
     * @return array
     */
    public static function getTHFields(): array
    {
        return [
            'tsISO8601',
            'ignition',
            'engineOnTime',
        ];
    }
}
