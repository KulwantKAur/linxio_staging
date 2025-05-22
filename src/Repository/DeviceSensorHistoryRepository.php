<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\DeviceSensorHistory;
use App\Entity\Sensor;
use App\Repository\Traits\FiltersTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * DeviceSensorHistoryRepository
 */
class DeviceSensorHistoryRepository extends EntityRepository
{
    use FiltersTrait;

    /**
     * @param Sensor $sensor
     * @param Device $device
     * @return DeviceSensorHistory|null
     * @throws NonUniqueResultException
     */
    public function getLastBySensorAndDevice(Sensor $sensor, Device $device)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dsh')
            ->from(DeviceSensorHistory::class, 'dsh')
            ->where('dsh.device = :device')
            ->andWhere('dsh.sensor = :sensor')
            ->setParameter('device', $device)
            ->setParameter('sensor', $sensor)
            ->orderBy('dsh.installedAt', Criteria::DESC)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param Sensor $sensor
     * @param Device $device
     * @return DeviceSensorHistory|null
     * @throws NonUniqueResultException
     */
    public function getLastNotUninstalledBySensorAndDevice(Sensor $sensor, Device $device)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dsh')
            ->from(DeviceSensorHistory::class, 'dsh')
            ->where('dsh.device = :device')
            ->andWhere('dsh.sensor = :sensor')
            ->andWhere('dsh.uninstalledAt IS NULL')
            ->setParameter('device', $device)
            ->setParameter('sensor', $sensor)
            ->orderBy('dsh.installedAt', Criteria::DESC)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
