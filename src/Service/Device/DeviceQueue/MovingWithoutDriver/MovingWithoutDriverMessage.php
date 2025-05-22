<?php


namespace App\Service\Device\DeviceQueue\MovingWithoutDriver;


use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Service\Device\Consumer\MessageHelper;

class MovingWithoutDriverMessage
{
    private Device $device;
    private array $trackerHistoryData;

    /**
     * @param Device $device
     * @param TrackerHistory $trackerHistory
     * @throws \Exception
     */
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
        return json_encode([
            'device_id' => $this->device->getId(),
            'trackerHistoryData' => $this->trackerHistoryData
        ]);
    }
}
