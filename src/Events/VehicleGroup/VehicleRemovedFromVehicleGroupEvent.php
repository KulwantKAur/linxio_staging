<?php

namespace App\Events\VehicleGroup;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use Symfony\Contracts\EventDispatcher\Event;

class VehicleRemovedFromVehicleGroupEvent extends Event
{
    const NAME = 'app.event.vehicleGroup.vehicleRemoved';
    protected $vehicle;
    protected $vehicleGroup;
    protected $currentUser;

    /**
     * @param Vehicle $vehicle
     * @param VehicleGroup $vehicleGroup
     * @param User $currentUser
     */
    public function __construct(Vehicle $vehicle, VehicleGroup $vehicleGroup, User $currentUser)
    {
        $this->vehicle = $vehicle;
        $this->vehicleGroup = $vehicleGroup;
        $this->currentUser = $currentUser;
    }

    /**
     * @return Vehicle
     */
    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @return VehicleGroup
     */
    public function getVehicleGroup(): VehicleGroup
    {
        return $this->vehicleGroup;
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}