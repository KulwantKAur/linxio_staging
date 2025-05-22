<?php

namespace App\Service\Tracker\Interfaces;

interface SimulatorTrackerInterface
{
    public function getTrackNumber($imei): int;
}