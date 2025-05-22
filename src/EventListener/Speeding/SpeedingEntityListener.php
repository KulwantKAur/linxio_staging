<?php

namespace App\EventListener\Speeding;

use App\Entity\Speeding;
use App\Entity\Tracker\TrackerHistory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SpeedingEntityListener
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Speeding $speeding
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Speeding $speeding, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('pointStart') || $event->hasChangedField('pointFinish')) {
            $this->updateDuration($speeding);
            $this->updateDistanceStats($speeding);
            $this->updateSpeedStats($speeding, $event->getEntityManager());
        }
    }

    /**
     * @param Speeding $speeding
     * @param EntityManager $em
     * @return Speeding
     */
    private function updateSpeedStats(Speeding $speeding, EntityManager $em): Speeding
    {
        $speedingPoints = $em->getRepository(TrackerHistory::class)->getCoordinatesForSpeedingStats($speeding);
        $totalSpeed = $maxSpeed = 0;

        foreach ($speedingPoints as $point) {
            $totalSpeed += $point['speed'];

            if ($point['speed'] > $maxSpeed) {
                $speeding->setMaxSpeed($point['speed']);
            }

            $maxSpeed = $point['speed'];
        }

        if ($speedingPoints) {
            $routePointsCount = count($speedingPoints);
            $speeding->setAvgSpeed($totalSpeed / $routePointsCount);
        }

        return $speeding;
    }

    /**
     * @param Speeding $speeding
     * @return Speeding
     */
    private function updateDuration(Speeding $speeding): Speeding
    {
        if ($speeding->getPointFinish() && $speeding->getPointStart()) {
            $speeding->setDuration($speeding->getFinishedAt()->getTimestamp()
                - $speeding->getStartedAt()->getTimestamp()
            );
        }

        return $speeding;
    }

    /**
     * @param Speeding $speeding
     * @return Speeding
     */
    private function updateDistanceStats(Speeding $speeding): Speeding
    {
        if (($speeding->getPointFinish() && $speeding->getPointFinish()->getOdometer())
            && ($speeding->getPointStart() && $speeding->getPointStart()->getOdometer()))
        {
            $speeding->setDistance(
                $speeding->getPointFinish()->getOdometer() - $speeding->getPointStart()->getOdometer()
            );
        }

        return $speeding;
    }
}