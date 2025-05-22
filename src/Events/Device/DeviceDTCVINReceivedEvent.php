<?php

namespace App\Events\Device;

use App\Entity\Device;
use App\Entity\Tracker\TrackerPayload;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceDTCVINReceivedEvent extends Event
{
    public const NAME = 'app.event.device.DTCVINReceived';

    protected $trackerRecord;
    protected $device;
    protected $trackerPayload;
    protected $trackerHistoryDTCVINIds;

    /**
     * @param DeviceDataInterface $trackerRecord
     * @param Device $device
     * @param TrackerPayload $trackerPayload
     */
    public function __construct(
        DeviceDataInterface $trackerRecord,
        Device $device,
        TrackerPayload $trackerPayload
    ) {
        $this->trackerRecord = $trackerRecord;
        $this->device = $device;
        $this->trackerPayload = $trackerPayload;
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

    public function setTrackerHistoryDTCVINIdsForEvents($trackerHistoryDTCVINIds)
    {
        $this->trackerHistoryDTCVINIds = $trackerHistoryDTCVINIds;
    }

    public function getTrackerHistoryDTCVINIdsForEvents()
    {
        return $this->trackerHistoryDTCVINIds;
    }

    /**
     * @return bool
     */
    public function isVINData(): bool
    {
        return $this->getTrackerRecord()->isVINData();
    }

    /**
     * @return bool
     */
    public function isDTCData(): bool
    {
        return $this->getTrackerRecord()->isDTCData();
    }
}