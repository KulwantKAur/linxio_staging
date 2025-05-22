<?php

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\AssetSensorHistory;
use App\Entity\DeviceSensorHistory;
use App\Entity\Sensor;
use App\Repository\Traits\FiltersTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * AssetSensorHistoryRepository
 */
class AssetSensorHistoryRepository extends EntityRepository
{
    use FiltersTrait;

    /**
     * @param Sensor $sensor
     * @param Asset $asset
     * @return DeviceSensorHistory|null
     * @throws NonUniqueResultException
     */
    public function getLastBySensorAndAsset(Sensor $sensor, Asset $asset)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ash')
            ->from(AssetSensorHistory::class, 'ash')
            ->where('ash.asset = :asset')
            ->andWhere('ash.sensor = :sensor')
            ->andWhere('ash.uninstalledAt IS NULL')
            ->setParameter('asset', $asset)
            ->setParameter('sensor', $sensor)
            ->orderBy('ash.installedAt', Criteria::DESC)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
