<?php

namespace App\Repository;

use App\Entity\FuelStation;
use App\Entity\Team;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class FuelStationRepository extends EntityRepository
{
    public function getListByTeam(Team $team): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('fs')
            ->from(FuelStation::class, 'fs')
            ->where('fs.team = :team')
            ->setParameter('team', $team)
            ->getQuery()->getResult();
    }
}
