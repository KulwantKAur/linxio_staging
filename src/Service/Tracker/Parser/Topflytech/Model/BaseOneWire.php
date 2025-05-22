<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Data;

/**
 * @example 252523003a0004086728406267183124102904342400004a00009a42bfe2e3428b70b4410000000001020e0cd98016b609000f980155006ff4a2
 */
class BaseOneWire implements DateTimePartPayloadInterface
{
    /**
     * To Maintain backward compatibility
     *
     * For pioneer version 1.0.0.7 onward, ibutton devices will be represented by device type 00
     * Other older versions and other devices will be represented by device type 01
     */
    public const IBUTTON_DEVICE_TYPES = ['00', '01'];
    public const IBUTTON_DEVICE_TYPE = '01';
    public const TEMPERATURE_SENSOR_DEVICE_TYPE = '28';
    public const TEMPERATURE_AND_HUMIDITY_SENSOR_DEVICE_TYPE = '2E';

    public $dateTime;
    public ?float $ignition;
    public ?string $deviceType;
    public ?string $deviceId;
    public $gpsData;
    public $locationData;

    public function __construct(array $data)
    {
        $this->dateTime = $data['dateTime'] ?? null;
        $this->ignition = $data['ignition'] ?? null;
        $this->deviceType = $data['deviceType'] ?? null;
        $this->deviceId = $data['deviceId'] ?? null;
        $this->gpsData = $data['gpsData'] ?? null;
        $this->locationData = $data['locationData'] ?? null;
    }

    public static function createFromTextPayload(string $textPayload, string $protocol = null): BaseOneWire
    {
        $dataAndGNSS = DataAndGNSS::createFromTextPayload(substr($textPayload, 16, 2));

        if ($dataAndGNSS->isGps()) {
            $gpsData = GpsData::createFromTextPayload(substr($textPayload, 18, 32));
        } else {
            $locationData = Location::createFromTextPayload(substr($textPayload, 18, 32));
        }

        $deviceType = substr($textPayload, 50, 2);
        $deviceId = substr($textPayload, 52, 16);

        return new static([
            'dateTime' => Data::formatDateTime(substr($textPayload, 0, 12)),
            'reserved' => substr($textPayload, 12, 2),
            'ignition' => hexdec(substr($textPayload, 14, 2)),
            'dataAndGNSS' => $dataAndGNSS,
            'gpsData' => $gpsData ?? null,
            'locationData' => $locationData ?? null,
            'deviceType' => $deviceType,
            'deviceId' => $deviceId,
        ]);
    }

    public function setDateTime(?\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    public function getDriverIdTag(): ?string
    {
        return $this->isIbutton() ? $this->getDeviceId() : null;
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        return substr($payload, Data::DATA_START_PACKET_POSITION + 0, 12);
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        return substr_replace($payload, $dtString, Data::DATA_START_PACKET_POSITION + 0, 12);
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getDeviceType(): ?string
    {
        return $this->deviceType;
    }

    public function setDeviceType(?string $deviceType): void
    {
        $this->deviceType = $deviceType;
    }

    public function isIbutton(): bool
    {
        return $this->getDeviceType() && in_array($this->getDeviceType(),  self::IBUTTON_DEVICE_TYPES);
    }

    public function getIgnition(): ?float
    {
        return $this->ignition;
    }

    public function setIgnition(?float $ignition): void
    {
        $this->ignition = $ignition;
    }

    public static function isRequestTypeWithData(string $BLEDataCode): bool
    {
        switch ($BLEDataCode) {
            case self::IBUTTON_DEVICE_TYPE:
                return true;
            default:
                return false;
        }
    }

    public static function isRequestTypeWithExtraData(string $BLEDataCode): bool
    {
        switch ($BLEDataCode) {
            case self::TEMPERATURE_SENSOR_DEVICE_TYPE:
            case self::TEMPERATURE_AND_HUMIDITY_SENSOR_DEVICE_TYPE:
                return true;
            default:
                return false;
        }
    }
}
