<?php

namespace App\Service\Streamax\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;

class StreamaxIButton extends StreamaxModel implements ImeiInterface
{
    public ?string $id;
    public ?string $ibuttonId;
    public ?string $vehicleId;
    public ?string $uniqueId;
    public ?string $signType; // SINGIN, SINGOUT
    public ?string $time;
    public ?string $driverId;
    public ?string $driverName;
    public ?float $lng;
    public ?float $lat;

    public function __construct(array $fields)
    {
        $this->id = $fields['id'] ?? null;
        $this->ibuttonId = $fields['ibuttonId'] ?? null;
        $this->vehicleId = $fields['vehicleId'] ?? null;
        $this->uniqueId = $fields['uniqueId'] ?? null;
        $this->signType = $fields['signType'] ?? null;
        $this->time = $fields['time'] ?? null;
        $this->driverId = $fields['driverId'] ?? null;
        $this->lng = $fields['lng'] ?? null;
        $this->lat = $fields['lat'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return $this->getUniqueId();
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function getIButtonId(): ?string
    {
        return $this->ibuttonId;
    }
}

