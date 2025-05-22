<?php

namespace App\Events\VehicleGroup;

use App\Entity\User;
use App\Entity\VehicleGroup;
use Symfony\Contracts\EventDispatcher\Event;

class VehicleGroupUpdatedEvent extends Event
{
    const NAME = 'app.event.vehicleGroup.updated';
    protected $vehicleGroup;
    protected $user;

    public function __construct(VehicleGroup $vehicleGroup, User $user)
    {
        $this->vehicleGroup = $vehicleGroup;
        $this->user = $user;
    }

    public function getVehicleGroup(): VehicleGroup
    {
        return $this->vehicleGroup;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}