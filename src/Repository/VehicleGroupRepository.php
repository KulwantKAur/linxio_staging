<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use Doctrine\ORM\EntityRepository;

class VehicleGroupRepository extends EntityRepository
{
    /**
     * @param array $groupIds
     * @param Team $team
     * @return mixed
     */
    public function getVehiclesByGroups(array $groupIds, Team $team)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->innerJoin('v.groups', 'g')
            ->andWhere('g.id IN (:groupIds)')
            ->andWhere('v.team = :team')
            ->setParameter('groupIds', $groupIds)
            ->setParameter('team', $team->getId())
            ->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getVehicleGroupById(User $user, $id)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('vg')
            ->from(VehicleGroup::class, 'vg')
            ->andWhere('vg.id = :id')
            ->setParameter('id', $id);

        if ($user->needToCheckUserGroup()) {
            $userVehicleGroups = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehicleGroupsIdFromUserGroup($user);

            $q->andWhere('vg.id in (:vehicleGroupIds)')
                ->setParameter('vehicleGroupIds', $userVehicleGroups);
        }

        return $q->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    public function getVehicleByGroupIdsAndTeam(array $groupIds, Team $team)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->innerJoin('v.groups', 'g')
            ->andWhere('g.id IN (:groupIds)')
            ->andWhere('v.team = :team')
            ->setParameter('groupIds', $groupIds)
            ->setParameter('team', $team)
            ->getQuery()->getResult();
    }
}