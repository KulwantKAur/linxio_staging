<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

abstract class BaseAlarm
{
    public const PACKET_MINIMAL_LENGTH = BasePosition::PACKET_MINIMAL_LENGTH;

    /**
     * @param string $textPayload
     * @return mixed
     */
    abstract public static function createFromTextPayload(string $textPayload);

    /**
     * @return bool
     */
    abstract public function isPanicButton(): bool;

    /**
     * @return string|int|null
     */
    abstract public function getType();

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->dataAndGNSS;
    }

    /**
     * @return bool
     */
    public function isJammerAlarm(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isJammerAlarmStarted(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isJammerAlarmEnd(): bool
    {
        return false;
    }
}
