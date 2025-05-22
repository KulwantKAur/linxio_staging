<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAlarm;
use App\Service\Tracker\Parser\Topflytech\Model\BaseOutputData;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\Location;

/**
 * Class Alarm
 * @example 252514005700010866425030756502000A00FF20010000204996009899101010109999055501550155015501550000101005050005051010050558866B4276D6E342912AB441111505050410101003FFFFFFFF50FFFFFFFFFF
 */
class Alarm extends BaseAlarm implements DateTimePartPayloadInterface
{
    use MovementTrait;
    use IgnitionTrait;

    public const EXTERNAL_POWER_DISCONNECTED_ALARM = 1;
    public const SOS_ALARM = 3; // not presented it the doc, not presented in the parser, just info from customer
    public const BATTERY_LOW_POWER_ALARM = 6;
    public const DEVICE_HIGH_TEMPERATURE_ALARM = 8;
    public const OVER_SPEED_ALARM = 70;

    // @todo implement if needed
    //1. Value=1,  Device removed alarm (triggered by back light sensor)
    //2. Value=5,  Start falling alarm
    //3. Value=6,  low battery alarm
    //4. Value=7, Battery recovered alarm
    //5. Value=8,  Device high temperature alarm
    //6. Value=9,  Vibration start alarm
    //7. Value=10, collision alarm
    //8. Value=12,  usb connected
    //9. Value=13,  usb disconnected.
    //10. Value=14, Enter geofence alarm
    //11.Value=15, Leave geofence alarm
    //12. Value=16, Ignition on alarm
    //13. Value=17, Ignition off alarm
    //14. Value=18,  Idle start alarm
    //15. Value=19,  Idle stop alarm
    //16. Value=20, power on alarm
    //17. Value=21, Device mounted alarm (triggered by back light sensor)
    //18. Value=24, Stop falling
    //19. Value=25, Device high temperature disappear
    //20. Value=26, Vibration stop alarm
    //21. Value=27, Collision stopped alarm
    //22. Value=29, power off alarm
    //23. Value=30, Device low temperature alarm
    //24. Value=31, Device low temperature disappear
    //25. Value=40,  DIN0 on
    //26. Value=41,  DIN0 off
    //27. Value=42,  DIN1 on
    //28. Value=43,  DIN1 off
    //29. Value=44,  DIN2 on
    //30. Value=45,  DIN2 off
    //25. Value=46, DIN3(from configurable input0) on
    //26. Value=47,  DIN3(from configurable input0) off
    //27. Value=48,  DIN4 (from configurable input1) on
    //28. Value=49,  DIN4 (from configurable input1) off
    //29. Value=50,  DIN5 (from configurable input2) on
    //30. Value=51,  DIN5(from configurable input2) off
    //31. Value=54 analog input0 voltage increase start alarm
    //32. Value=55, analog input0 voltage increase stop alarm
    //33. Value=56 analog input0 voltage decrease start alarm
    //34. Value=57, analog input0 voltage decrease stop alarm
    //35. Value=58 analog input1 voltage increase start alarm
    //36. Value=59 analog input1 voltage increase stop alarm
    //37. Value=60 analog input1 voltage decrease start alarm
    //38. Value=61 analog input1 voltage decrease stop alarm
    //39. Value=62 analog input2 voltage increase start alarm
    //40. Value=63 analog input2 voltage increase stop alarm
    //41. Value=64 analog input2 voltage decrease start alarm
    //42. Value=65 analog input2 voltage decrease stop alarm
    //43. Value=66 Jamming start alarm
    //44. Value=67 Jamming stop alarm
    //45. Value=68, external power disconnected alarm (from Circular Connector)
    //46. Value=69, external power connected alarm (from Circular Connector)
    //47. Value=70,  Over speed alarm
    //48. Value=71, towing alarm
    //49. Value=74,  Antitheft alarm
    //50. Value=75, external power low alarm

    public $angleInterval;
    public $distanceInterval;
    public $analogInput0;
    public $analogInput1;
    public $analogInput2;
    public $analogInput3;
    public $analogInput4;
    public $alarmType;
    public $odometer;
    public $dateTime;
    public $batteryVoltagePercentage;
    public $externalVoltage;
    public $gpsData;
    public $locationData;
    public $IOData;
    public $digitalOutput;
    public $ignitionOnDuration;
    public $ignitionOffDuration;
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
        $this->alarmType = $data['alarmType'] ?? null;
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
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return $this->alarmType == self::SOS_ALARM;
    }

    /**
     * @return BaseOutputData|null
     */
    public function getDigitalOutput(): ?BaseOutputData
    {
        return $this->digitalOutput;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->alarmType;
    }
}
