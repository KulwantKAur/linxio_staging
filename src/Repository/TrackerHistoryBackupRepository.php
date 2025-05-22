<?php

namespace App\Repository;

use App\Entity\TrackerHistoryBackup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class TrackerHistoryBackupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrackerHistoryBackup::class);
    }
    public function findByDateRange(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.ts >= :start')
            ->andWhere('t.ts <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.ts', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    
}
