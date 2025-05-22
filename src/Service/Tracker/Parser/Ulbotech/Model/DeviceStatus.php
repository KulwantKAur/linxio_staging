<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Ulbotech\Data;

/**
 * Class DeviceStatus
 * @package App\Service\Tracker\Parser\Ulbotech\Model
 *
 * @example STT:C002;0 | STT:242;0
 */
class DeviceStatus
{
    public const POWERED_WITH_EXTERNAL_OR_INTERNAL = 0;
    public const MOVE_OR_STOP = 1;
    public const OVER_SPEED = 2;
    public const JAMMING = 3;
    public const GEO_FENCE = 4;
    public const IMMOBILIZE = 5;
    public const ACC = 6;
    public const ENGINE = 9;
    public const OBD = 11;
    public const ANGLE = 12;
    public const SPEED_RAPID = 13;
    public const DOMESTIC_ROAMING = 14;

    public $poweredWithInternal;
    public $move;
    public $overSpeed;
    public $engineOn;
    public $jamming;

    /**
     * DeviceStatus constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->poweredWithInternal = $data['poweredWithInternal'] ?? null;
        $this->move = $data['move'] ?? null;
        $this->overSpeed = $data['overSpeed'] ?? null;
        $this->engineOn = $data['engineOn'] ?? null;
        $this->jamming = $data['jamming'] ?? null;
    }

    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $dataPartSeparatorPosition = strpos($textPayload, Data::DATA_PART_SEPARATOR);
        $binaryStatusValues = DataHelper::getBinaryFromHex(substr($textPayload, 4, $dataPartSeparatorPosition - 4));
        $binaryStatusValues = DataHelper::addZerosToEndOfString(strrev($binaryStatusValues));
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
            'poweredWithInternal' => Data::getIntValueFromSetByKey($set, self::POWERED_WITH_EXTERNAL_OR_INTERNAL),
            'move' => Data::getIntValueFromSetByKey($set, self::MOVE_OR_STOP),
            'overSpeed' => Data::getIntValueFromSetByKey($set, self::OVER_SPEED),
            'engineOn' => Data::getIntValueFromSetByKey($set, self::ENGINE),
            'jamming' => Data::getIntValueFromSetByKey($set, self::JAMMING),
        ]);
    }

    /**
     * @param string $textPayload
     * @return array
     */
    public static function getStatusAndAlarmData(string $textPayload): array
    {
        return [
            'statuses' => self::createFromTextPayload($textPayload),
            'alarms' => DeviceStatusAlarm::createFromTextPayload($textPayload),
        ];
    }

    /**
     * @return int|null
     */
    public function getMove(): ?int
    {
        return $this->move;
    }

    /**
     * @param int|null $move
     */
    public function setMove(?int $move): void
    {
        $this->move = $move;
    }

    /**
     * @return int|null
     */
    public function getEngineOn(): ?int
    {
        return $this->engineOn;
    }

    /**
     * @param int|null $engineOn
     */
    public function setEngineOn(?int $engineOn): void
    {
        $this->engineOn = $engineOn;
    }
}
