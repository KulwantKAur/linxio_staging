<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\DriverBehaviorBase;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\Location;

/**
 * Class DriverBehaviorAM
 * @example (HB) 252506002C000108806168988888880000000000000000899899899158866B4276D6E342912AB44111150505
 * @example (HA) 252506002C000108806168988888880100000000000000899899899158866B4276D6E342912AB44111150505
 * @example (HC) 252506002C000108806168988888880200000000000000899899899158866B4276D6E342912AB44111150505
 */
class DriverBehaviorAM extends DriverBehaviorBase implements DateTimePartPayloadInterface
{
    public $dateTime;
    public $acceleration;
    public $gpsData;
    public $locationData;
    public ?DataAndGNSS $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->behaviorType = $data['behaviorType'] ?? null;
        $this->dateTime = $data['dateTime'] ?? null;
        $this->acceleration = $data['acceleration'] ?? null;
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
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 14, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 26, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 26, 32));
        }

        return new self([
            'dataAndGNSS' => $dataAndGNSS,
            'acceleration' => Data::formatAcceleration(substr($textPayload, 16, 10)),
            'behaviorType' => DriverBehaviorBase::formatBehaviorTypeAM(hexdec(substr($textPayload, 0, 2))),
            'dateTime' => Data::formatDateTime(substr($textPayload, 2, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
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
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        return substr($payload, Data::DATA_START_PACKET_POSITION + 2, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 2, 12);
    }
}
