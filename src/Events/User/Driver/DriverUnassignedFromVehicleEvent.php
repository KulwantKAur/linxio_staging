<?php

namespace App\Events\User\Driver;

use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class DriverUnassignedFromVehicleEvent extends Event
{
    const NAME = 'app.event.driver.unassignedFromVehicle';

    protected $device;
    protected $trackerHistoryData;

    public function __construct(
        private readonly User    $driver,
        private readonly Vehicle $vehicle
    ) {
    }

    public function getDriver(): User
    {
        return $this->driver;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }
}
