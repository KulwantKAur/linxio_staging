<?php

namespace App\EventListener\Idling;

use App\Entity\Idling;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IdlingEntityListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Idling $idling
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Idling $idling, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('pointStart') || $event->hasChangedField('pointFinish')) {
            $this->updateDuration($idling);
        }
    }

    /**
     * @param Idling $idling
     * @return Idling
     */
    private function updateDuration(Idling $idling): Idling
    {
        if ($idling->getPointFinish() && $idling->getPointStart()) {
            $idling->setDuration($idling->getFinishedAt()->getTimestamp() - $idling->getStartedAt()->getTimestamp());
        }

        return $idling;
    }
}