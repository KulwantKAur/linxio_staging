<?php

namespace App\Repository;

use App\Entity\Sensor;
use App\Repository\Traits\FiltersTrait;

/**
 * SensorRepository
 */
class SensorRepository extends \Doctrine\ORM\EntityRepository
{
    use FiltersTrait;

    /**
     * @param string $sensorId
     * @return Sensor|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySensorId(string $sensorId)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(Sensor::class, 's')
            ->where('LOWER(s.sensorId) = :sensorId')
            ->setParameter('sensorId', strtolower($sensorId))
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param array $sensorIdData
     * @return Sensor|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByLowerSensorId(array $sensorIdData): ?Sensor
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from(Sensor::class, 's')
            ->where('LOWER(s.sensorId) = :sensorId')
            ->setParameter('sensorId', strtolower($sensorIdData['sensorId']))
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
