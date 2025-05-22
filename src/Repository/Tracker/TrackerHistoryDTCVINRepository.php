<?php

namespace App\Repository\Tracker;

use App\Entity\Tracker\TrackerHistoryDTCVIN;
use App\Entity\Vehicle;
use Doctrine\Common\Collections\Criteria;

/**
 * TrackerHistoryDTCVINRepository
 */
class TrackerHistoryDTCVINRepository extends \Doctrine\ORM\EntityRepository
{
    public function getTrackerHistoryDtcByVehicleAndDate(Vehicle $vehicle, \DateTime $from, \DateTime $to)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('th')
            ->from(TrackerHistoryDTCVIN::class, 'th')
            ->andWhere('th.vehicle = :vehicle')
            ->andWhere('th.occurredAt > :from')
            ->andWhere('th.occurredAt < :to')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('th.occurredAt', Criteria::DESC)
            ->getQuery()
            ->getResult();
    }
}
