<?php

namespace App\Service\Traccar\Model;

use App\Entity\Device;
use App\Service\Traccar\Model\PositionAttributes\TraccarPositionAttributes;

/**
 * @internal https://github.com/traccar/traccar/blob/master/src/main/java/org/traccar/model/Position.java
 * @example {"position":{"id":0,"attributes":{"priority":0,"sat":13,"event":0,"ignition":true,"motion":true,"io200":0,"io113":0,"io33":253,"io37":6,"io48":47,"power":14.238,"io24":0,"battery":0,"io68":0,"io13":1715,"io15":0,"io42":43,"io43":0,"io49":7996,"operator":25701,"tripOdometer":20,"odometer":6902740,"io12":1130206,"io238":0,"distance":15.09,"totalDistance":13022475.67,"hours":737000},"deviceId":4,"type":null,"protocol":"teltonika","serverTime":"2021-08-11T09:04:07.468+00:00","deviceTime":"2019-12-20T06:28:42.000+00:00","fixTime":"2019-12-20T06:28:42.000+00:00","outdated":false,"valid":true,"latitude":53.8862733,"longitude":27.449195,"altitude":241,"speed":0,"course":316,"address":null,"accuracy":0,"network":null},"device":{"id":4,"attributes":[],"groupId":0,"name":"Teltonika","uniqueId":"888888888888888","status":"online","lastUpdate":"2021-08-11T09:04:07.470+00:00","positionId":5265,"geofenceIds":[],"phone":"","model":"","contact":"","category":null,"disabled":false}}
 */
class TraccarPosition extends TraccarModel
{
    /** @var int|null $id */
    private $id;
    /** @var int $deviceId */
    private $deviceId;
    /** @var string $protocol */
    private $protocol;
    /** @var \DateTimeInterface $deviceTime */
    private $deviceTime;
    /** @var \DateTimeInterface $fixTime */
    private $fixTime;
    /** @var \DateTimeInterface $serverTime */
    private $serverTime;
    /** @var bool $outdated */
    private $outdated;
    private ?bool $valid;
    /** @var float|null $latitude */
    private $latitude;
    /** @var float|null $longitude */
    private $longitude;
    /** @var float|null $altitude */
    private $altitude;
    /** @var float|null $speed */
    private $speed;
    /** @var float|null $course */
    private $course;
    /** @var string|null $address */
    private $address;
    /** @var int|null $accuracy */
    private $accuracy;
    /** @var \stdClass|null $rawAttributes */
    private $rawAttributes;
    /** @var TraccarPositionAttributes|null $attributes */
    private $attributes;

    /**
     * @param \stdClass|array $fields
     * @param Device|null $device
     * @throws \Exception
     */
    public function __construct($fields, ?Device $device = null)
    {
        $fields = self::convertArrayToObject($fields);
        $this->id = $this->handlePossibleZeroValueInRawField($fields->id);
        $this->deviceId = $fields->deviceId ?? null;
        $this->protocol = $fields->protocol ?? null;
        $this->deviceTime = $fields->deviceTime ? $this->convertDeviceDateToDatetime($fields->deviceTime) : null;
        $this->fixTime = $fields->fixTime ? $this->convertDeviceDateToDatetime($fields->fixTime) : null;
        $this->serverTime = $fields->serverTime ? $this->convertDeviceDateToDatetime($fields->serverTime) : null;
        $this->outdated = $fields->outdated ?? null;
        $this->valid = $fields->valid ?? null;
        $this->latitude = $fields->latitude ?? null;
        $this->longitude = $fields->longitude ?? null;
        $this->altitude = $fields->altitude ?? null;
        $this->speed = (isset($fields->speed) && $fields->speed >= 0) ? $this->convertKnotsToKmH($fields->speed) : null;
        $this->course = $fields->course ?? null;
        $this->address = $fields->address ?? null;
        $this->accuracy = $fields->accuracy ?? null;
        $this->rawAttributes = $fields->attributes ?? null;
        $this->attributes = $this->handlePositionAttributes($this, $device);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDeviceId(): int
    {
        return $this->deviceId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeviceTime(): \DateTimeInterface
    {
        return $this->deviceTime;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @return float|null
     */
    public function getAltitude(): ?float
    {
        return $this->altitude;
    }

    /**
     * @return float|null
     */
    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return TraccarPositionAttributes|null
     */
    public function getAttributes(): ?TraccarPositionAttributes
    {
        return $this->attributes;
    }

    /**
     * @param TraccarPositionAttributes|null $attributes
     * @return self
     */
    public function setAttributes(?TraccarPositionAttributes $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return \stdClass|null
     */
    public function getRawAttributes(): ?\stdClass
    {
        return $this->rawAttributes;
    }

    /**
     * @return float|null
     */
    public function getCourse(): ?float
    {
        return $this->course;
    }

    /**
     * @param float|null $course
     * @return TraccarPosition
     */
    public function setCourse(?float $course): TraccarPosition
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getFixTime(): \DateTimeInterface
    {
        return $this->fixTime;
    }

    /**
     * @return bool|null
     */
    public function isValid(): ?bool
    {
        return $this->valid;
    }
}

