<?php

namespace App\Service\Streamax\Model;

class StreamaxGPSExtend extends StreamaxModel
{
    public ?int $temperature;
    public ?int $mileage;
    public ?int $voltage;

    public function __construct($fields)
    {
        $fields = self::convertStringToArray($fields);
        $this->temperature = $fields['vehicleInfo']['temperatrue'] ?? null;
        $this->mileage = $fields['vehicleInfo']['mileage'] ?? null;
        $this->voltage = $fields['vehicleInfo']['voltage'] ?? null;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): void
    {
        $this->mileage = $mileage;
    }

    public function getMileageMeters(): ?float
    {
        return $this->getMileage() ? $this->getMileage() * 1000 : null;
    }

    public function getTemperature(): ?int
    {
        return $this->temperature;
    }

    public function getVoltage(): ?int
    {
        return $this->voltage;
    }
}

