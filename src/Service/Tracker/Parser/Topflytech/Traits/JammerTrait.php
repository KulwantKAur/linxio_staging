<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Traits;

trait JammerTrait
{
    /**
     * @inheritDoc
     */
    public function isJammerAlarm(): bool
    {
        return $this->alarmType == self::JAMMER_START_ALARM || $this->alarmType == self::JAMMER_END_ALARM;
    }

    /**
     * @inheritDoc
     */
    public function isJammerAlarmStarted(): bool
    {
        return $this->alarmType == self::JAMMER_START_ALARM;
    }
}
