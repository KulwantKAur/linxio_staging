<?php

namespace App\Service\Streamax\Model;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\DataHelper;

class StreamaxAlarm extends StreamaxModel implements ImeiInterface, DateTimePartPayloadInterface, GpsDataInterface
{
    public CONST TYPE_SOS = 7;
    public CONST TYPE_SPEEDING_ALARM = 8;
    public CONST TYPE_LOW_VOLTAGE = 9;
    public CONST TYPE_RAPID_ACCELERATION = 18006;
    public CONST TYPE_RAPID_DECELERATION = 18007;
    public CONST TYPE_SHARP_LEFT_TURN = 18010;
    public CONST TYPE_SHARP_RIGHT_TURN = 18011;
    public CONST TYPE_SPEED_LIMIT_SIGN_ALARM = 56007;
    public CONST TYPE_UNFASTENED_SEAT_BELT = 56016;
    // all types: https://ftcloud.streamax.com:20002/DOC/Webhooks#alarm

    public const ACTION_START = 'START';
    public const ACTION_END = 'END';

    public ?string $alarmId;
    public ?int $altitude;
    public ?int $angle;
    public ?string $fleetName;
    public ?float $lat;
    public ?float $lng;
    public ?int $signalStrength;
    public ?float $speed;
    public string $time; // RFC3339
    public ?string $startTime; // RFC3339
    public ?string $endTime; // RFC3339
    public ?string $uniqueId; // Unique identifier of a device
    public ?string $vehicleId;
    public ?string $vehicleNumber;
    public ?string $driverName;
    public ?string $action; // START|END
    public ?int $alarmType;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->alarmId = $fields['alarmId'] ?? null;
        $this->altitude = $fields['gpsAltitude'] ?? null;
        $this->angle = $fields['gpsAngle'] ?? null;
        $this->lat = $fields['gpsLat'] ?? null;
        $this->lng = $fields['gpsLng'] ?? null;
        $this->fleetName = $fields['fleetName'] ?? null;
        $this->signalStrength = $fields['signalStrength'] ?? null;
        $this->speed = $fields['gpsSpeed'] ?? null;
        $this->time = $fields['startTime'];
        $this->startTime = $fields['startTime'];
        $this->endTime = $fields['endTime'];
        $this->uniqueId = $fields['uniqueId'] ?? null;
        $this->vehicleId = $fields['vehicleId'] ?? null;
        $this->vehicleNumber = $fields['vehicleNumber'] ?? null;
        $this->driverName = $fields['driverName'] ?? null;
        $this->alarmType = $fields['alarmType'] ?? null;
        $this->action = $fields['action'] ?? null;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return $this->getUniqueId();
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'alarmId' => $this->getAlarmId(),
            'uniqueId' => $this->getUniqueId(),
            'angle' => $this->getAngle(),
            'lat' => $this->getLat(),
            'lng' => $this->getLng(),
            'speed' => $this->getSpeed(),
            'time' => $this->getTime(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        // TODO: Implement getPayloadWithNewDateTime() method.
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        // TODO: Implement getDateTimePayload() method.
    }

    /**
     * @return int|null
     */
    public function getAngle(): ?int
    {
        return $this->angle;
    }

    /**
     * @param int|null $angle
     */
    public function setAngle(?int $angle): void
    {
        $this->angle = $angle;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lat
     */
    public function setLat(?float $lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return float|null
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float|null $lng
     */
    public function setLng(?float $lng): void
    {
        $this->lng = $lng;
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
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getMovement(): int
    {
        return ($this->getSpeed() > 0) ? 1 : 0;
    }

    /**
     * @return int
     */
    public function getIgnition(): int
    {
        return ($this->getSpeed() > 0) ? 1 : 0;
    }

    public function getLongitude()
    {
        return $this->getLng();
    }

    public function getLatitude()
    {
        return $this->getLat();
    }

    /**
     * @return int|null
     */
    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return int|null
     */
    public function getAlarmType(): ?int
    {
        return $this->alarmType;
    }

    /**
     * @param int|null $alarmType
     */
    public function setAlarmType(?int $alarmType): void
    {
        $this->alarmType = $alarmType;
    }

    /**
     * @return bool
     */
    public function isSOSType(): bool
    {
        return $this->getAlarmType() === self::TYPE_SOS;
    }

    /**
     * @return bool
     */
    public function isForDrivingBehavior(): bool
    {
        return in_array($this->getAlarmType(), [
            self::TYPE_RAPID_ACCELERATION,
            self::TYPE_RAPID_DECELERATION,
            self::TYPE_SHARP_LEFT_TURN,
            self::TYPE_SHARP_RIGHT_TURN,
        ]);
    }

    /**
     * @return bool
     */
    public function isHarshCornering(): bool
    {
        return in_array($this->getAlarmType(), [
            self::TYPE_SHARP_LEFT_TURN,
            self::TYPE_SHARP_RIGHT_TURN,
        ]);
    }

    /**
     * @return bool
     */
    public function isOverspeeding(): bool
    {
        return in_array($this->getAlarmType(), [
            self::TYPE_SPEEDING_ALARM,
            self::TYPE_SPEED_LIMIT_SIGN_ALARM,
        ]);
    }

    /**
     * @return bool
     */
    public function isHarshBraking(): bool
    {
        return $this->getAlarmType() === self::TYPE_RAPID_DECELERATION;
    }

    /**
     * @return bool
     */
    public function isHarshAcceleration(): bool
    {
        return $this->getAlarmType() === self::TYPE_RAPID_ACCELERATION;
    }

    /**
     * @return string|null
     */
    public function getAlarmId(): ?string
    {
        return $this->alarmId;
    }

    /**
     * @param string|null $alarmId
     */
    public function setAlarmId(?string $alarmId): void
    {
        $this->alarmId = $alarmId;
    }

    /**
     * @return string|null
     */
    public function getStartTime(): mixed
    {
        return $this->startTime;
    }

    /**
     * @return string|null
     */
    public function getEndTime(): mixed
    {
        return $this->endTime;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStartTimeAsDate(): ?\DateTimeInterface
    {
        return $this->getStartTime() ? $this->getDateTimeFromString($this->getStartTime()) : null;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndTimeAsDate(): ?\DateTimeInterface
    {
        return $this->getEndTime() ? $this->getDateTimeFromString($this->getEndTime()) : null;
    }
}

