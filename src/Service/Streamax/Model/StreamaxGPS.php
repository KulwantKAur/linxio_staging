<?php

namespace App\Service\Streamax\Model;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\DataHelper;

class StreamaxGPS extends StreamaxModel implements ImeiInterface, DateTimePartPayloadInterface, GpsDataInterface
{
    public ?string $id;
    public ?int $altitude;
    public ?int $angle;
    public ?string $fleetName;
    public ?float $hdop;
    public ?int $acc; // Ignition information, 0 - Flameout, 1 - Ignition
    public ?float $lat;
    public ?float $lng;
    public ?float $mileage; // km
    public ?int $numOfSatellites;
    public ?int $signalStrength;
    public ?float $speed;
    public ?string $time; // RFC3339
    public ?string $uniqueId; // Unique identifier of a device
    public ?string $vehicleId;
    public ?string $vehicleNumber;
    public ?StreamaxGPSExtend $extendData; // json string

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->id = $fields['id'] ?? null;
        $this->altitude = $fields['altitude'] ?? null;
        $this->angle = $fields['angle'] ?? null;
        $this->fleetName = $fields['fleetName'] ?? null;
        $this->hdop = $fields['hdop'] ?? null;
        $this->acc = $fields['acc'] ?? null;
        $this->lat = $fields['lat'] ?? null;
        $this->lng = $fields['lng'] ?? null;
        $this->mileage = $fields['mileage'] ?? null;
        $this->numOfSatellites = $fields['numOfSatellites'] ?? null;
        $this->signalStrength = $fields['signalStrength'] ?? null;
        $this->speed = $fields['speed'] ?? null;
        $this->time = $fields['time'] ?? null;
        $this->uniqueId = $fields['uniqueId'] ?? null;
        $this->vehicleId = $fields['vehicleId'] ?? null;
        $this->vehicleNumber = $fields['vehicleNumber'] ?? null;
        $this->extendData = isset($fields['extendData']) ? new StreamaxGPSExtend($fields['extendData']) : null;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
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
            'id' => $this->getId(),
            'uniqueId' => $this->getUniqueId(),
            'angle' => $this->getAngle(),
            'lat' => $this->getLat(),
            'lng' => $this->getLng(),
            'speed' => $this->getSpeed(),
            'time' => $this->getTime(),
        ];
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
     * @return int|null
     */
    public function getAngle(): ?int
    {
        return $this->angle;
    }

    /**
     * @param int|null $angle
     */
    public function setAngle(?int $angle): void
    {
        $this->angle = $angle;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lat
     */
    public function setLat(?float $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float|null $lng
     */
    public function setLng(?float $lng): void
    {
        $this->lng = $lng;
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
     * @return string|null
     */
    public function getTime(): ?string
    {
        return $this->time;
    }

    /**
     * @param string|null $time
     */
    public function setTime(?string $time): void
    {
        $this->time = $time;
    }

    public function getLongitude()
    {
        return $this->getLng();
    }

    public function getLatitude()
    {
        return $this->getLat();
    }

    /**
     * @return int|null
     */
    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    /**
     * @param int|null $altitude
     */
    public function setAltitude(?int $altitude): void
    {
        $this->altitude = $altitude;
    }

    /**
     * @return int|null
     */
    public function getNumOfSatellites(): ?int
    {
        return $this->numOfSatellites;
    }

    /**
     * @param int|null $numOfSatellites
     */
    public function setNumOfSatellites(?int $numOfSatellites): void
    {
        $this->numOfSatellites = $numOfSatellites;
    }

    /**
     * @return float|null
     */
    public function getMileage(): ?float
    {
        return $this->mileage;
    }

    /**
     * @return float|null
     */
    public function getMileageMeters(): ?float
    {
        return $this->getMileage() ? intval($this->getMileage() * 1000) : null;
    }

    /**
     * @param float|null $mileage
     */
    public function setMileage(?float $mileage): void
    {
        $this->mileage = $mileage;
    }

    /**
     * @return int
     */
    public function getMovement(): int
    {
        return ($this->getSpeed() > 0) ? 1 : 0;
    }

    /**
     * @return int
     */
    public function getIgnition(): int
    {
        return !is_null($this->getAcc())
            ? $this->getAcc()
            : (($this->getSpeed() > 0) ? 1 : 0);
    }

    /**
     * @return int|null
     */
    public function getAcc(): ?int
    {
        return $this->acc;
    }

    /**
     * @param int|null $acc
     */
    public function setAcc(?int $acc): void
    {
        $this->acc = $acc;
    }

    /**
     * @return StreamaxGPSExtend|null
     */
    public function getExtendData(): ?StreamaxGPSExtend
    {
        return $this->extendData;
    }

    /**
     * @param StreamaxGPSExtend|null $extendData
     */
    public function setExtendData(?StreamaxGPSExtend $extendData): void
    {
        $this->extendData = $extendData;
    }

    public function getTemperature(): ?int
    {
        return $this->getExtendData()?->getTemperature();
    }

    public function getVoltage(): ?int
    {
        return $this->getExtendData()?->getVoltage();
    }
}

