<?php

namespace App\Repository;

use App\Entity\IntegrationScope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IntegrationScope|null find($id, $lockMode = null, $lockVersion = null)
 * @method IntegrationScope|null findOneBy(array $criteria, array $orderBy = null)
 * @method IntegrationScope[]    findAll()
 * @method IntegrationScope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IntegrationScopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntegrationScope::class);
    }
}
