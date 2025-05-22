<?php

namespace App\EventListener\Tracker;

use App\Entity\Tracker\TrackerHistoryLast;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TrackerHistoryLastEntityListener
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postLoad(TrackerHistoryLast $trackerHistoryLast, LifecycleEventArgs $args)
    {
        $trackerHistoryLast->setEntityManager($this->entityManager);

        return $trackerHistoryLast;
    }

    public function prePersist(TrackerHistoryLast $trackerHistoryLast, LifecycleEventArgs $args)
    {
        $trackerHistoryLast->setEntityManager($this->entityManager);

        return $trackerHistoryLast;
    }
}