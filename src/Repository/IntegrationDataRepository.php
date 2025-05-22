<?php

namespace App\Repository;

use App\Entity\Integration;
use App\Entity\IntegrationData;

class IntegrationDataRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByTeamIdAndIntegration($teamId, Integration $integration): ?IntegrationData
    {
        return $this->createQueryBuilder('id')
            ->andWhere('IDENTITY(id.team) = :teamId')
            ->andWhere('id.integration = :integration')
            ->setParameter('teamId', $teamId)
            ->setParameter('integration', $integration)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByTeamIdAndIntegrationId($teamId, $integrationId): ?IntegrationData
    {
        return $this->createQueryBuilder('id')
            ->andWhere('IDENTITY(id.team) = :teamId')
            ->andWhere('IDENTITY(id.integration) = :integrationId')
            ->setParameter('teamId', $teamId)
            ->setParameter('integrationId', $integrationId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
