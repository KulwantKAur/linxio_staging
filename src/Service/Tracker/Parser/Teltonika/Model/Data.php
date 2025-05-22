<?php

declare(strict_types = 1);

namespace App\Service\Tracker\Parser\Teltonika\Model;

use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\SensorIOInterface;
use App\Service\Tracker\Parser\TrackerData;
use DateTimeImmutable;
use JsonSerializable;

class Data extends TrackerData implements JsonSerializable, DeviceDataInterface, SensorIOInterface
{
    public const DATETIME_LENGTH = 16;

    /**
     * @var DateTimeImmutable
     */
    private $dateTime;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var GpsData
     */
    private $gpsData;

    /**
     * @var SensorsData
     */
    private $sensorsData;

    /**
     * @param $payload
     * @param $position
     * @return bool|DateTimeImmutable
     * @throws \Exception
     */
    private static function getTimestampFromPayload(string $payload, int $position): DateTimeImmutable
    {
        // Timestamp needs to be a float because its containing milliseconds
        $timestamp = hexdec(substr($payload, $position, self::DATETIME_LENGTH)) / 1000;

        return (new DateTimeImmutable())->setTimestamp(intval($timestamp));
    }

    /**
     * @param $payload
     * @param $position
     * @return int
     */
    private static function getPriorityFromPayload(string $payload, int $position): int
    {
        return (int) hexdec(substr($payload, $position, 2));
    }

    public function __construct(
        DateTimeImmutable $dateTime,
        GpsData $gpsData,
        SensorsData $sensorsData,
        int $priority
    )
    {
        $this->dateTime = $dateTime;
        $this->priority = $priority;
        $this->gpsData = $gpsData;
        $this->sensorsData = $sensorsData;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTimeImmutable $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getGpsData(): GpsDataInterface
    {
        return $this->gpsData;
    }

    public function getSensorsData(): SensorsData
    {
        return $this->sensorsData;
    }

    /**
     * @return array|null
     */
    public function getSensorsIOData(): ?array
    {
        return $this->getSensorsData() ? $this->getSensorsData()->getIOData() : null;
    }

    public function jsonSerialize(): array
    {
        return [
            'dateTime' => $this->getDateTime(),
            'priority' => $this->getPriority(),
            'gpsData' => $this->getGpsData()
        ];
    }

    /**
     * @param string $payload
     * @param int $position
     * @return Data
     * @throws \Exception
     */
    public static function createFromHex(string $payload, int &$position): Data
    {
        $dateTime = self::getTimestampFromPayload($payload, $position);
        $position += self::DATETIME_LENGTH;

        $priority = self::getPriorityFromPayload($payload, $position);
        $position += 2;

        $gpsData = GpsData::createFromHex($payload, $position);
        $sensorsData = SensorsData::createFromHex($payload, $position);

        return new Data($dateTime, $gpsData, $sensorsData, $priority);
    }

    /**
     * @param $payload
     * @param $position
     * @return string
     * @throws \Exception
     */
    public static function getTimestampPayload(string $payload, int $position): string
    {
        return substr($payload, $position, self::DATETIME_LENGTH);
    }

    /**
     * @param $payload
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public static function getDateTimeValue(string $payload): \DateTimeInterface
    {
        return (new \DateTime())->setTimestamp(intval(hexdec($payload) / 1000));
    }

    /**
     * @param \DateTimeInterface $datetime
     * @return \DateTimeInterface
     */
    public static function encodeDateTime(\DateTimeInterface $datetime): string
    {
        $datetimeString = dechex($datetime->getTimestamp() * 1000);

        if (!$datetimeString) {
            throw new \InvalidArgumentException("Invalid datetime format, skipped.");
        }

        $datetimeStringLength = strlen($datetimeString);

        if ($datetimeStringLength < self::DATETIME_LENGTH) {
            $datetimeString = str_repeat('0', self::DATETIME_LENGTH - $datetimeStringLength) . $datetimeString;
        }

        return $datetimeString;
    }

    /**
     * @param string $payload
     * @param int $position
     * @param string $dtString
     * @return \DateTimeInterface
     */
    public static function getPayloadWithNewDateTime(string $payload, int $position, string $dtString): string
    {
        return substr_replace($payload, $dtString, $position, self::DATETIME_LENGTH);
    }

    /**
     * @inheritDoc
     */
    public function getIgnition(?bool $isFixWithSpeed = null)
    {
        $ignitionBySpeed = (!is_null($isFixWithSpeed) && $isFixWithSpeed)
            ? (($this->getGpsData()->getSpeed() > 0) ? 1 : 0)
            : null;

        return !is_null($ignitionBySpeed) ? $ignitionBySpeed : $this->ignition;
    }

    /**
     * @inheritDoc
     */
    public function getEngineOnTime()
    {
        // TODO: Implement getEngineOnTime() method.
    }

    /**
     * @inheritDoc
     */
    public function getDTCVINData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getDriverBehaviorData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getNetworkData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSatellites(): ?int
    {
        return $this->getGpsData()->getSatellites();
    }

    /**
     * @inheritDoc
     */
    public function getOdometer()
    {
        return null;
    }
}
