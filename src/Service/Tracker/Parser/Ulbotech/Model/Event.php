<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Ulbotech\Data;

/**
 * Class Event
 * @package App\Service\Tracker\Parser\Ulbotech\Model
 *
 * @example EVT:1 | EVT:F0;200 | EVT:F0;202
 */
class Event
{
    private const NOT_EVENT_TRIGGERED = '00';
    private const INTERVAL_TRIGGERED = '01';
    private const ANGLE_TRIGGERED = '02';
    private const DISTANCE_TRIGGERED = '03';
    private const REQUEST_TRIGGERED = '04';
    private const RFID_READER_TRIGGERED = '10';
    private const IBEACON_TRIGGERED = '11';
    private const GEO_FENCE_TRIGGERED = '80';
    private const DRIVER_BEHAVIOR_TRIGGERED = '90';
    private const STATUS_CHANGED_TRIGGERED = 'F0';
    private const ALARM_TRIGGERED = 'F8';
    private const MIN_VALUE_WITH_ADD_INFO = 128;

    private $noEvent;
    private $interval;
    private $angle;
    private $distance;
    private $status;
    private $driverBehavior;
    private $alarm;
    private $subset;

    /**
     * AnalogData constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->noEvent = $data['noEvent'] ?? null;
        $this->interval = $data['interval'] ?? null;
        $this->angle = $data['angle'] ?? null;
        $this->distance = $data['distance'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->alarm = $data['alarm'] ?? null;
        $this->driverBehavior = $data['driverBehavior'] ?? null;
        $this->subset = $data['subset'] ?? null;
    }

    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $set = explode(Data::DATA_PART_SEPARATOR, substr($textPayload, 4));
        $subSet = [];
        $keyName = $set[0];

        if (hexdec($keyName) >= self::MIN_VALUE_WITH_ADD_INFO) {
            $binaryValues = strrev(DataHelper::getBinaryFromHex($set[1]));
            $subSetValues = str_split($binaryValues);

            switch ($keyName) {
                case self::DRIVER_BEHAVIOR_TRIGGERED:
                    $subSet = HarshDriverBehavior::createFromSet($subSetValues);
                    break;
                case self::STATUS_CHANGED_TRIGGERED:
                    $subSet = DeviceStatus::createFromSet($subSetValues);
                    break;
                case self::ALARM_TRIGGERED:
                    $subSet = DeviceStatusAlarm::createFromSet($subSetValues);
                    break;
                case self::GEO_FENCE_TRIGGERED:
                default:
                    $subSet = [];
                    break;
            }
        }

        return self::createFromKeyAndSubset($keyName, $subSet);
    }

    /**
     * @param $key
     * @param $subSet
     * @return self
     */
    public static function createFromKeyAndSubset($key, $subSet = []): self
    {
        return new self([
            'noEvent' => self::isKeyEqual($key, self::NOT_EVENT_TRIGGERED),
            'interval' => self::isKeyEqual($key, self::INTERVAL_TRIGGERED),
            'angle' => self::isKeyEqual($key, self::ANGLE_TRIGGERED),
            'distance' => self::isKeyEqual($key, self::DISTANCE_TRIGGERED),
            'status' => self::isKeyEqual($key, self::STATUS_CHANGED_TRIGGERED),
            'alarm' => self::isKeyEqual($key, self::ALARM_TRIGGERED),
            'driverBehavior' => self::isKeyEqual($key, self::DRIVER_BEHAVIOR_TRIGGERED),
            'subset' => $subSet
        ]);
    }

    /**
     * @param $key
     * @param $paramKey
     * @return int|null
     */
    private static function isKeyEqual($key, $paramKey)
    {
        return ($key == $paramKey) ? 1 : null;
    }
}
