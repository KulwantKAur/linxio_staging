<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAlarm;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\IOData;
use App\Service\Tracker\Parser\Topflytech\Model\Location;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\JammerTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;

/**
 * @example 262604005300010880616898888888000A00FF20010000200096009899101010100000101005050005051010050558866B4276D6E342912AB44100000505101011151010101010101010101010101010101010
 */

class Alarm extends BaseAlarm implements DateTimePartPayloadInterface
{
    use MovementTrait;
    use IgnitionTrait;
    use JammerTrait;

    public const EXTERNAL_POWER_DISCONNECTED_ALARM = 1;
    public const BATTERY_LOW_POWER_ALARM = 2;
    public const SOS_ALARM = 3;
    public const OVER_SPEED_ALARM = 4;
    public const JAMMER_START_ALARM = 21;
    public const JAMMER_END_ALARM = 22;

    public $ignitionOnDuration;
    public $ignitionOffDuration;
    public $angleInterval;
    public $distanceInterval;
    public $odometer;
    public $dateTime;
    public $batteryVoltagePercentage;
    public $externalVoltage;
    public $gpsData;
    public $locationData;
    public $speed;
    public $accumulatingFuel; // ml
    public $instantFuel; // ml
    public $restData;
    public $IOData;
    public $alarmType;
    public $RPM; // round per minute
    public $airInput; // g/s
    public $airPressure; // kPa
    public $coolantTemperature; // Celsius
    public $airInflowTemperature; // Celsius
    public $engineLoad; // %
    public $throttlePosition; // %
    public $remainFuelRate; // %
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
        $this->odometer = DataHelper::formatValueIgnoreZero($data, 'odometer');
        $this->dateTime = $data['dateTime'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
        $this->batteryVoltagePercentage = $data['batteryVoltagePercentage'] ?? null;
        $this->externalVoltage = $data['externalVoltage'] ?? null;
        $this->speed = $data['speed'] ?? null;
        $this->accumulatingFuel = $data['accumulatingFuel'] ?? null;
        $this->instantFuel = $data['instantFuel'] ?? null;
        $this->restData = $data['restData'] ?? null;
        $this->IOData = $data['IOData'] ?? null;
        $this->alarmType = $data['alarmType'] ?? null;
        $this->RPM = $data['RPM'] ?? null;
        $this->airInput = $data['airInput'] ?? null;
        $this->airPressure = $data['airPressure'] ?? null;
        $this->coolantTemperature = $data['coolantTemperature'] ?? null;
        $this->airInflowTemperature = $data['airInflowTemperature'] ?? null;
        $this->engineLoad = $data['engineLoad'] ?? null;
        $this->throttlePosition = $data['throttlePosition'] ?? null;
        $this->remainFuelRate = $data['remainFuelRate'] ?? null;
        $this->dataAndGNSS = $data['dataAndGNSS'] ?? null;
        $this->formatMovement($this->speed);
        $this->formatIgnition($this->speed);
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
            $gpsData = GpsDataNoSpeed::createFromTextPayload(substr($textPayload, 62, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 62, 32));
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
            'alarmType' => substr($textPayload, 36, 2),
            'reserve' => Data::formatReserve(substr($textPayload, 38, 2)),
            'odometer' => hexdec(substr($textPayload, 40, 8)),
            'batteryVoltagePercentage' => Data::formatBatteryVoltagePercentage(substr($textPayload, 48, 2)),
            'dateTime' => Data::formatDateTime(substr($textPayload, 50, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'externalVoltage' => Data::formatExternalVoltage(substr($textPayload, 94, 4)),
            'speed' => DataHelper::formatIntegerAndFraction(substr($textPayload, 98, 4), 3, 1),
            'accumulatingFuel' => Data::formatIntValueWithFF(substr($textPayload, 102, 8)),
            'instantFuel' => Data::formatIntValueWithFF(substr($textPayload, 110, 8)),
            'RPM' => Data::formatIntValueWithFF(substr($textPayload, 118, 4)),
            'airInput' => Data::formatIntValueWithFF(substr($textPayload, 122, 2)),
            'airPressure' => Data::formatIntValueWithFF(substr($textPayload, 124, 2)),
            'coolantTemperature' => Data::formatTemperatureMinus40(
                Data::formatIntValueWithFF(substr($textPayload, 126, 2))
            ),
            'airInflowTemperature' => Data::formatTemperatureMinus40(
                Data::formatIntValueWithFF(substr($textPayload, 128, 2))
            ),
            'engineLoad' => Data::formatIntValueWithFF(substr($textPayload, 130, 2)),
            'throttlePosition' => Data::formatIntValueWithFF(substr($textPayload, 132, 2)),
            'remainFuelRate' => Data::formatIntValueWithFF(substr($textPayload, 134, 2)),
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
    public function getGpsData(): ?GpsDataNoSpeed
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
     * @return IOData|null
     */
    public function getIOData(): ?IOData
    {
        return $this->IOData;
    }

    /**
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return $this->alarmType == self::SOS_ALARM;
    }

    /**
     * @return float|int|null
     */
    public function getBatteryVoltagePercentage()
    {
        return $this->batteryVoltagePercentage;
    }

    /**
     * @return int|null
     */
    public function getAccumulatingFuel()
    {
        return $this->accumulatingFuel;
    }

    /**
     * @return int|null
     */
    public function getInstantFuel()
    {
        return $this->instantFuel;
    }

    /**
     * @return int|null
     */
    public function getRPM()
    {
        return $this->RPM;
    }

    /**
     * @return int|null
     */
    public function getAirInput()
    {
        return $this->airInput;
    }

    /**
     * @return int|null
     */
    public function getAirPressure()
    {
        return $this->airPressure;
    }

    /**
     * @return int|null
     */
    public function getCoolantTemperature()
    {
        return $this->coolantTemperature;
    }

    /**
     * @return int|null
     */
    public function getAirInflowTemperature()
    {
        return $this->airInflowTemperature;
    }

    /**
     * @return int|null
     */
    public function getEngineLoad()
    {
        return $this->engineLoad;
    }

    /**
     * @return int|null
     */
    public function getThrottlePosition()
    {
        return $this->throttlePosition;
    }

    /**
     * @return int|null
     */
    public function getRemainFuelRate()
    {
        return $this->remainFuelRate;
    }

    /**
     * @return array
     */
    public function getOBDData(): array
    {
        return [
            'accumulatingFuel' => $this->getAccumulatingFuel(),
            'instantFuel' => $this->getInstantFuel(),
            'RPM' => $this->getRPM(),
            'airInput' => $this->getAirInput(),
            'airPressure' => $this->getAirPressure(),
            'coolantTemperature' => $this->getCoolantTemperature(),
            'airInflowTemperature' => $this->getAirInflowTemperature(),
            'engineLoad' => $this->getEngineLoad(),
            'throttlePosition' => $this->getThrottlePosition(),
            'remainFuelRate' => $this->getRemainFuelRate(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        return substr($payload, Data::DATA_START_PACKET_POSITION + 50, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 50, 12);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->alarmType;
    }
}
