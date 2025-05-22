<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Ulbotech\Data;

class DeviceStatusAlarm
{
    private const TOWING = 5;
    private const NOT_DEFINED = 6;
    private const PANIC_BUTTON = 10;

    public $poweredWithInternal;
    public $motion;
    public $overSpeed;
    public $jamming;
    public $panicButton;

    /**
     * DeviceStatusAlarm constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->poweredWithInternal = $data['poweredWithInternal'] ?? null;
        $this->motion = $data['motion'] ?? null;
        $this->overSpeed = $data['overSpeed'] ?? null;
        $this->jamming = $data['jamming'] ?? null;
        $this->panicButton = $data['panicButton'] ?? null;
    }

    /**
     * @param string $textPayload
     * @return static
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $dataPartSeparatorPosition = strpos($textPayload, Data::DATA_PART_SEPARATOR);
        $binaryAlarmValues = DataHelper::getBinaryFromHex(substr($textPayload, $dataPartSeparatorPosition + 1));
        $binaryAlarmValues = DataHelper::addZerosToEndOfString(strrev($binaryAlarmValues));
        $set = str_split($binaryAlarmValues);

        return self::createFromSet($set);
    }

    /**
     * @param array $set
     * @return self
     */
    public static function createFromSet(array $set): self
    {
        return new self([
            'poweredWithInternal' => Data::getIntValueFromSetByKey($set, DeviceStatus::POWERED_WITH_EXTERNAL_OR_INTERNAL),
            'motion' => Data::getIntValueFromSetByKey($set, DeviceStatus::MOVE_OR_STOP),
            'overSpeed' => Data::getIntValueFromSetByKey($set, DeviceStatus::OVER_SPEED),
            'jamming' => Data::getIntValueFromSetByKey($set, DeviceStatus::JAMMING),
            'panicButton' => Data::getIntValueFromSetByKey($set, self::PANIC_BUTTON),
        ]);
    }

    /**
     * @return mixed|null
     */
    public function getPanicButton(): ?int
    {
        return $this->panicButton;
    }

    /**
     * @return bool
     */
    public function isPanicButton(): bool
    {
        return boolval($this->getPanicButton());
    }
}
