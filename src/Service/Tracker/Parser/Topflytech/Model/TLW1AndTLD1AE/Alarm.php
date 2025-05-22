<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAlarm;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\IOData;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\JammerTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\Location;

/**
 * Class Alarm
 * @example 252504004400010880616898888888000A00FF2001000020009600989910101010055501550000101005050005051010050558866B4276D6E342912AB441111505051010
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

    // @todo implement if needed
    // 5. Value=5, into geofence alarm
    // 6. Value=6, out of geofence alarm
    // 7. Value=7, drag alarm
    // 8. Value=8, vibration alarm
    // 9. Value=9, device get address for postion SMS
    // 10. Value=10, antitheft alarm
    // 11. Value=11, analog input1 voltage increase alarm
    // 12. Value=12, analog input1 voltage decrease alarm
    // 13. Value=13, analog input2 voltage increase alarm
    // 14. Value=14, analog input2 voltage decrease alarm
    // 15. Value=15, ACC on alarm
    // 16. Value=16, ACC off alarm
    // 17. Value=17, AC on alarm
    // 18. Value=18, AC off alarm
    // 19. Value=19, one idling start alarm. Customer can define the idling timing
    // 20. Value=20, one idling end alarm
    // 21. Value=21, signal jammer start alarm.  (config with JAMMERD command)
    // 22. Value=22, signal jammer end alarm
    // 23. Value=23, external power recover alarm
    // 24. Value=24, alarm of the external power voltage lower than threshold value. (config with AEPOWER command)
    // 25. Value=25, digital 3 input from 0 to 1;
    // 26. Value=26, digital 3 input from 1 to 0;
    // 27. Value=27, digital 4 input from 0 to 1;
    // 28. Value=28, digital 4 input from 1 to 0;

    public $angleInterval;
    public $distanceInterval;
    public $analogInput1;
    public $analogInput2;
    public $alarmType;
    public $odometer;
    public $dateTime;
    public $batteryVoltagePercentage;
    public $externalVoltage;
    public $gpsData;
    public $locationData;
    public $IOData;
    public $ignitionOnDuration;
    public $ignitionOffDuration;
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
        $this->analogInput1 = DataHelper::formatValueIgnoreZero($data, 'analogInput1');
        $this->analogInput2 = DataHelper::formatValueIgnoreZero($data, 'analogInput2');
        $this->alarmType = $data['alarmType'] ?? null;
        $this->odometer = DataHelper::formatValueIgnoreZero($data, 'odometer');
        $this->dateTime = $data['dateTime'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
        $this->batteryVoltagePercentage = $data['batteryVoltagePercentage'] ?? null;
        $this->externalVoltage = $data['externalVoltage'] ?? null;
        $this->IOData = $data['IOData'] ?? null;
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
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 70, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 70, 32));
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
            'analogInput1' => Data::formatAnalogInput(substr($textPayload, 36, 4)),
            'analogInput2' => Data::formatAnalogInput(substr($textPayload, 40, 4)),
            'alarmType' => substr($textPayload, 44, 2),
            'reserve' => Data::formatReserve(substr($textPayload, 46, 2)),
            'odometer' => hexdec(substr($textPayload, 48, 8)),
            'batteryVoltagePercentage' => Data::formatBatteryVoltagePercentage(substr($textPayload, 56, 2)),
            'dateTime' => Data::formatDateTime(substr($textPayload, 58, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'externalVoltage' => Data::formatExternalVoltage(substr($textPayload, 102, 4)),
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
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return $this->alarmType == self::SOS_ALARM;
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
     * @inheritDoc
     */
    public function getType()
    {
        return $this->alarmType;
    }
}
