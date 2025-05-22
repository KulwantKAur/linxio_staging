<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech;

use App\Service\Tracker\Interfaces\PanicButtonInterface;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Parser\TrackerData;
use App\Service\Tracker\Parser\Ulbotech\Model\AnalogData;
use App\Service\Tracker\Parser\Ulbotech\Model\DeviceStatus;
use App\Service\Tracker\Parser\Ulbotech\Model\DeviceStatusAlarm;
use App\Service\Tracker\Parser\Ulbotech\Model\Event;
use App\Service\Tracker\Parser\Ulbotech\Model\GpsData;
use App\Service\Tracker\Parser\Ulbotech\Model\HarshDriverBehavior;
use App\Service\Tracker\Parser\Ulbotech\Model\Location;

/**
 * Class Data
 * @package App\Service\Tracker\Parser\Ulbotech
 *
 * @example *TS01,861107034113663,123615090320,LBS:505;1;20C9;7EF5821;73,STT:C000;0,MGR:768609,ADC:0;12.64;1;31.20;2;4.30,EVT:1
 */
class Data extends TrackerData implements DeviceDataInterface, PanicButtonInterface
{
    public const DATA_SEPARATOR = ',';
    public const DATA_PART_SEPARATOR = ';';
    private const TYPE_GPS = 'GPS';
    private const TYPE_LBS = 'LBS';
    private const TYPE_STATUS = 'STT';
    private const TYPE_MILEAGE = 'MGR';
    private const TYPE_DEVICE_AD = 'ADC';
    private const TYPE_OBD = 'OBD';
    private const TYPE_OBD_ALARM = 'OAL';
    private const TYPE_FUEL = 'FUL';
    private const TYPE_DRIVER_BEHAVIOR = 'HDB';
    private const TYPE_VEHICLE_ID = 'VIN';
    private const TYPE_ENGINE_RUN_TIME = 'EGT';
    private const TYPE_EVENT = 'EVT';
    private const TYPE_TRIP = 'TRP';

    public const DATETIME_FORMAT = 'Hisdmy';

    public $protocol;
    public $imei;
    public $statusData;
    /** @var DeviceStatusAlarm|null $alarmData */
    public $alarmData;
    public $dateTime;
    public $gpsData;
    public $analogData;
    public $location;
    public $mileage;
    public $engineOnTime; // seconds
    public $fuelData;
    public $eventData;
    public $driverBehaviorData;
    public $tripData;

    /**
     * @param string $textPayload
     * @return int
     */
    private static function formatMileage(string $textPayload): int
    {
        return intval(substr($textPayload, 4));
    }

    /**
     * @param string $textPayload
     * @return int
     */
    private static function getFuel(string $textPayload): int
    {
        // FUL:4069
        return intval(substr($textPayload, 4));
    }

    /**
     * @param string $textPayload
     * @return int
     */
    private static function formatEngineOnTime(string $textPayload): int
    {
        // EGT:384691
        return intval(substr($textPayload, 4));
    }

