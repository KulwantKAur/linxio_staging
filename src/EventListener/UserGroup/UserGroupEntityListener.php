<?php

namespace App\EventListener\UserGroup;

use App\Entity\VehicleGroup;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserGroupEntityListener
{
    public function postLoad(VehicleGroup $vehicleGroup, LifecycleEventArgs $args)
    {
    }
}