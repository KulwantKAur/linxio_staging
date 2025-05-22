<?php

namespace App\Repository\Tracker;

use App\Entity\Device;
use App\Entity\Tracker\TrackerPayloadTemp;
use Doctrine\ORM\Query;

class TrackerPayloadTempRepository extends \Doctrine\ORM\EntityRepository
{
    public function getByDeviceAndRangeQuery(
        Device $device,
        ?string $startedAt,
        ?string $finishedAt
    ): Query {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('tpt')
            ->from(TrackerPayloadTemp::class, 'tpt')
            ->where('tpt.device = :device')
            ->andWhere('tpt.isProcessed = false')
            ->setParameter('device', $device);

        if ($startedAt) {
            $query->andWhere('tpt.createdAt >= :startedAt')
                ->setParameter('startedAt', $startedAt);
        }
        if ($finishedAt) {
            $query->andWhere('tpt.createdAt <= :finishedAt')
                ->setParameter('finishedAt', $finishedAt);
        }

        return $query->getQuery();
    }
}
