<?php

namespace App\Repository;

use App\Entity\BillingEntityHistory;
use App\Entity\Device;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class BillingEntityHistoryRepository extends EntityRepository
{
    public function getLastRecord(int $id, string $entity, string $type, $isNullDateTo = null)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('b')
            ->from(BillingEntityHistory::class, 'b')
            ->andWhere('b.entityId = :id')
            ->setParameter('id', $id)
            ->andWhere('b.entity = :entity')
            ->setParameter('entity', $entity)
            ->andWhere('b.type = :type')
            ->setParameter('type', $type);

        if (!is_null($isNullDateTo)) {
            if ($isNullDateTo) {
                $q->andWhere('b.dateTo IS NULL');
            } else {
                $q->andWhere('b.dateTo IS NOT NULL');
            }
        }

        return $q->orderBy('b.id', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @param string $entity
     * @param string $type
     * @param \DateTimeInterface $dateFrom
     * @param null $isNullDateTo
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRecordByDate(
        int $id,
        string $entity,
        string $type,
        \DateTimeInterface $dateFrom,
        $isNullDateTo = null
    ) {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('b')
            ->from(BillingEntityHistory::class, 'b')
            ->andWhere('b.entityId = :id')
            ->setParameter('id', $id)
            ->andWhere('b.entity = :entity')
            ->setParameter('entity', $entity)
            ->andWhere('b.type = :type')
            ->setParameter('type', $type)
            ->andWhere('b.dateFrom <= :dateFrom')
            ->setParameter('dateFrom', $dateFrom);

        if (!is_null($isNullDateTo)) {
            if ($isNullDateTo) {
                $q->andWhere('b.dateTo IS NULL');
            } else {
                $q->andWhere('b.dateTo IS NOT NULL');
            }
        }

        return $q->orderBy('b.id', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getDeviceTeamHistory(Device $device): Query
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('beh')
            ->from(BillingEntityHistory::class, 'beh')
            ->andWhere('beh.entity = :entity')
            ->setParameter('entity', BillingEntityHistory::ENTITY_DEVICE)
            ->andWhere('beh.entityId = :entityId')
            ->setParameter('entityId', $device->getId())
            ->andWhere('beh.type = :type')
            ->setParameter('type', BillingEntityHistory::TYPE_CHANGE_TEAM)
            ->orderBy('beh.id', 'DESC')
            ->getQuery();
    }
}
