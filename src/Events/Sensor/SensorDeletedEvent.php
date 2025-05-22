<?php

namespace App\Events\Sensor;

use App\Entity\Sensor;
use Symfony\Contracts\EventDispatcher\Event;

class SensorDeletedEvent extends Event
{
    const NAME = 'app.event.sensor.deleted';

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