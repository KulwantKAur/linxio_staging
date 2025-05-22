<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseOutputData;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\Location;

/**
 * Class Position
 * @example 252513005700010880616898888888000A00FF20010000200096009899101010109999055501550155015501550000101005050005051010050558866B4276D6E342912AB441111505050410101003FFFFFFFF50FFFFFFFFFF
 */
class Position extends BasePosition implements DateTimePartPayloadInterface
{
    public const PACKET_LENGTH = 148;

    use MovementTrait;
    use IgnitionTrait;

    public $ignitionOnDuration;
    public $ignitionOffDuration;
    public $angleInterval;
    public $distanceInterval;
    public $analogInput0;
    public $analogInput1;
    public $analogInput2;
    public $analogInput3;
    public $analogInput4;
    public $odometer;
    public $dateTime;
    public $batteryVoltagePercentage;
    public $externalVoltage;
    public $gpsData;
    public $locationData;
    public $IOData;
    public $digitalOutput;
    public $internalBatteryVoltage;
    public $deviceTemperature;
    public ?DataAndGNSS $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->ignitionOnDuration = DataHelper::formatValueIgnoreZero($data, 'ignitionOnDuration');
        $this->ignitionOffDuration = DataHelper::formatValueIgnoreZero($data, 'ignitionOffDuration');
        $this->angleInterval = DataHelper::formatValueIgnoreZero($data, 'angleInterval');
        $this->distanceInterval = DataHelper::formatValueIgnoreZero($data, 'distanceInterval');
        $this->analogInput0 = DataHelper::formatValueIgnoreZero($data, 'analogInput0');
        $this->analogInput1 = DataHelper::formatValueIgnoreZero($data, 'analogInput1');
        $this->analogInput2 = DataHelper::formatValueIgnoreZero($data, 'analogInput2');
        $this->analogInput3 = DataHelper::formatValueIgnoreZero($data, 'analogInput3');
        $this->analogInput4 = DataHelper::formatValueIgnoreZero($data, 'analogInput4');
        $this->odometer = DataHelper::formatValueIgnoreZero($data, 'odometer');
        $this->dateTime = $data['dateTime'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
        $this->batteryVoltagePercentage = $data['batteryVoltagePercentage'] ?? null;
        $this->externalVoltage = $data['externalVoltage'] ?? null;
        $this->IOData = $data['IOData'] ?? null;
        $this->digitalOutput = $data['digitalOutput'] ?? null;
        $this->internalBatteryVoltage = $data['internalBatteryVoltage'] ?? null;
        $this->deviceTemperature = $data['deviceTemperature'] ?? null;
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
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 18, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 86, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 86, 32));
        }

        return new self([
            'ignitionOnDuration' => hexdec(substr($textPayload, 0, 4)),
            'ignitionOffDuration' => hexdec(substr($textPayload, 4, 4)),
            'angleInterval' => hexdec(substr($textPayload, 8, 2)),
            'distanceInterval' => hexdec(substr($textPayload, 10, 4)),
            'overSpeedAlarmAndNetwork' => Data::formatOverSpeedAlarmAndNetwork(substr($textPayload, 14, 4)),
            'dataAndGNSS' => $dataAndGNSS,
            'gsensor' => Data::formatGsensor(substr($textPayload, 20, 2)),
            'other' => Data::formatOther(substr($textPayload, 22, 2)),
            'heartbeatDuration' => hexdec(substr($textPayload, 24, 2)),
            'relayStatus' => Data::formatRelayStatus(substr($textPayload, 26, 2)),
            'dragAlarm' => Data::formatDragAlarm(substr($textPayload, 28, 4)),
            'IOData' => IOData::createFromTextPayload(substr($textPayload, 32, 4)),
            'digitalOutput' => OutputData::createFromTextPayload(substr($textPayload, 36, 2)),
            'reserved' => substr($textPayload, 38, 2),
            'analogInput0' => Data::formatAnalogInputTLW2(substr($textPayload, 40, 4)),
            'analogInput1' => Data::formatAnalogInputTLW2(substr($textPayload, 44, 4)),
            'analogInput2' => Data::formatAnalogInputTLW2(substr($textPayload, 48, 4)),
            'analogInput3' => Data::formatAnalogInputTLW2(substr($textPayload, 52, 4)),
            'analogInput4' => Data::formatAnalogInputTLW2(substr($textPayload, 56, 4)),
            'alarmType' => substr($textPayload, 60, 2),
            'reserve' => Data::formatReserve(substr($textPayload, 62, 2)),
            'odometer' => hexdec(substr($textPayload, 64, 8)),
            'batteryVoltagePercentage' => Data::formatBatteryVoltagePercentage(substr($textPayload, 72, 2)),
            'dateTime' => Data::formatDateTime(substr($textPayload, 74, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'internalBatteryVoltage' => Data::formatInternalBatteryVoltageTLW2(substr($textPayload, 118, 4)),
            'externalVoltage' => Data::formatExternalVoltage(substr($textPayload, 122, 4)),
            'rpm' => substr($textPayload, 126, 4), // @todo
            'smartUpload' => substr($textPayload, 130, 2), // @todo
            'batteryMonitoring' => substr($textPayload, 132, 4), // @todo
            'deviceTemperature' => BasePosition::formatDeviceTemperature(substr($textPayload, 136, 2)),
            'reserved2' => substr($textPayload, 138, 10),
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
     * @return float|null
     */
    public function getExternalVoltage(): ?float
    {
        return $this->externalVoltage;
    }

    /**
     * @return int|null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @return IOData|null
     */
    public function getIOData(): ?IOData
    {
        return $this->IOData;
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
        return substr($payload, Data::DATA_START_PACKET_POSITION + 58, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 58, 12);
    }

    /**
     * @return float|null
     */
    public function getInternalBatteryVoltage(): ?float
    {
        return $this->internalBatteryVoltage;
    }

    /**
     * @param float|null $internalBatteryVoltage
     * @return self
     */
    public function setInternalBatteryVoltage($internalBatteryVoltage): self
    {
        $this->internalBatteryVoltage = $internalBatteryVoltage;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getDeviceTemperature(): ?float
    {
        return $this->deviceTemperature;
    }

    /**
     * @param float|null $deviceTemperature
     * @return self
     */
    public function setDeviceTemperature($deviceTemperature): self
    {
        $this->deviceTemperature = $deviceTemperature;

        return $this;
    }

    /**
     * @return BaseOutputData|null
     */
    public function getDigitalOutput(): ?BaseOutputData
    {
        return $this->digitalOutput;
    }
}
