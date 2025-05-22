<?php

namespace App\Events\Vehicle;

use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class VehicleStatusChangedEvent extends Event
{
    const NAME = 'app.event.vehicle.status.changed';
    protected $vehicle;
    protected $data;

    public function __construct(Vehicle $vehicle, array $data = [])
    {
        $this->vehicle = $vehicle;
        $this->data = $data;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function getData(): array
    {
        return $this->data;
    }
}