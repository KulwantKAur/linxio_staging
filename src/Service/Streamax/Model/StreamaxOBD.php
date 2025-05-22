<?php

namespace App\Service\Streamax\Model;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;

class StreamaxOBD extends StreamaxModel implements ImeiInterface, DateTimePartPayloadInterface
{
    public ?float $speed;
    public string $time; // RFC3339
    public ?string $uniqueId; // Unique identifier of a device
    public ?string $vehicleId;
    public ?string $type; // custom
    public ?float $batteryVoltage; // V
    public ?string $brake; // NOT_BRAKE,BRAKE
    public ?float $totalMileage; // km
    public ?string $turningState; // STRAIGHT,LEFT,RIGHT
    public ?string $seatBelt; // FASTENED,NOT_FASTENED
    public ?int $engineHours; // sec
    public ?float $coolantTemp; // celsius

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->speed = $fields['speed'] ?? null;
        $this->time = $fields['time'];
        $this->uniqueId = $fields['uniqueId'] ?? null;
        $this->vehicleId = $fields['vehicleId'] ?? null;
        $this->batteryVoltage = $fields['batteryVoltage'] ?? null;
        $this->brake = $fields['brake'] ?? null;
        $this->totalMileage = $fields['totalMileage'] ?? null;
        $this->turningState = $fields['turningState'] ?? null;
        $this->seatBelt = $fields['seatBelt'] ?? null;
        $this->engineHours = $fields['engineHours'] ?? null;
        $this->coolantTemp = $fields['coolantTemp'] ?? null;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return $this->getUniqueId();
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'uniqueId' => $this->getUniqueId(),
            'speed' => $this->getSpeed(),
            'time' => $this->getTime(),
            'batteryVoltage' => $this->getBatteryVoltage(),
        ];
    }

    /**
     * @return float|null
     */
    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    /**
     * @param float|null $speed
     */
    public function setSpeed(?float $speed): void
    {
        $this->speed = $speed;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        // TODO: Implement getPayloadWithNewDateTime() method.
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        // TODO: Implement getDateTimePayload() method.
    }

    /**
     * @return float|null
     */
    public function getBatteryVoltage(): ?float
    {
        return $this->batteryVoltage;
    }

    /**
     * @param float|null $batteryVoltage
     */
    public function setBatteryVoltage(?float $batteryVoltage): void
    {
        $this->batteryVoltage = $batteryVoltage;
    }

    /**
     * @return string|null
     */
    public function getBrake(): ?string
    {
        return $this->brake;
    }
}

