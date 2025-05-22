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
 * @example 27271000310057088888888888888820102612425101000201B3EC0011C03100C958866B4276D6E342912AB44111150005
 * @example 27271000310057088888888888888820102612425101000201B3EC0011C03101C958866B4276D6E342912AB44111150005
 */
class SOS extends BaseDataCode
{
    use MovementTrait;
    use IgnitionTrait;

    public const SOS_ALARM_TYPE = 0;
    public const LOW_BATTERY_ALARM_TYPE = 1;

    public $BLETagId;
    public $internalBatteryVoltage;
    public $type;
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
        $this->type = $data['type'] ?? null;
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
            'type' => hexdec(substr($textPayload, 14, 2)),
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
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isSOSAlarm(): bool
    {
        return $this->getType() == self::SOS_ALARM_TYPE;
    }

    /**
     * @return string|null
     */
    public function getBLETagId(): ?string
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
    public function getSOSData()
    {
        return $this->getDataArray();
    }

    /**
     * @return array
     */
    public function getDataArray(): array
    {
        return [
            'BLETagId' => $this->getBLETagId(),
            'internalBatteryVoltage' => $this->getInternalBatteryVoltage(),
            'type' => $this->getType(),
            'gpsData' => $this->getGpsData(),
            'locationData' => $this->getLocationData(),
            'dataAndGNSS' => $this->getDataAndGNSS(),
        ];
    }
}
