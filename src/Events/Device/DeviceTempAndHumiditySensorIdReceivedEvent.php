<?php

namespace App\Events\Device;

use App\Entity\Device;
use App\Entity\Tracker\TrackerPayload;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceTempAndHumiditySensorIdReceivedEvent extends Event
{
    public const NAME = 'app.event.device.tempAndHumiditySensorIdReceived';

    protected $trackerRecord;
    protected $device;
    protected $trackerPayload;
    protected $trackerHistorySensorIds;
    protected $trackerHistorySensors;

    /**
     * @param DeviceDataInterface $trackerRecord
     * @param Device $device
     * @param TrackerPayload $trackerPayload
     * @param array $trackerHistorySensorIds
     * @param array $trackerHistorySensors
     */
    public function __construct(
        DeviceDataInterface $trackerRecord,
        Device $device,
        TrackerPayload $trackerPayload,
        $trackerHistorySensorIds = [],
        $trackerHistorySensors = []
    ) {
        $this->trackerRecord = $trackerRecord;
        $this->device = $device;
        $this->trackerPayload = $trackerPayload;
        $this->trackerHistorySensorIds = $trackerHistorySensorIds;
        $this->trackerHistorySensors = $trackerHistorySensors;
    }

    /**
     * @return DeviceDataInterface
     */
    public function getTrackerRecord(): DeviceDataInterface
    {
        return $this->trackerRecord;
    }

    /**
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return TrackerPayload
     */
    public function getTrackerPayload(): TrackerPayload
    {
        return $this->trackerPayload;
    }

    public function setTrackerHistorySensorIdForEvents($trackerHistorySensorIds)
    {
        $this->trackerHistorySensorIds = $trackerHistorySensorIds;
    }

    public function getTrackerHistorySensorIdForEvents()
    {
        return $this->trackerHistorySensorIds;
    }

    public function setTrackerHistorySensorsForStatusEvent($trackerHistorySensors)
    {
        $this->trackerHistorySensors[] = $trackerHistorySensors;
    }

    public function getTrackerHistorySensorsForEvents()
    {
        return $this->trackerHistorySensors;
    }
}