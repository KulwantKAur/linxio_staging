<?php

namespace App\Repository\Tracker;

use App\Entity\Tracker\TrackerPayloadStreamax;
use Doctrine\ORM\Query;

class TrackerPayloadStreamaxRepository extends \Doctrine\ORM\EntityRepository
{
    public function getByRangeQuery(
        ?string $startedAt,
        ?string $finishedAt
    ): Query {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('tps')
            ->from(TrackerPayloadStreamax::class, 'tps')
            ->where('tps.isProcessed = false');

        if ($startedAt) {
            $query->andWhere('tps.createdAt >= :startedAt')
                ->setParameter('startedAt', $startedAt);
        }
        if ($finishedAt) {
            $query->andWhere('tps.createdAt <= :finishedAt')
                ->setParameter('finishedAt', $finishedAt);
        }

        return $query->getQuery();
    }

    public function getByRangeCount(
        ?string $startedAt,
        ?string $finishedAt
    ): int {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(tps.id)')
            ->from(TrackerPayloadStreamax::class, 'tps')
            ->where('tps.isProcessed = false');

        if ($startedAt) {
            $query->andWhere('tps.createdAt >= :startedAt')
                ->setParameter('startedAt', $startedAt);
        }
        if ($finishedAt) {
            $query->andWhere('tps.createdAt <= :finishedAt')
                ->setParameter('finishedAt', $finishedAt);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
