<?php


namespace App\Repository\Traits;


use App\Entity\AreaGroup;
use App\Entity\VehicleGroup;
use Doctrine\DBAL\Connection;

trait FiltersTrait
{
    /**
     * @param $query
     * @param $depotId
     * @param $noDepot
     * @return mixed
     */
    protected function addDepotIdFilter($query, $depotId, $noDepot)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if ($depotId && $noDepot) {
            $query->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('depot_id'),
                    $qb->expr()->in('depot_id', $depotId)
                )
            );
        } else {
            if ($noDepot) {
                $query->andWhere('depot_id IS NULL');
            }

            if ($depotId) {
                $query->andWhere('depot_id IN (' . $depotId . ')');
            }
        }

        return $query;
    }

    /**
     * @param $query
     * @param $vehicleGroup
     * @param $noGroups
     * @return mixed
     */
    protected function addGroupsFilter($query, $vehicleGroup, $noGroups)
    {
        $em = $this->getEntityManager();
        $vgTable = $em->getClassMetadata(VehicleGroup::class)->getTableName();

        if ($noGroups && $vehicleGroup) {
            $groupsFilterSubQuery = $em->getConnection()->createQueryBuilder()
                ->select('v.id')
                ->from('vehicle', 'v')
                ->leftJoin('v', 'vehicles_groups', 'vgs', 'vgs.vehicle_id = v.id')
                ->orWhere('vgs.vehicle_group_id IN (' . $vehicleGroup . ')')
                ->orWhere('vgs.vehicle_group_id is null');

            $query->rightJoin(
                'v',
                sprintf('(%s)', $groupsFilterSubQuery->getSQL()),
                'vgsf',
                'vgsf.id = v.id'
            );
        } else {
            if ($noGroups) {
                $query->andWhere('groups IS NULL');
            }

            if ($vehicleGroup) {
                $groupsFilterSubQuery = $em->getConnection()->createQueryBuilder()
                    ->select('vgs.vehicle_id')
                    ->from('vehicles_groups', 'vgs')
                    ->leftJoin('vgs', $vgTable, 'vg', 'vg.id =vgs.vehicle_group_id')
                    ->groupBy('vgs.vehicle_id')
                    ->andWhere('vg.id IN (' . $vehicleGroup . ')');

                $query->rightJoin(
                    'v',
                    sprintf('(%s)', $groupsFilterSubQuery->getSQL()),
                    'vgsf',
                    'vgsf.vehicle_id = v.id'
                );
            }
        }

        return $query;
    }

    protected function addAreaGroupsFilter(
        $query,
        $areaGroup,
        $noAreaGroup,
        $areaGroupTableNameFroIn = 'ag',
        $areaGroupTable = 'ag'
    ) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if ($areaGroup && $noAreaGroup) {
            $query->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull($areaGroupTable),
                    $qb->expr()->in($areaGroupTableNameFroIn . '.id', $areaGroup)
                )
            );
        } else {
            if ($noAreaGroup) {
                $query->andWhere($areaGroupTable . ' IS NULL');
            }

            if ($areaGroup) {
                $query->andWhere($areaGroupTableNameFroIn . '.id IN (:areaGroup)')
                    ->setParameter('areaGroup', $areaGroup, Connection::PARAM_INT_ARRAY);
            }
        }

        return $query;
    }

    protected function addAreaFilter($query, $area, $noArea, $areaTableName = 'a')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        if ($area && $noArea) {
            $query->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull($areaTableName . '.id'),
                    $qb->expr()->in($areaTableName . '.id', $area)
                )
            );
        } else {
            if ($noArea) {
                $query->andWhere($areaTableName . '.id IS NULL');
            }

            if ($area) {
                $query->andWhere($areaTableName . '.id IN (:area)')
                    ->setParameter('area', $area, Connection::PARAM_INT_ARRAY);
            }
        }

        return $query;
    }
}