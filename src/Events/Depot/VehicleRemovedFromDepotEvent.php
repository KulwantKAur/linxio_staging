<?php

namespace App\Events\Depot;

use App\Entity\Depot;
use App\Entity\User;
use App\Entity\Vehicle;
use Symfony\Contracts\EventDispatcher\Event;

class VehicleRemovedFromDepotEvent extends Event
{
    const NAME = 'app.event.depot.vehicleRemoved';
    protected $vehicle;
    protected $depot;
    protected $currentUser;

    /**
     * @param Vehicle $vehicle
     * @param Depot $depot
     * @param User $currentUser
     */
    public function __construct(Vehicle $vehicle, Depot $depot, User $currentUser)
    {
        $this->vehicle = $vehicle;
        $this->depot = $depot;
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
     * @return Depot
     */
    public function getDepot(): Depot
    {
        return $this->depot;
    }

    /**
     * @return User
     */
    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}