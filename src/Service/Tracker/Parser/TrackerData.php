<?php

namespace App\Service\Tracker\Parser;

class TrackerData
{
    public function isJammerAlarmStarted(): bool
    {
        return false;
    }

    public function isAccidentHappened(): bool
    {
        return false;
    }

    public function isIButton(): bool
    {
        return false;
    }

    public function getDriverIdTag(): ?string
    {
        return null;
    }

    public function getDriverFOBId(): ?string
    {
        return null;
    }

    public function isCameraEventReceived(): bool
    {
        return false;
    }
}
