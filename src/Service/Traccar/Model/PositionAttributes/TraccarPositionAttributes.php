<?php

namespace App\Service\Traccar\Model\PositionAttributes;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Service\Traccar\Model\TraccarData;
use App\Service\Traccar\Model\TraccarModel;

class TraccarPositionAttributes
{
    /** @var int|null in sec */
    public $hours;
    /** @var int|null */
    public $odometer;
    /** @var float|null */
    public $totalDistance;
    /** @var bool|null */
    public $ignition;
    /** @var bool|null */
    public $motion;
    /** @var int|null */
    public $sat;
    /** @var float|null */
    public $distance;
    /** @var int|null in % */
    public $batteryLevel;
    /** @var float|null */
    public $power;
    /** @var float|null */
    public $battery;
    public ?float $deviceTemp;
    public ?string $iccid;

    /**
     * @param \stdClass|array $fields
     */
    public function __construct($fields)
    {
        $fields = TraccarModel::convertArrayToObject($fields);
        $this->odometer = $fields->odometer ?? null;
        $this->hours = $fields->hours ?? null;
        $this->sat = $fields->sat ?? null;
        $this->distance = $fields->distance ?? null;
        $this->ignition = $fields->ignition ?? null;
        $this->motion = $fields->motion ?? null;
        $this->totalDistance = $fields->totalDistance ?? null;
        $this->batteryLevel = $fields->batteryLevel ?? null;
        $this->battery = $fields->battery ?? null;
        $this->power = $fields->power ?? null;
        $this->deviceTemp = $fields->deviceTemp ?? null;
        $this->iccid = $fields->iccid ?? null;
    }

    /**
     * @return bool|null
     */
    public function getMotion(): ?bool
    {
        return $this->motion;
    }

    /**
     * @return bool|null
     */
    public function getIgnition(): ?bool
    {
        return $this->ignition;
    }

    /**
     * @return int|null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @return int|null
     */
    public function getHours(): ?int
    {
        return $this->hours;
    }

    /**
     * @return float|null
     */
    public function getPower(): ?float
    {
        return $this->power;
    }

    /**
     * @return float|null
     */
    public function getBattery(): ?float
    {
        return $this->battery;
    }

    /**
     * @return int|null
     */
    public function getBatteryLevel(): ?int
    {
        return $this->batteryLevel;
    }

    /**
     * @return int|null
     */
    public function getSat(): ?int
    {
        return $this->sat;
    }

    /**
     * @return float|null
     */
    public function getDistance(): ?float
    {
        return $this->distance;
    }

    /**
     * @return float|null
     */
    public function getTotalDistance(): ?float
    {
        return $this->totalDistance;
    }

    /**
     * @return float|null
     */
    public function getTotalDistanceRound(): ?float
    {
        return $this->getTotalDistance() ? round($this->getTotalDistance()) : null;
    }

    /**
     * @return float|null
     */
    public function getBatteryMilli(): ?float
    {
        return $this->getBattery() ? ($this->getBattery() * 1000) : null;
    }

    /**
     * @return float|null
     */
    public function getPowerMilli(): ?float
    {
        return $this->getPower() ? ($this->getPower() * 1000) : null;
    }

    /**
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->deviceTemp ? round($this->deviceTemp, 2) : null;
    }

    /**
     * @return float|null
     */
    public function getTemperatureMilli(): ?float
    {
        return $this->getTemperature() ? ($this->getTemperature() * 1000) : null;
    }

    /**
     * @return string|null
     */
    public function getIccid(): ?string
    {
        return $this->iccid;
    }

    /**
     * @param string $protocol
     * @param $data
     * @param Device|null $device
     * @return TraccarPositionAttributes
     */
    public static function getInstance(string $protocol, $data, ?Device $device): TraccarPositionAttributes
    {
        switch ($protocol) {
            case TraccarData::PROTOCOL_TELTONIKA:
                return new TraccarPositionAttributesTeltonika($data);
            case TraccarData::PROTOCOL_ULBOTECH:
                return new TraccarPositionAttributesUlbotech($data);
            case TraccarData::PROTOCOL_CONCOX:
                if ($device && TraccarData::isModelNameConcoxCRX3($device->getModelName())) {
                    return new TraccarPositionAttributesConcoxCRX3($data);
                }

                return new TraccarPositionAttributesConcox($data);
            case TraccarData::PROTOCOL_MEITRACK:
                return new TraccarPositionAttributesMeitrack($data);
            case TraccarData::PROTOCOL_QUECLINK:
                return new TraccarPositionAttributesQueclink($data);
            case TraccarData::PROTOCOL_DIGITAL_MATTER:
            case TraccarData::PROTOCOL_DIGITAL_MATTER_HTTP:
                return new TraccarPositionAttributesDigitalMatter($data);
            case TraccarData::PROTOCOL_EELINK:
                return new TraccarPositionAttributesEELink($data);
            default:
                return new self([]);
        }
    }
}

