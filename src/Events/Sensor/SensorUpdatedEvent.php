<?php

namespace App\Events\Sensor;

use App\Entity\Sensor;
use Symfony\Contracts\EventDispatcher\Event;

class SensorUpdatedEvent extends Event
{
    const NAME = 'app.event.sensor.updated';
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