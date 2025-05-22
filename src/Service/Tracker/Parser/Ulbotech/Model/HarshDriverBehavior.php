<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Ulbotech\Data;

/**
 * Class HarshDriverBehavior
 * @package App\Service\Tracker\Parser\Ulbotech\Model
 *
 * @example HDB:1 | HDB:2
 */
class HarshDriverBehavior
{
    public const RAPID_ACCELERATION = 'rapidAcceleration';
    public const RAPID_ACCELERATION_KEY = 0;
    public const ROUGH_BRAKING = 'roughBraking';
    public const ROUGH_BRAKING_KEY = 1;
    public const HARSH_COURSE = 'harshCourse';
    public const HARSH_COURSE_KEY = 2;
    public const NO_WARM_UP = 'noWarmUp';
    public const NO_WARM_UP_KEY = 3;
    public const LONG_IDLE = 'longIdle';
    public const LONG_IDLE_KEY = 4;
    public const FATIGUE_DRIVING = 'fatigueDriving';
    public const FATIGUE_DRIVING_KEY = 5;
    public const ROUGH_TERRAIN = 'roughTerrain';
    public const ROUGH_TERRAIN_KEY = 6;
    public const HIGH_RPM = 'highRpm';
    public const HIGH_RPM_KEY = 7;

    public $rapidAcceleration;
    public $roughBraking;
    public $harshCourse;
    public $noWarmUp;
    public $longIdle;
    public $fatigueDriving;
    public $roughTerrain;
    public $highRpm;

    /**
     * DeviceStatus constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->rapidAcceleration = $data[self::RAPID_ACCELERATION] ?? null;
        $this->roughBraking = $data[self::ROUGH_BRAKING] ?? null;
        $this->harshCourse = $data[self::HARSH_COURSE] ?? null;
        $this->noWarmUp = $data[self::NO_WARM_UP] ?? null;
        $this->longIdle = $data[self::LONG_IDLE] ?? null;
        $this->fatigueDriving = $data[self::FATIGUE_DRIVING] ?? null;
        $this->roughTerrain = $data[self::ROUGH_TERRAIN] ?? null;
        $this->highRpm = $data[self::HIGH_RPM] ?? null;
    }

    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $binaryStatusValues = DataHelper::getBinaryFromHex(substr($textPayload, 4));
        $set = str_split($binaryStatusValues);

        return self::createFromSet($set);
    }

    /**
     * @param array $set
     * @return self
     */
    public static function createFromSet(array $set): self
    {
        return new self([
            self::RAPID_ACCELERATION => Data::getIntValueFromSetByKey($set, self::RAPID_ACCELERATION_KEY),
            self::ROUGH_BRAKING => Data::getIntValueFromSetByKey($set, self::ROUGH_BRAKING_KEY),
            self::HARSH_COURSE => Data::getIntValueFromSetByKey($set, self::HARSH_COURSE_KEY),
            self::NO_WARM_UP => Data::getIntValueFromSetByKey($set, self::NO_WARM_UP_KEY),
            self::LONG_IDLE => Data::getIntValueFromSetByKey($set, self::LONG_IDLE_KEY),
            self::FATIGUE_DRIVING => Data::getIntValueFromSetByKey($set, self::FATIGUE_DRIVING_KEY),
            self::ROUGH_TERRAIN => Data::getIntValueFromSetByKey($set, self::ROUGH_TERRAIN_KEY),
            self::HIGH_RPM => Data::getIntValueFromSetByKey($set, self::HIGH_RPM_KEY),
        ]);
    }
}