    /**
     * @return mixed
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param mixed $protocol
     */
    public function setProtocol($protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * @return mixed
     */
    public function getImei()
    {
        return $this->imei;
    }

    /**
     * @param mixed $imei
     */
    public function setImei($imei): void
    {
        $this->imei = $imei;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    /**
     * @param mixed $dateTime
     */
    public function setDateTime($dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $textPayload
     * @return self|null
     * @throws \Exception
     */
    public function createFromTextPayload(string $textPayload): ?self
    {
        $data = explode(self::DATA_SEPARATOR, $textPayload);
        $this->setProtocol($data[0]);
        $this->setImei($data[1]);

        if (!isset($data[2]) || !self::formatDateTime($data[2])) {
            return null;
        }

        $this->setDateTime(self::formatDateTime($data[2]));

        foreach ($data as $key => $datum) {
            switch (substr($datum, 0, 3)) {
                case self::TYPE_GPS:
                    $this->setGpsData(GpsData::createFromTextPayload($datum));
                    break;
                case self::TYPE_STATUS:
                    $statusAndAlarmData = DeviceStatus::getStatusAndAlarmData($datum);
                    $this->setStatusData($statusAndAlarmData['statuses']);
                    $this->setAlarmData($statusAndAlarmData['alarms']);
                    break;
                case self::TYPE_MILEAGE:
                    $this->setMileage(self::formatMileage($datum));
                    break;
                case self::TYPE_LBS:
                    $this->setLocation(Location::createFromTextPayload($datum));
                    break;
                case self::TYPE_DEVICE_AD:
                    $this->setAnalogData(AnalogData::createFromTextPayload($datum));
                    break;
                case self::TYPE_FUEL:
                    $this->setFuelData(self::getFuel($datum));
                    break;
                case self::TYPE_EVENT:
                    $this->setEventData(Event::createFromTextPayload($datum));
                    break;
                case self::TYPE_DRIVER_BEHAVIOR:
                    $this->setDriverBehaviorData(HarshDriverBehavior::createFromTextPayload($datum));
                    break;
                case self::TYPE_OBD:
                    // @todo
                    break;
                case self::TYPE_TRIP:
                    // @todo
                    $this->setTripData(true);
                    break;
                case self::TYPE_ENGINE_RUN_TIME:
                    $this->setEngineOnTime(self::formatEngineOnTime($datum));
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    /**
     * @param array $set
     * @param $key
     * @return int|null
     */
    public static function getIntValueFromSetByKey(array $set, $key): ?int
    {
        return isset($set[$key]) ? intval($set[$key]) : null;
    }

    /**
     * @param array $set
     * @param $key
     * @return float|null
     */
    public static function getFloatValueFromSetByKey(array $set, $key): ?float
    {
        return isset($set[$key]) ? floatval($set[$key]) : null;
    }

    /**
     * @return GpsDataInterface
     */
    public function getGpsData(): GpsDataInterface
    {
        return $this->gpsData ?: new GpsData([]);
        // @todo implement LBS part for future
        // return $this->gpsData ?: ($this->getLocation() ?: new GpsData([]));
    }

    /**
     * @param GpsDataInterface $gpsData
     */
    public function setGpsData(GpsDataInterface $gpsData): void
    {
        $this->gpsData = $gpsData;
    }

    /**
     * @return mixed
     */
    public function getMileage()
    {
        return $this->mileage;
    }

    /**
     * @param mixed $mileage
     */
    public function setMileage($mileage): void
    {
        $this->mileage = $mileage;
    }

    /**
     * @return AnalogData|null
     */
    public function getAnalogData(): ?AnalogData
    {
        return $this->analogData;
    }

    /**
     * @param AnalogData $analogData
     */
    public function setAnalogData(AnalogData $analogData): void
    {
        $this->analogData = $analogData;
    }

    /**
     * @return DeviceStatus|null
     */
    public function getStatusData(): ?DeviceStatus
    {
        return $this->statusData;
    }

    /**
     * @param DeviceStatus $statusData
     */
    public function setStatusData($statusData): void
    {
        $this->statusData = $statusData;
    }

    /**
     * @return DeviceStatusAlarm|null
     */
    public function getAlarmData()
    {
        return $this->alarmData;
    }

    /**
     * @param DeviceStatusAlarm $alarmData
     */
    public function setAlarmData($alarmData): void
    {
        $this->alarmData = $alarmData;
    }

    /**
     * @return mixed
     */
    public function getEngineOnTime()
    {
        return $this->engineOnTime;
    }

    /**
     * @param mixed $engineOnTime
     */
    public function setEngineOnTime($engineOnTime): void
    {
        $this->engineOnTime = $engineOnTime;
    }

    /**
     * @return HarshDriverBehavior
     */
    public function getDriverBehaviorData()
    {
        return $this->driverBehaviorData;
    }

    /**
     * @param HarshDriverBehavior $driverBehaviorData
     */
    public function setDriverBehaviorData(HarshDriverBehavior $driverBehaviorData): void
    {
        $this->driverBehaviorData = $driverBehaviorData;
    }

    /**
     * @return Event
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * @param Event $eventData
     */
    public function setEventData($eventData): void
    {
        $this->eventData = $eventData;
    }

    /**
     * @return mixed
     */
    public function getFuelData()
    {
        return $this->fuelData;
    }

    /**
     * @param mixed $fuelData
     */
    public function setFuelData($fuelData): void
    {
        $this->fuelData = $fuelData;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getTripData()
    {
        return $this->tripData;
    }

    /**
     * @param mixed $tripData
     */
    public function setTripData($tripData): void
    {
        $this->tripData = $tripData;
    }

    /**
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return $this->getAlarmData() ? $this->getAlarmData()->isPanicButton() : false;
    }

    /**
     * @param string $textPayload
     * @return \DateTime|null
     * @throws \Exception
     */
    public static function formatDateTime(string $textPayload): ?\DateTime
    {
        $result = (new \DateTime())::createFromFormat(self::DATETIME_FORMAT, $textPayload);

        return $result ?: null;
    }

    /**
     * @param string $paylod
     * @return string
     * @throws \Exception
     */
    public static function getDatetimePayload(string $paylod): string
    {
        $data = explode(self::DATA_SEPARATOR, $paylod);

        return $data[2];
    }

    /**
     * @param \DateTimeInterface $datetime
     * @return \DateTime
     */
    public static function encodeDateTime(\DateTimeInterface $datetime): string
    {
        $datetimeString = $datetime->format(self::DATETIME_FORMAT);

        if (!$datetimeString) {
            throw new \InvalidArgumentException("Invalid datetime format, skipped.");
        }

        return $datetimeString;
    }

    /**
     * @param string $payload
     * @param string $dtString
     * @return string
     */
    public static function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        for ($i = 1, $offset = 0, $dtLength = 12; $i < 3; $i++) {
            $offset = strpos($payload, self::DATA_SEPARATOR, $offset + 1);
            $nextSeparatorPos = strpos($payload, self::DATA_SEPARATOR, $offset + 1);
            $dtLength = $nextSeparatorPos - $offset - 1;
        }

        return substr_replace($payload, self::DATA_SEPARATOR . $dtString, $offset, $dtLength + 1);
    }

    /**
     * @inheritDoc
     */
    public function getIgnition(?bool $isFixWithSpeed = null): ?int
    {
        $ignitionBySpeed = (!is_null($isFixWithSpeed) && $isFixWithSpeed)
            ? (($this->getGpsData()->getSpeed() > 0) ? 1 : 0)
            : null;
        $ignitionByData = $this->getStatusData() ? $this->getStatusData()->getEngineOn() : null;

        return !is_null($ignitionBySpeed) ? $ignitionBySpeed : $ignitionByData;
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
    public function getNetworkData()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSatellites(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getOdometer()
    {
        return $this->getMileage();
    }
}
