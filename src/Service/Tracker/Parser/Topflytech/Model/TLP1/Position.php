<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\Location;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;

/**
 * @example 272702004900010880616898888889000005051010050558866B4276D6E342912AB4411115050589989989910050334545101005058000000A00FF00FF200100980000FFFFFFFFFFFF
 */

class Position extends BasePosition implements DateTimePartPayloadInterface
{
    public const PACKET_LENGTH = 116;

    use MovementTrait;
    use IgnitionTrait;

    public $dateTime;
    public $gpsData;
    public $locationData;
    public $acceleration;
    public $batteryVoltagePercentage;
    public $internalBatteryVoltage;
    public $deviceTemperature;
    public $odometer;
    public $status;
    public $ignitionOnDuration;
    public $ignitionOffDuration;
    public $angleInterval;
    public $distanceInterval;
    public $solarPanelVoltage;
    public $setting1;
    public $setting2;
    public $setting3;
    public $solarChargingStatus;
    public $reserved;
    public ?DataAndGNSS $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->dateTime = $data['dateTime'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
        $this->acceleration = $data['acceleration'] ?? null;
        $this->batteryVoltagePercentage = $data['batteryVoltagePercentage'] ?? null;
        $this->deviceTemperature = $data['deviceTemperature'] ?? null;
        $this->internalBatteryVoltage = $data['internalBatteryVoltage'] ?? null;
        $this->solarPanelVoltage = $data['solarPanelVoltage'] ?? null;
        $this->odometer = DataHelper::formatValueIgnoreZero($data, 'odometer');
        $this->status = $data['status'] ?? null;
        $this->ignitionOnDuration = DataHelper::formatValueIgnoreZero($data, 'ignitionOnDuration');
        $this->ignitionOffDuration = DataHelper::formatValueIgnoreZero($data, 'ignitionOffDuration');
        $this->angleInterval = DataHelper::formatValueIgnoreZero($data, 'angleInterval');
        $this->distanceInterval = DataHelper::formatValueIgnoreZero($data, 'distanceInterval');
        $this->setting1 = $data['setting1'] ?? null;
        $this->setting2 = $data['setting2'] ?? null;
        $this->setting3 = $data['setting3'] ?? null;
        $this->reserved = $data['reserved'] ?? null;
        $this->solarChargingStatus = $this->status ? $this->status->solarChargingStatus : null;
        $this->dataAndGNSS = $data['dataAndGNSS'] ?? null;
        $this->formatMovement();
        $this->formatIgnition();
    }

    /**
     * @inheritDoc
     */
    public static function getPacketLength(): int
    {
        return self::PACKET_LENGTH;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 0, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 16, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 16, 32));
        }

        return new self([
            'dataAndGNSS' => $dataAndGNSS,
            'alarmCode' => hexdec(substr($textPayload, 2, 2)),
            'dateTime' => Data::formatDateTime(substr($textPayload, 4, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'acceleration' => self::formatAcceleration(substr($textPayload, 48, 10)),
            'batteryVoltagePercentage' => Data::formatBatteryVoltagePercentage(substr($textPayload, 58, 2)),
            'deviceTemperature' => BasePosition::formatDeviceTemperature(substr($textPayload, 60, 2)),
            'internalBatteryVoltage' => Data::formatInternalBatteryVoltage(substr($textPayload, 64, 2)),
            'solarPanelVoltage' => Data::formatSolarPanelVoltage(substr($textPayload, 66, 2)),
            'odometer' => hexdec(substr($textPayload, 68, 8)),
            'status' => PositionStatus::createFromTextPayload(substr($textPayload, 76, 4)),
            'ignitionOnDuration' => hexdec(substr($textPayload, 80, 4)),
            'ignitionOffDuration' => hexdec(substr($textPayload, 84, 8)),
            'angleInterval' => hexdec(substr($textPayload, 92, 2)),
            'distanceInterval' => hexdec(substr($textPayload, 94, 4)),
            'heartbeatDuration' => hexdec(substr($textPayload, 98, 2)),
            'setting1' => self::formatSetting1(substr($textPayload, 100, 2)),
            'setting2' => self::formatSetting2(substr($textPayload, 102, 2)),
            'setting3' => self::formatSetting2(substr($textPayload, 104, 2)),
            'reserved' => hexdec(substr($textPayload, 106, 10)),
        ]);
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime|null $dateTime
     */
    public function setDateTime(?\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
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
     * @return int|null
     */
    public function getIgnitionOnDuration(): ?int
    {
        return $this->ignitionOnDuration;
    }

    /**
     * @return int|null
     */
    public function getIgnitionOffDuration(): ?int
    {
        return $this->ignitionOffDuration;
    }

    /**
     * @return int|null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @return mixed|null
     */
    public function getStatus(): ?PositionStatus
    {
        return $this->status;
    }

    /**
     * @return float|null
     */
    public function getDeviceTemperature(): ?float
    {
        return $this->deviceTemperature;
    }

    /**
     * @return bool|null
     */
    public function getSolarChargingStatus(): ?bool
    {
        return $this->solarChargingStatus;
    }

    /**
     * @param bool|null $solarChargingStatus
     */
    public function setSolarChargingStatus(?bool $solarChargingStatus): void
    {
        $this->solarChargingStatus = $solarChargingStatus;
    }

    /**
     * @return float|int|null
     */
    public function getInternalBatteryVoltage()
    {
        return $this->internalBatteryVoltage;
    }

    /**
     * @return float|int|null
     */
    public function getBatteryVoltagePercentage()
    {
        return $this->batteryVoltagePercentage;
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        return substr($payload, Data::DATA_START_PACKET_POSITION + 4, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 4, 12);
    }
}
