<?php

namespace App\EventListener\Tracker;

use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerHistoryIOLast;
use Doctrine\ORM\EntityManager;

class TrackerHistoryIOEntityListener
{
    private $em;

    /**
     * @param TrackerHistoryIO $trackerHistoryIO
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    private function generateTrackerHistoryIOLast(TrackerHistoryIO $trackerHistoryIO)
    {
        $THIOLast = $this->em->getRepository(TrackerHistoryIOLast::class)
            ->getLastRecordByDeviceIdAndType($trackerHistoryIO->getDeviceId(), $trackerHistoryIO->getType());

        if ($THIOLast) {
            if ($trackerHistoryIO->getLastOccurredAt() <= $THIOLast->getOccurredAt()) {
                return;
            }
        } else {
            $THIOLast = new TrackerHistoryIOLast();
        }

        $THIOLast->fromTrackerHistoryIO($trackerHistoryIO);
        $this->em->persist($THIOLast);
        $this->em->flush();
    }

    /**
     * VehicleListener constructor.
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    /**
     * @param TrackerHistoryIO $trackerHistoryIO
     */
    public function postPersist(TrackerHistoryIO $trackerHistoryIO)
    {
        $this->generateTrackerHistoryIOLast($trackerHistoryIO);
    }

    /**
     * @param TrackerHistoryIO $trackerHistoryIO
     */
    public function postUpdate(TrackerHistoryIO $trackerHistoryIO)
    {
        $this->generateTrackerHistoryIOLast($trackerHistoryIO);
    }
}