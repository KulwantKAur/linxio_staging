<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\IOData;
use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;
use App\Service\Tracker\Parser\Topflytech\Model\BasePosition;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\Location;

/**
 * Class Position
 * @package App\Service\Tracker\Parser\Topflytech\Model
 *
 */
class Position extends BasePosition implements DateTimePartPayloadInterface
{
    public const PACKET_LENGTH = 106;

    use MovementTrait;
    use IgnitionTrait;

    public $ignitionOnDuration;
    public $ignitionOffDuration;
    public $angleInterval;
    public $distanceInterval;
    public $analogInput1;
    public $analogInput2;
    public $odometer;
    public $dateTime;
    public $batteryVoltagePercentage;
    public $externalVoltage;
    public $gpsData;
    public $locationData;
    public $IOData;
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
}
