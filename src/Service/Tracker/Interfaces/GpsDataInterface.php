<?php

namespace App\Service\Tracker\Interfaces;

interface GpsDataInterface
{
    public function getLongitude();

    public function getLatitude();

    public function getAltitude();

    public function getAngle();

    public function getSpeed();
}