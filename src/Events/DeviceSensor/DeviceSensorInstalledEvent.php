<?php

namespace App\Events\DeviceSensor;

use App\Entity\DeviceSensor;
use Symfony\Contracts\EventDispatcher\Event;

class DeviceSensorInstalledEvent extends Event
{
    public const NAME = 'app.event.deviceSensor.installed';

    protected $deviceSensor;

    public function __construct(DeviceSensor $deviceSensor)
    {
        $this->deviceSensor = $deviceSensor;
    }

    public function getDeviceSensor(): DeviceSensor
    {
        return $this->deviceSensor;
    }
}