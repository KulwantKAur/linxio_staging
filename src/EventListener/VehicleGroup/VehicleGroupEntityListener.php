<?php

namespace App\EventListener\VehicleGroup;

use App\Entity\VehicleGroup;
use Doctrine\ORM\Event\LifecycleEventArgs;

class VehicleGroupEntityListener
{
    public function postLoad(VehicleGroup $vehicleGroup, LifecycleEventArgs $args)
    {
    }
}