<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\User;
use App\Entity\UserGroup;
use \Doctrine\ORM\EntityRepository;

/**
 * Class DocumentRepository
 * @package App\Repository
 */
class DocumentRepository extends EntityRepository
{
    /**
     * @param User $currentUser
     * @param string $status
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDocumentsCountWithStatus(User $currentUser, string $status)
    {
        $subQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('max(dr.id) as maxId')
            ->from(DocumentRecord::class, 'dr')
            ->andWhere('dr.status IN (:recordStatus)')
            ->groupBy('dr.document')
            ->getQuery();

        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(d) as count')
            ->from(Document::class, 'd')
            ->innerJoin(DocumentRecord::class, 'record', 'WITH', 'record.document = d')
            ->innerJoin('d.vehicle', 'v')
            ->andWhere('v.team = :team')
            ->setParameter('team', $currentUser->getTeam())
            ->andWhere('record.id IN (' . $subQuery->getDQL() . ')')
            ->andWhere('record.status = :status')
            ->setParameter('recordStatus', DocumentRecord::DASHBOARD_STATUSES)
            ->setParameter('status', $status);

        if ($currentUser->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($currentUser);
            $q->andWhere('v.id in (:vehicleIds)')->setParameter('vehicleIds', $userVehicles);
        }

        return $q->getQuery()->getSingleResult()['count'];
    }

    /**
     * @param User $currentUser
     * @param int $count
     * @return mixed
     */
    public function getLastDocuments(User $currentUser, $count = 3)
    {
        $subQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('max(dr.id) as maxId')
            ->from(DocumentRecord::class, 'dr')
            ->andWhere('dr.status IN (:recordStatus)')
            ->groupBy('dr.document')
            ->getQuery();

        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('d')
            ->from(Document::class, 'd')
            ->innerJoin(DocumentRecord::class, 'drecord', 'WITH', 'drecord.document = d')
            ->innerJoin('d.vehicle', 'v')
            ->andWhere('v.team = :team')
            ->setParameter('team', $currentUser->getTeam())
            ->andWhere('d.status IN (:status)')
            ->andWhere('drecord.id IN (' . $subQuery->getDQL() . ')')
            ->setParameter('status', Document::DASHBOARD_STATUSES)
            ->setParameter('recordStatus', DocumentRecord::DASHBOARD_STATUSES)
            ->setMaxResults($count)
            ->orderBy('drecord.expDate');

        if ($currentUser->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($currentUser);
            $q->andWhere('v.id in (:vehicleIds)')->setParameter('vehicleIds', $userVehicles);
        }

        return $q->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param User $currentUser
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDocumentById($id, User $currentUser)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('d')
            ->from(Document::class, 'd')
            ->andWhere('d.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        if ($currentUser->needToCheckUserGroup()) {
            $vehicleIds = $this->getEntityManager()->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($currentUser);
            $q->andWhere('IDENTITY(d.vehicle) IN (:vehicleIds)')->setParameter('vehicleIds', $vehicleIds);
        }

        return $q->getQuery()->getOneOrNullResult();
    }
}