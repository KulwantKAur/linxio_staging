<?php

namespace App\EventListener\Vehicle;

use App\Entity\Vehicle;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class VehicleEntityListener
{
    public function __construct(
        private VehicleService $vehicleService
    ) {
    }

    private function handleUpdateAt(Vehicle $vehicle, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('driver') && !$args->hasChangedField('updatedAt')) {
            $vehicle->setUpdatedAt(new \DateTime());
        }
    }

//    to avoid extra updates for now, see App\EventListener\Vehicle\VehicleListener:84
//    public function preUpdate(Vehicle $vehicle, PreUpdateEventArgs $args)
//    {
//        $this->handleUpdateAt($vehicle, $args);
//    }

    public function postLoad(Vehicle $vehicle, PostLoadEventArgs $args): Vehicle
    {
        $vehicle->setVehicleService($this->vehicleService);

        return $vehicle;
    }

    public function postPersist(Vehicle $vehicle, PostPersistEventArgs $args)
    {
        $vehicle->setVehicleService($this->vehicleService);

        return $vehicle;
    }
}