<?php

namespace App\EventListener\FuelCard;

use App\Entity\FuelCard\FuelCard;
use App\Service\MapService\MapServiceResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;

class FuelCardEntityListener
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly MapServiceResolver $mapServiceResolver
    ) {
    }

    public function postLoad(FuelCard $fuelCard, PostLoadEventArgs $args)
    {
        $fuelCard->setEntityManager($this->em);
        $fuelCard->setMapService($this->mapServiceResolver->getInstance());

        return $fuelCard;
    }

    public function prePersist(FuelCard $fuelCard, PrePersistEventArgs $args)
    {
        $fuelCard->setEntityManager($this->em);

        return $fuelCard;
    }
}