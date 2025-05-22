<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\BaseAccidentViaAcc;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Model\Location;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;

/**
 * @todo implement nested packets
 * Class AccidentViaAcc
 * @example 25250701B4000108806168988888880100000000000000899899899058866B4276D6E342912AB44111150505
 */

class AccidentViaAcc extends BaseAccidentViaAcc implements DateTimePartPayloadInterface
{
    private const DISABLED_KEY = 0;
    private const HAPPEN_KEY = 1;

    public $dateTime;
    public $acceleration;
    public $gpsData;
    public $locationData;
    public $accidentCode;
    public ?DataAndGNSS $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->accidentCode = $data['accidentCode'] ?? null;
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
            'accidentCode' => hexdec(substr($textPayload, 0, 2)),
            'dateTime' => Data::formatDateTime(substr($textPayload, 2, 12)),
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isHappened(): bool
    {
        return $this->accidentCode == self::HAPPEN_KEY;
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
     * @param string $textPayload
     * @return bool
     */
    public static function hasPosition(string $textPayload): bool
    {
        return substr($textPayload, 2, 6) == (TcpDecoder::PROTOCOL_TLW1 . Data::POSITION_MESSAGE_TYPE);
    }

    /**
     * @param string $textPayload
     * @return string
     */
    public static function getPositionPayload(string $textPayload): string
    {
        return substr($textPayload, 32);
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
