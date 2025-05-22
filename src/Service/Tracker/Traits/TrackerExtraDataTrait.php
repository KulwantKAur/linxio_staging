<?php

namespace App\Service\Tracker\Traits;

use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;

trait TrackerExtraDataTrait
{
    public ?int $deviceId = null;
    public array $extraData = [];

    public function initExtraDataByDeviceId(int $deviceId): void
    {
        $this->setExtraData([]);
        $this->setExtraDataDeviceId($deviceId);
    }

    public function setExtraDataDeviceId(int $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getExtraData(): array
    {
        return $this->extraData;
    }

    public function getExtraDataByKey(string $key): mixed
    {
        return $this->getExtraData() && isset($this->getExtraData()[$key])
            ? $this->getExtraData()[$key]
            : null;
    }

    public function setExtraData(array $extraData): void
    {
        $this->extraData = $extraData;
    }

    public function setExtraDataValueByKey(string $key, $value): void
    {
        $this->extraData[$key] = $value;
    }

    public function updateByExtraData(Device $device, TrackerHistory $trackerHistory): void
    {
    }
}
