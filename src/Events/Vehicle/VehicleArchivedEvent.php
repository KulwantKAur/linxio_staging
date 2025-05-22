<?php

namespace App\Events\Vehicle;

use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class VehicleArchivedEvent extends Event
{
    const NAME = 'app.event.vehicle.archived';
    protected $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }
}