<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\DocumentRecord;
use \Doctrine\ORM\EntityRepository;

/**
 * Class DocumentRecordRepository
 * @package App\Repository
 */
class DocumentRecordRepository extends EntityRepository
{
    /**
     * @param bool $iterate
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult|mixed
     */
    public function getActiveDocumentRecords(bool $iterate = false)
    {
        $qb = $this->createQueryBuilder('dr');

        $q = $qb->select('dr')
            ->leftJoin('dr.document', 'd')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->in('dr.status', [DocumentRecord::STATUS_ACTIVE, DocumentRecord::STATUS_EXPIRE_SOON]),
                    $qb->expr()->isNotNull('dr.expDate')
                )
            )
            ->andWhere('d.status NOT IN (:statuses)')
            ->andWhere('dr.noExpiry = false')
            ->setParameter('statuses', [Document::STATUS_DELETED, Document::STATUS_DRAFT, Document::STATUS_ARCHIVE])
            ->getQuery();

        if ($iterate) {
            return $q->toIterable();
        }

        return $q->getResult();
    }
}
