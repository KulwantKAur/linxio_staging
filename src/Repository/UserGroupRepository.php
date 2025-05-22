<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\AreaGroup;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Util\Doctrine\DoctrineHelper;
use Doctrine\ORM\EntityRepository;

class UserGroupRepository extends EntityRepository
{
    public static $cache = [];

    /**
     * @param User $user
     * @return mixed[]
     */
    public function getUserVehiclesIdFromUserGroup(User $user)
    {
        $cacheKey = __METHOD__ . $user->getId();
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $em = $this->getEntityManager();

        $vTable = $em->getClassMetadata(Vehicle::class)->getTableName();
        $uTable = $em->getClassMetadata(User::class)->getTableName();
        $ugTable = $em->getClassMetadata(UserGroup::class)->getTableName();

        if ($user->allVehiclesAccess()) {
            $teamIds = null;
            if ($user->isInClientTeam()) {
                $teamIds = [$user->getTeam()->getId()];
            }
            if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
                $teamIds = $user->getManagedTeamsIds();
            }

            $vehiclesQuery = $em->getConnection()->createQueryBuilder()->select('v.id')->from($vTable, 'v');

            if ($teamIds) {
                $vehiclesQuery->andWhere('v.team_id IN (:teamIds)')
                    ->setParameter('teamIds', implode(', ', $teamIds));
            }

            return array_column($vehiclesQuery->execute()->fetchAllAssociative(), 'id');
        }

        $vehiclesQuery = $em->getConnection()->createQueryBuilder()
            ->select('v.id')
            ->from($vTable, 'v')
            ->join('v', 'users_vehicles', 'uvs', 'v.id = uvs.vehicle_id')
            ->join('uvs', $ugTable, 'ug', 'uvs.user_group_id = ug.id')
            ->join('ug', 'users_groups', 'ugs', 'ug.id = ugs.user_group_id')
            ->andWhere('ugs.user_id = :userId');

        $vehicleGroupVehiclesQuery = $em->getConnection()->createQueryBuilder()
            ->select('vgs.vehicle_id')
            ->from($uTable, 'u')
            ->join('u', 'users_groups', 'ugs', 'u.id = ugs.user_id')
            ->join('ugs', $ugTable, 'ug', 'ugs.user_group_id = ug.id')
            ->join('ug', 'users_vehicles_groups', 'uvg', 'ug.id = uvg.user_group_id')
            ->join('uvg', 'vehicles_groups', 'vgs', 'uvg.vehicle_group_id = vgs.vehicle_group_id')
            ->andWhere('ugs.user_id = :userId');

        $depotVehiclesQuery = $em->getConnection()->createQueryBuilder()
            ->select('v.id')
            ->from($uTable, 'u')
            ->join('u', 'users_groups', 'ugs', 'u.id = ugs.user_id')
            ->join('ugs', $ugTable, 'ug', 'ugs.user_group_id = ug.id')
            ->join('ug', 'users_vehicles_depots', 'uvd', 'ug.id = uvd.user_group_id')
            ->join('uvd', $vTable, 'v', 'v.depot_id = uvd.depot_id')
            ->andWhere('ugs.user_id = :userId');

        $unionQuery = $em->getConnection()->createQueryBuilder()
            ->select('DISTINCT(vehicles.id) as id')
            ->from(
                '(' . DoctrineHelper::unionQueryBuilders(
                    [$vehiclesQuery, $vehicleGroupVehiclesQuery, $depotVehiclesQuery]
                ) . ') as',
                'vehicles'
            )
            ->setParameter('userId', $user->getId());

        $result = array_column($unionQuery->execute()->fetchAll(), 'id');
        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * @param User $user
     * @return mixed[]
     */
    public function getUserVehicleGroupsIdFromUserGroup(User $user)
    {
        $em = $this->getEntityManager();

        $uTable = $em->getClassMetadata(User::class)->getTableName();
        $ugTable = $em->getClassMetadata(UserGroup::class)->getTableName();
        $vgTable = $em->getClassMetadata(VehicleGroup::class)->getTableName();

        if (!$user->needToCheckUserGroup()) {
            $teamIds = null;
            if ($user->isInClientTeam()) {
                $teamIds = [$user->getTeam()->getId()];
            }
            if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
                $teamIds = $user->getManagedTeamsIds();
            }

            $vehicleGroupQuery = $em->getConnection()->createQueryBuilder()
                ->select('vg.id')->from($vgTable, 'vg');

            if ($teamIds) {
                $vehicleGroupQuery->andWhere('vg.team_id IN (:teamIds)')
                    ->setParameter('teamIds', implode(', ', $teamIds));
            }

            return array_column($vehicleGroupQuery->execute()->fetchAll(), 'id');
        }

        $vehicleGroupQuery = $em->getConnection()->createQueryBuilder()
            ->select('DISTINCT(uvg.vehicle_group_id) as id')
            ->from($uTable, 'u')
            ->join('u', 'users_groups', 'ugs', 'u.id = ugs.user_id')
            ->join('ugs', $ugTable, 'ug', 'ugs.user_group_id = ug.id')
            ->join('ug', 'users_vehicles_groups', 'uvg', 'ug.id = uvg.user_group_id')
            ->andWhere('ugs.user_id = :userId')
            ->setParameter('userId', $user->getId());

