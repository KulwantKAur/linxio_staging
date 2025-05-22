<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\BaseDataCode;

/**
 * @example
 */
abstract class BaseBLE implements DateTimePartPayloadInterface
{
    public const TIRE_PRESSURE_SENSOR_DATA_CODE = '0001';
    public const SOS_DATA_CODE = '0002';
    public const DRIVER_ID_DATA_CODE = '0003';
    public const TEMPERATURE_AND_HUMIDITY_SENSOR_DATA_CODE = '0004';
    public const DOOR_SENSOR_DATA_CODE = '0005';
    public const RELAY_DATA_CODE = '0006';
    public const RSSI_ACCURACY = 5; // dbm
    public const RSSI_ACCURACY_TIME = 600; // 10 min
    public const RSSI_ACCURACY_TIME_FOR_STOPPED = 3600; // 60 min
    public const PACKET_MINIMAL_LENGTH = 50;

    public $dateTime;
    public $ignition;
    public $BLEDataCode;
    public $BLEData;
    public $gpsData;
    public $locationData;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->dateTime = $data['dateTime'] ?? null;
        $this->ignition = DataHelper::formatValueIgnoreZero($data, 'ignition');
        $this->BLEDataCode = $data['BLEDataCode'] ?? null;
        $this->BLEData = $data['BLEData'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
    }

    /**
     * @param string $BLEDataCode
     * @param string $textPayload
     * @param string $protocol
     * @return BaseDataCode|array
     * @throws \Exception
     */
    abstract static function handleDataByDataCodeAndPayload(string $BLEDataCode, string $textPayload, string $protocol);

    /**
     * @param string $textPayload
     * @param string $protocol
     * @return static
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload, string $protocol)
    {
        $BLEDataCode = substr($textPayload, 14, 4);
        $BLEData = static::handleDataByDataCodeAndPayload($BLEDataCode, substr($textPayload, 18), $protocol);

        return new static([
            'dateTime' => Data::formatDateTime(substr($textPayload, 0, 12)),
            'ignition' => hexdec(substr($textPayload, 12, 2)),
            'BLEDataCode' => $BLEDataCode,
            'BLEData' => $BLEData,
            'gpsData' => $BLEData->getGpsData(),
            'locationData' => $BLEData->getLocationData(),
        ]);
    }

    /**
     * @param \DateTime|null $dateTime
     */
    public function setDateTime(?\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    /**
     * @return BaseDataCode|null
     */
    public function getBLEData(): ?BaseDataCode
    {
        return $this->BLEData;
    }

    /**
     * @return mixed|null
     */
    public function getGpsData(): ?GpsData
    {
        return $this->getBLEData()->getGpsData() ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getLocationData(): ?Location
    {
        return $this->getBLEData()->getLocationData() ?? null;
    }

    /**
     * @return int|null
     */
    public function getMovement(): ?int
    {
        return $this->getBLEData()->getMovement() ?? null;
    }

    /**
     * @return int|null
     */
    public function getIgnition(): ?int
    {
        return $this->getBLEData()->getIgnition() ?? null;
    }

    /**
     * @return float|null
     */
    public function getInternalBatteryVoltage(): ?float
    {
        return $this->getBLEData()->getInternalBatteryVoltage() ?? null;
    }

    /**
     * @return string|null
     */
    public function getDriverIdTag(): ?string
    {
        return $this->getBLEData()->getDriverIdTag() ?? null;
    }

    /**
     * @return array|null
     */
    public function getDriverSensorData(): ?array
    {
        return $this->getBLEData() ? $this->getBLEData()->getDriverSensorData() : null;
    }

    /**
     * @return array|null
     */
    public function getBLETempAndHumidityData(): ?array
    {
        return $this->getBLEData() ? $this->getBLEData()->getTempAndHumidityData() : null;
    }

    /**
     * @return array|null
     */
    public function getBLESOSData(): ?array
    {
        return $this->getBLEData() ? $this->getBLEData()->getSOSData() : null;
    }

    /**
     * @return bool
     */
    public function isBLESOSAlarm(): bool
    {
        return $this->getBLEData() ? $this->getBLEData()->isSOSAlarm() : false;
    }

    /**
     * @return bool
     */
    public function isNullableData(): bool
    {
        return $this->getBLEData() ? $this->getBLEData()->isNullableData() : false;
    }

    /**
     * @return array|null
     */
    public function getBLEDataArray(): ?array
    {
        return $this->getBLEData() ? $this->getBLEData()->getDataArray() : null;
    }

    /**
     * @param string $BLEDataCode
     * @return bool
     */
    public static function isRequestTypeWithData(string $BLEDataCode): bool
    {
        switch ($BLEDataCode) {
            case self::SOS_DATA_CODE:
            case self::DRIVER_ID_DATA_CODE:
                return true;
            default:
                return false;
        }
    }

    /**
     * @param string $BLEDataCode
     * @return bool
     */
    public static function isRequestTypeWithExtraData(string $BLEDataCode): bool
    {
        switch ($BLEDataCode) {
            case self::TEMPERATURE_AND_HUMIDITY_SENSOR_DATA_CODE:
                return true;
            default:
                return false;
        }
    }

    /**
     * @return string|null
     */
    public function getBLEDataCode(): ?string
    {
        return $this->BLEDataCode;
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        return substr($payload, Data::DATA_START_PACKET_POSITION + 0, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 0, 12);
    }

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->getBLEData() ? $this->getBLEData()->getDataAndGNSS() : null;
    }
}
