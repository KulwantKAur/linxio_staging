<?php

namespace App\Service\Device\DeviceSensorQueue;

use App\Entity\Device;

class DeviceSensorQueueMessage
{
    private $device;
    private $trackerHistorySensorId;

    public function __construct(
        Device $device,
        int $trackerHistorySensorId
    ) {
        $this->device = $device;
        $this->trackerHistorySensorId = $trackerHistorySensorId;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode(
            [
                'device_id' => $this->device->getId(),
                'tracker_history_sensor_id' => $this->trackerHistorySensorId
            ]
        );
    }
}