        return array_column($vehicleGroupQuery->execute()->fetchAll(), 'id');
    }

    /**
     * @param User $user
     * @return mixed[]
     */
    public function getUserDepotsIdFromUserGroup(User $user)
    {
        $em = $this->getEntityManager();

        $uTable = $em->getClassMetadata(User::class)->getTableName();
        $ugTable = $em->getClassMetadata(UserGroup::class)->getTableName();

        $depotsQuery = $em->getConnection()->createQueryBuilder()
            ->select('DISTINCT(uvd.depot_id) as id')
            ->from($uTable, 'u')
            ->join('u', 'users_groups', 'ugs', 'u.id = ugs.user_id')
            ->join('ugs', $ugTable, 'ug', 'ugs.user_group_id = ug.id')
            ->join('ug', 'users_vehicles_depots', 'uvd', 'ug.id = uvd.user_group_id')
            ->andWhere('ugs.user_id = :userId')
            ->setParameter('userId', $user->getId());

        return array_column($depotsQuery->execute()->fetchAll(), 'id');
    }

    public function getUserAreasIdFromUserGroup(User $user)
    {
        $cacheKey = __METHOD__ . $user->getId();
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $em = $this->getEntityManager();

        $aTable = $em->getClassMetadata(Area::class)->getTableName();
        $uTable = $em->getClassMetadata(User::class)->getTableName();
        $ugTable = $em->getClassMetadata(UserGroup::class)->getTableName();

        if ($user->allAreasAccess()) {
            $teamIds = null;
            if ($user->isInClientTeam()) {
                $teamIds = [$user->getTeam()->getId()];
            }
            if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
                $teamIds = $user->getManagedTeamsIds();
            }

            $vehiclesQuery = $em->getConnection()->createQueryBuilder()->select('a.id')->from($aTable, 'a');

            if ($teamIds) {
                $vehiclesQuery->andWhere('a.team_id IN (:teamIds)')
                    ->setParameter('teamIds', implode(', ', $teamIds));
            }

            return array_column($vehiclesQuery->execute()->fetchAllAssociative(), 'id');
        }

        $areasQuery = $em->getConnection()->createQueryBuilder()
            ->select('a.id')
            ->from($aTable, 'a')
            ->join('a', 'users_areas', 'uas', 'a.id = uas.area_id')
            ->join('uas', $ugTable, 'ug', 'uas.user_group_id = ug.id')
            ->join('ug', 'users_groups', 'ugs', 'ug.id = ugs.user_group_id')
            ->andWhere('ugs.user_id = :userId');

        $areaGroupVehiclesQuery = $em->getConnection()->createQueryBuilder()
            ->select('ags.area_id')
            ->from($uTable, 'u')
            ->join('u', 'users_groups', 'ugs', 'u.id = ugs.user_id')
            ->join('ugs', $ugTable, 'ug', 'ugs.user_group_id = ug.id')
            ->join('ug', 'users_area_groups', 'uag', 'ug.id = uag.user_group_id')
            ->join('uag', 'areas_groups', 'ags', 'uag.area_group_id = ags.area_group_id')
            ->andWhere('ugs.user_id = :userId');

        $unionQuery = $em->getConnection()->createQueryBuilder()
            ->select('DISTINCT(areas.id) as id')
            ->from(
                '(' . DoctrineHelper::unionQueryBuilders(
                    [$areasQuery, $areaGroupVehiclesQuery]
                ) . ') as',
                'areas'
            )
            ->setParameter('userId', $user->getId());

        $result = array_column($unionQuery->execute()->fetchAll(), 'id');
        self::$cache[$cacheKey] = $result;

        return $result;
    }

    public function getUserAreaGroupsIdFromUserGroup(User $user)
    {
        $em = $this->getEntityManager();

        $uTable = $em->getClassMetadata(User::class)->getTableName();
        $ugTable = $em->getClassMetadata(UserGroup::class)->getTableName();
        $agTable = $em->getClassMetadata(AreaGroup::class)->getTableName();

        if ($user->hasAreaGroupScope()) {
            $areaGroupQuery = $em->getConnection()->createQueryBuilder()
                ->select('DISTINCT(uag.area_group_id) as id')
                ->from($uTable, 'u')
                ->join('u', 'users_groups', 'ugs', 'u.id = ugs.user_id')
                ->join('ugs', $ugTable, 'ug', 'ugs.user_group_id = ug.id')
                ->join('ug', 'users_area_groups', 'uag', 'ug.id = uag.user_group_id')
                ->andWhere('ugs.user_id = :userId')
                ->setParameter('userId', $user->getId());

            return array_column($areaGroupQuery->execute()->fetchAllAssociative(), 'id');
        }

        $teamIds = null;
        if ($user->isInClientTeam()) {
            $teamIds = [$user->getTeam()->getId()];
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $teamIds = $user->getManagedTeamsIds();
        }

        $areaGroupQuery = $em->getConnection()->createQueryBuilder()->select('ag.id')->from($agTable, 'ag');

        if ($teamIds) {
            $areaGroupQuery->andWhere('ag.team_id IN (:teamIds)')
                ->setParameter('teamIds', implode(', ', $teamIds));
        }

        return array_column($areaGroupQuery->execute()->fetchAllAssociative(), 'id');

    }
}