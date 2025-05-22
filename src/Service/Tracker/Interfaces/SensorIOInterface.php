<?php

namespace App\Service\Tracker\Interfaces;

interface SensorIOInterface
{
    /**
     * @return array|null
     */
    public function getSensorsIOData(): ?array;
}