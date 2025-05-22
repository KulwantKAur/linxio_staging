<?php

namespace App\Repository\Tracker;

use App\Entity\Device;
use App\Entity\Sensor;
use App\Entity\Tracker\TrackerHistorySensor;
use Doctrine\Common\Collections\Criteria;

/**
 * TrackerHistorySensorRepository
 */
class TrackerHistorySensorRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Device $device
     * @param $occurredAt
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function recordExistsForDevice(Device $device, $occurredAt)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(ths)')
            ->from(TrackerHistorySensor::class, 'ths')
            ->where('ths.occurredAt = :occurredAt')
            ->andWhere('ths.device = :device')
            ->setParameter('occurredAt', $occurredAt)
            ->setParameter('device', $device)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $deviceSensorId
     * @param string $sort
     * @param string $order
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return \Doctrine\ORM\Query
     */
    public function getByDeviceSensorIdQuery(
        int $deviceSensorId,
        string $sort,
        string $order,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ) {
        switch ($sort) {
            case 'deviceId':
                $sort = 'device';
                break;
            case 'vehicleId':
                $sort = 'vehicle';
                break;
            case 'deviceSensorId':
                $sort = 'deviceSensor';
                break;
            default:
                break;
        }

        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $qb->select('ths')
            ->from(TrackerHistorySensor::class, 'ths')
            ->where('ths.deviceSensor = :deviceSensorId')
            ->andWhere($qb->expr()->gte('ths.occurredAt', ':startDate'))
            ->andWhere($qb->expr()->lte('ths.occurredAt', ':endDate'))
            ->andWhere($qb->expr()->eq('ths.isNullableData', 'false'))
            ->orderBy('ths.' . $sort, $order)
            ->setParameter('deviceSensorId', $deviceSensorId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery();
    }

    public function getPrevTrackerHistorySensor(
        TrackerHistorySensor $trackerHistorySensor,
        $withoutNull = true
    ): ?TrackerHistorySensor {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ths')
            ->from(TrackerHistorySensor::class, 'ths')
            ->where('ths.occurredAt < :occurredAt')
            ->andWhere('ths.device = :device')
            ->andWhere('ths.deviceSensor = :deviceSensor');

        if ($withoutNull) {
            $q->andWhere('ths.isNullableData = false');
        }

        return $q->setParameter('occurredAt', $trackerHistorySensor->getOccurredAt())
            ->setParameter('device', $trackerHistorySensor->getDevice())
            ->setParameter('deviceSensor', $trackerHistorySensor->getDeviceSensor())
            ->orderBy('ths.occurredAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param Sensor $sensor
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function getTrackerHistoriesSensorBySensor(
        Sensor $sensor,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ) {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ths')
            ->from(TrackerHistorySensor::class, 'ths')
            ->leftJoin('ths.deviceSensor', 'ds')
            ->where('ds.sensor = :sensor')
            ->andWhere('ths.occurredAt >= :dateFrom')
            ->andWhere('ths.occurredAt <= :dateTo')
            ->setParameter('sensor', $sensor)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->orderBy('ths.occurredAt', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }
}
