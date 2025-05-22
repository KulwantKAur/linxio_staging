<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\BLE;

use App\Service\Tracker\Parser\Topflytech\Data;

/**
 * @see TemperatureAndHumiditySensorService.php
 */
class TemperatureAndHumiditySensor
{
    public const AMBIENT_LIGHT_STATUS_OFF = 0;
    public const AMBIENT_LIGHT_STATUS_ON = 1;

    /** @var string|null */
    public $BLESensorId;
    /** @var float|null */
    public $sensorBatteryVoltage; // mV
    /** @var int|null */
    public $sensorBatteryPercentage; // %
    /** @var float|null */
    public $temperature; // Celsius
    /** @var float|null */
    public $humidity; // %
    /** @var array|null */
    public $ambientLightStatus; // int
    /** @var int|null */
    public $RSSI; // dBm
    /** @var bool */
    public $isNullableData = false;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->BLESensorId = $data['BLESensorId'] ?? null;
        $this->sensorBatteryVoltage = $data['sensorBatteryVoltage'] ?? null;
        $this->sensorBatteryPercentage = $data['sensorBatteryPercentage'] ?? null;
        $this->temperature = $data['temperature'] ?? null;
        $this->humidity = $data['humidity'] ?? null;
        $this->ambientLightStatus = $data['ambientLightStatus'] ?? null;
        $this->RSSI = $data['RSSI'] ?? null;
        $this->isNullableData = $data['isNullableData'] ?? false;
    }

    /**
     * @return string|null
     */
    public function getBLESensorId(): ?string
    {
        return $this->BLESensorId;
    }

    /**
     * @param string|null $BLESensorId
     */
    public function setBLESensorId(?string $BLESensorId): void
    {
        $this->BLESensorId = $BLESensorId;
    }

    /**
     * @return float|null
     */
    public function getSensorBatteryVoltage(): ?float
    {
        return $this->sensorBatteryVoltage;
    }

    /**
     * @param float|null $sensorBatteryVoltage
     */
    public function setSensorBatteryVoltage(?float $sensorBatteryVoltage): void
    {
        $this->sensorBatteryVoltage = $sensorBatteryVoltage;
    }

    /**
     * @return int|null
     */
    public function getSensorBatteryPercentage(): ?int
    {
        return $this->sensorBatteryPercentage;
    }

    /**
     * @param int|null $sensorBatteryPercentage
     */
    public function setSensorBatteryPercentage(?int $sensorBatteryPercentage): void
    {
        $this->sensorBatteryPercentage = $sensorBatteryPercentage;
    }

    /**
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    /**
     * @param float|null $temperature
     */
    public function setTemperature(?float $temperature): void
    {
        $this->temperature = $temperature;
    }

    /**
     * @return float|null
     */
    public function getHumidity(): ?float
    {
        return $this->humidity;
    }

    /**
     * @param float|null $humidity
     */
    public function setHumidity(?float $humidity): void
    {
        $this->humidity = $humidity;
    }

    /**
     * @return int|null
     */
    public function getAmbientLightStatus(): ?int
    {
        return $this->ambientLightStatus;
    }

    /**
     * @param int|null $ambientLightStatus
     */
    public function setAmbientLightStatus(?int $ambientLightStatus): void
    {
        $this->ambientLightStatus = $ambientLightStatus;
    }

    /**
     * @return int|null
     */
    public function getRSSI(): ?int
    {
        return $this->RSSI;
    }

    /**
     * @param int|null $RSSI
     */
    public function setRSSI(?int $RSSI): void
    {
        $this->RSSI = $RSSI;
    }

    /**
     * @inheritDoc
     */
    public function getDataArray(): ?array
    {
        return [
            'BLESensorId' => $this->getBLESensorId(),
            'sensorBatteryVoltage' => $this->getSensorBatteryVoltage(),
            'sensorBatteryPercentage' => $this->getSensorBatteryPercentage(),
            'temperature' => $this->getTemperature(),
            'humidity' => $this->getHumidity(),
            'ambientLightStatus' => $this->getambientLightStatus(),
            'RSSI' => $this->getRSSI(),
            'isNullableData' => $this->isNullableData(),
        ];
    }

    /**
     * @return bool
     */
    public function isNullableData(): bool
    {
        return $this->isNullableData;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $sensorBatteryVoltage = Data::formatTempAndHumiditySensorBatteryVoltage(substr($textPayload, 12, 2));
        $sensorBatteryPercentage = Data::formatIntValueWithFF(substr($textPayload, 14, 2));
        $temperature = Data::formatTempAndHumiditySensorTemperature(substr($textPayload, 16, 4));

        return new self([
            'BLESensorId' => substr($textPayload, 0, 12),
            'sensorBatteryVoltage' => $sensorBatteryVoltage,
            'sensorBatteryPercentage' => $sensorBatteryPercentage,
            'temperature' => $temperature,
            'humidity' => Data::formatTempAndHumiditySensorHumidity(substr($textPayload, 20, 4)),
            'ambientLightStatus' => Data::formatTempAndHumiditySensorAmbientLightStatus(substr($textPayload, 24, 4)),
            'RSSI' => Data::formatTempAndHumiditySensorRSSIData(substr($textPayload, 28, 2)),
            'isNullableData' => boolval(is_null($sensorBatteryVoltage)
                && is_null($sensorBatteryPercentage)
                && is_null($temperature)),
        ]);
    }
}
