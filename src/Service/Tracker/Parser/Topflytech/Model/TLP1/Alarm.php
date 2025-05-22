<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAlarm;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\Location;

/**
 * @example 272704004900010880616898888889000005051010050558866B4276D6E342912AB4411115050589989989910050334545101005058000000A00FF00FF200100980000FFFFFFFFFFFF
 */

class Alarm extends BaseAlarm implements DateTimePartPayloadInterface
{
    use MovementTrait;
    use IgnitionTrait;

    public const SOS_ALARM = 3;
    public const BATTERY_LOW_ALARM = 6;
    public const DEVICE_HIGH_TEMPERATURE = 8;

    public $dateTime;
    public $alarmCode;
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
    public $solarChargingStatus;
    public ?DataAndGNSS $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->dateTime = $data['dateTime'] ?? null;
        $this->alarmCode = $data['alarmCode'] ?? null;
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
        $this->solarChargingStatus = $this->status ? $this->status->solarChargingStatus : null;
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
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 0, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 16, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 16, 32));
        }

        return new self([
            'dataAndGNSS' => $dataAndGNSS,
            'alarmCode' => substr($textPayload, 2, 2),
            'dateTime' => Data::formatDateTime(substr($textPayload, 4, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'acceleration' => BasePosition::formatAcceleration(substr($textPayload, 48, 10)),
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
            'setting1' => BasePosition::formatSetting1(substr($textPayload, 100, 2)),
            'setting2' => BasePosition::formatSetting2(substr($textPayload, 102, 2)),
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
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return $this->alarmCode == self::SOS_ALARM;
    }

    /**
     * @return float|null
     */
    public function getDeviceTemperature(): ?float
    {
        return $this->deviceTemperature;
    }

    /**
     * @return float|int|null
     */
    public function getBatteryVoltagePercentage()
    {
        return $this->batteryVoltagePercentage;
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
    public function getSolarPanelVoltage()
    {
        return $this->solarPanelVoltage;
    }

    /**
     * @return float|int|null
     */
    public function getSolarChargingStatus()
    {
        return $this->solarChargingStatus;
    }

    /**
     * @return mixed|null
     */
    public function getIgnitionOnDuration(): ?int
    {
        return $this->ignitionOnDuration;
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

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->alarmCode;
    }
}
