<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Parser\Topflytech\Traits\IgnitionTrait;
use App\Service\Tracker\Parser\Topflytech\Traits\MovementTrait;

/**
 * Class DriverBehaviorBase
 */
abstract class DriverBehaviorBase
{
    use MovementTrait;
    use IgnitionTrait;

    private const HARSH_BRAKING_KEY = 0;
    private const HARSH_ACCELERATION_KEY = 1;
    private const HARSH_TURNING_KEY = 2;

    private const HIGH_SPEED_BRAKING_TYPE = 0;
    private const HIGH_SPEED_ACCELERATION_TYPE = 1;
    private const MIDDLE_SPEED_BRAKING_TYPE = 2;
    private const MIDDLE_SPEED_ACCELERATION_TYPE = 3;
    private const LOW_SPEED_BRAKING_TYPE = 4;
    private const LOW_SPEED_ACCELERATION_TYPE = 5;

    public const HARSH_BRAKING = 'harsh_braking';
    public const HARSH_ACCELERATION = 'harsh_acceleration';
    public const HARSH_TURNING = 'harsh_turning';

    public const PACKET_MINIMAL_LENGTH = 100;

    public $behaviorType;

    abstract public static function createFromTextPayload(string $textPayload);
    abstract public function getGpsData();
    abstract public function getLocationData();
    abstract public function getDateTime();

    public function getBehaviorType()
    {
        return $this->behaviorType;
    }

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->dataAndGNSS;
    }

    /**
     * @param int|null $behaviorTypeKey
     * @return string|null
     */
    public static function formatBehaviorTypeAM(?int $behaviorTypeKey): ?string
    {
        switch ($behaviorTypeKey) {
            case self::HARSH_BRAKING_KEY:
                $behaviorType = self::HARSH_BRAKING;
                break;
            case self::HARSH_ACCELERATION_KEY:
                $behaviorType = self::HARSH_ACCELERATION;
                break;
            case self::HARSH_TURNING_KEY:
                $behaviorType = self::HARSH_TURNING;
                break;
            default:
                $behaviorType = null;
        }

        return $behaviorType;
    }

    /**
     * @param int|null $behaviorTypeKey
     * @return string|null
     */
    public static function formatBehaviorTypeGNSS(?int $behaviorTypeKey): ?string
    {
        switch ($behaviorTypeKey) {
            case self::HIGH_SPEED_BRAKING_TYPE:
            case self::MIDDLE_SPEED_BRAKING_TYPE:
            case self::LOW_SPEED_BRAKING_TYPE:
                $behaviorType = self::HARSH_BRAKING;
                break;
            case self::HIGH_SPEED_ACCELERATION_TYPE:
            case self::MIDDLE_SPEED_ACCELERATION_TYPE:
            case self::LOW_SPEED_ACCELERATION_TYPE:
                $behaviorType = self::HARSH_ACCELERATION;
                break;
            default:
                $behaviorType = null;
        }

        return $behaviorType;
    }
}
