<?php

namespace App\EventListener\Depot;

use App\Entity\Depot;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DepotEntityListener
{
    public function postLoad(Depot $depot, LifecycleEventArgs $args)
    {
    }
}