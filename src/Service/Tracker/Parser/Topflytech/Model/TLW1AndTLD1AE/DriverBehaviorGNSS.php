<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;
use App\Service\Tracker\Parser\Topflytech\Model\DriverBehaviorBase;
use App\Service\Tracker\Parser\Topflytech\Model\GpsData;

/**
 * Class DriverBehaviorGNSS
 * @example (HB) 252505003C000108806168988888880000000000000058866B4276D6E342912AB4411115050500000000000058866B4276D6E342912AB44111150505
 * @example (HA) 252505003C000108806168988888880100000000000058866B4276D6E342912AB4411115050500000000000058866B4276D6E342912AB44111150505
 */

class DriverBehaviorGNSS extends DriverBehaviorBase implements DateTimePartPayloadInterface
{
    public $behaviorTypeKey;
    public $dateTime;
    public $gpsData;
    public ?DataAndGNSS $dataAndGNSS;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->behaviorType = $data['behaviorType'] ?? null;
        $this->behaviorTypeKey = $data['behaviorTypeKey'] ?? null;
        $this->dateTime = $data['dateTime'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
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
        $gpsData = GpsData::createFromTextPayload(substr($textPayload, 14, 32));
        $behaviorTypeKey = hexdec(substr($textPayload, 0, 2));

        return new self([
            'behaviorType' => DriverBehaviorBase::formatBehaviorTypeGNSS($behaviorTypeKey),
            'behaviorTypeKey' => $behaviorTypeKey,
            'dateTime' => Data::formatDateTime(substr($textPayload, 2, 12)),
            'gpsData' => $gpsData ?? null,
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
     * @return null
     */
    public function getLocationData()
    {
        return null;
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
