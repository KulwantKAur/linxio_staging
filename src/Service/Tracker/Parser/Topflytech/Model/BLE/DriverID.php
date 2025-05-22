<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\BLE;

use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\Location;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;

/**
 * @example gps 27271000270001088888888888888820111200000001000301B3EC0011C03100C858866B4276D6E342912AB44111150505
 * @example location 27271000270001088888888888888820111200000001000301B3EC0011C031000058866B4276D6E342912AB44111150505
 */
class DriverID extends BaseDataCode
{
    use MovementTrait;
    use IgnitionTrait;

    public const DRIVER_ID_DATA_TYPE = 0;
    public const LOW_BATTERY_ALARM_TYPE = 1;

    public $BLETagId;
    public $internalBatteryVoltage;
    public $driverIdType;
    public $gpsData;
    public $locationData;
    public $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->BLETagId = $data['BLETagId'] ?? null;
        $this->internalBatteryVoltage = $data['internalBatteryVoltage'] ?? null;
        $this->driverIdType = $data['driverIdType'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
        $this->dataAndGNSS = $data['dataAndGNSS'] ?? null;
        $this->formatMovement();
        $this->formatIgnition();
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 16, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 18, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 18, 32));
        }

        return new self([
            'BLETagId' => substr($textPayload, 0, 12),
            'internalBatteryVoltage' => Data::formatInternalBatteryVoltage(substr($textPayload, 12, 2)),
            'driverIdType' => hexdec(substr($textPayload, 14, 2)),
            'dataAndGNSS' => $dataAndGNSS,
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
        ]);
    }

    /**
     * @return mixed|null
     */
    public function getGpsData(): ?GpsData
    {
        return $this->gpsData;
    }

    /**
     * @return mixed|null
     */
    public function getLocationData(): ?Location
    {
        return $this->locationData;
    }

    /**
     * @return float|int|null
     */
    public function getInternalBatteryVoltage()
    {
        return $this->internalBatteryVoltage;
    }

    /**
     * @return mixed|null
     */
    public function getDriverIdType(): ?int
    {
        return $this->driverIdType;
    }

    /**
     * @return bool
     */
    public function isDriverIdData(): bool
    {
        return $this->getDriverIdType() == self::DRIVER_ID_DATA_TYPE;
    }

    /**
     * @return string|null
     */
    public function getDriverIdTag(): ?string
    {
        return $this->BLETagId;
    }

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->dataAndGNSS;
    }

    /**
     * @param DataAndGNSS|null $dataAndGNSS
     */
    public function setDataAndGNSS(?DataAndGNSS $dataAndGNSS): void
    {
        $this->dataAndGNSS = $dataAndGNSS;
    }

    /**
     * @return null
     */
    public function getDriverSensorData()
    {
        return $this->getDataArray();
    }

    /**
     * @return array
     */
    public function getDataArray(): array
    {
        return [
            'BLETagId' => $this->getDriverIdTag(),
            'internalBatteryVoltage' => $this->getInternalBatteryVoltage(),
            'driverIdType' => $this->getDriverIdType(),
            'gpsData' => $this->getGpsData(),
            'locationData' => $this->getLocationData(),
            'dataAndGNSS' => $this->getDataAndGNSS(),
        ];
    }
}
