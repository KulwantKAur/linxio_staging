<?php

namespace App\Repository;

use App\Entity\AreaGroup;
use App\Entity\User;
use App\Entity\UserGroup;

/**
 * AreaGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AreaGroupRepository extends \Doctrine\ORM\EntityRepository
{
    public function getById($id, User $user)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ag')
            ->from(AreaGroup::class, 'ag')
            ->andWhere('ag.id = :id')
            ->setParameter('id', $id);

        if ($user->needToCheckUserGroup()) {
            $userAreas = $this->getEntityManager()->getRepository(UserGroup::class)->getUserAreaGroupsIdFromUserGroup($user);
            $qb->andWhere('ag.id in (:areaIds)')->setParameter('areaIds', $userAreas);
        }

        if ($user->isInClientTeam()) {
            $qb->andWhere('ag.team = :team')->setParameter('team', $user->getTeam());
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
