<?php

namespace App\Events\Sensor;

use App\Entity\Sensor;
use Symfony\Contracts\EventDispatcher\Event;

class SensorCreatedEvent extends Event
{
    const NAME = 'app.event.sensor.created';
    protected $sensor;

    public function __construct(Sensor $sensor)
    {
        $this->sensor = $sensor;
    }

    public function getSensor(): Sensor
    {
        return $this->sensor;
    }
}