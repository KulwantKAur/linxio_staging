<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\AreaHistory;
use App\Entity\Depot;
use App\Entity\DriverHistory;
use App\Entity\Idling;
use App\Entity\Route;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Util\StringHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class AreaHistoryRepository extends EntityRepository
{
    /**
     * @param Vehicle $vehicle
     * @param string $dateTime
     * @return mixed
     */
    public function findArrivedAreaHistory(Vehicle $vehicle, string $dateTime)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ah')
            ->from(AreaHistory::class, 'ah')
            ->join('ah.area', 'a')
            ->andWhere("ah.vehicle = :vehicle")
            ->setParameter('vehicle', $vehicle)
            ->andWhere("ah.arrived <= :dateTime")
            ->setParameter('dateTime', $dateTime)
            ->andWhere("ah.departed IS NULL");

        return $query->getQuery()->getResult();
    }

    /**
     * @param Vehicle $vehicle
     * @param string $dateTime
     * @return mixed
     */
    public function findAreaHistoryByVehicleAndDate(Vehicle $vehicle, string $dateTime, $areaId)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a.id')
            ->from(AreaHistory::class, 'ah')
            ->join('ah.area', 'a')
            ->andWhere("ah.vehicle = :vehicle")
            ->setParameter('vehicle', $vehicle)
            ->andWhere("ah.arrived <= :dateTime")
            ->orWhere("ah.arrived >= :dateTime")
            ->setParameter('dateTime', $dateTime)
            ->andWhere('a.id = :areaId')
            ->setParameter('areaId', $areaId);

        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->isNull('ah.departed'),
                $query->expr()->gte('ah.departed', ':dateTime')
            ));

        return $query->getQuery()->getResult();
    }

    /**
     * @param $data
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insert($data)
    {
        return $this->getEntityManager()->getConnection()->insert('area_history', $data);
    }

    /**
     * @param $id
     * @param $dateTime
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setDeparted($id, $dateTime)
    {
        return $this->getEntityManager()->getConnection()
            ->executeStatement("UPDATE area_history SET departed = ? WHERE area_history.area_id = ?", [$dateTime, $id]);
    }

    /**
     * @param Area $area
     * @return mixed
     * @throws \Exception
     */
    public function setDepartedByArea(Area $area)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $q = $qb->update(AreaHistory::class, 'ah')
            ->set('ah.departed', ':departed')
            ->andWhere('ah.area = :area')
            ->andWhere('ah.departed IS NULL')
            ->setParameter('departed', new \DateTime())
            ->setParameter('area', $area)
            ->getQuery();

        return $q->execute();
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $vehicleId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getVisitedAreas(array $params, User $user, $vehicleId = false)
    {
        $areaTable = $this->getEntityManager()->getClassMetadata(Area::class)->getTableName();
        $areaHistoryTable = $this->getEntityManager()->getClassMetadata(AreaHistory::class)->getTableName();
        $idlingTable = $this->getEntityManager()->getClassMetadata(Idling::class)->getTableName();
        $routeTable = $this->getEntityManager()->getClassMetadata(Route::class)->getTableName();
        $depotTable = $this->getEntityManager()->getClassMetadata(Depot::class)->getTableName();
        $vTable = $this->getEntityManager()->getClassMetadata(Vehicle::class)->getTableName();
        $dhTable = $this->getEntityManager()->getClassMetadata(DriverHistory::class)->getTableName();
        $uTable = $this->getEntityManager()->getClassMetadata(User::class)->getTableName();

        $connection = $this->getEntityManager()->getConnection();

        $teamQuery = $connection->createQueryBuilder()->select('unnest(:_team_ids::INTEGER[]) AS id');
        $qb = $connection->createQueryBuilder();

        $areaQuery = $qb->select(
            [
                'a.id',
                'a.name',
                'string_agg(ag.name, \', \') AS ag_name',
                'a.team_id',
                'a.status'
            ]
        )
            ->from('area', 'a')
            ->innerJoin('a', sprintf('(%s)', $teamQuery->getSQL()), 't', 't.id=a.team_id')
            ->leftJoin('a', 'areas_groups', 'ags', 'a.id = ags.area_id')
            ->leftJoin('ags', 'area_group', 'ag', 'ags.area_group_id = ag.id')
            ->andWhere($qb->expr()->eq('a.status', ':status'))
            ->groupBy('a.id')
            ->setParameter('_team_ids', $this->formatPostgresArrayString($params['teamId']))
            ->setParameter('status', Area::STATUS_ACTIVE);

        if (isset($params['ag_name'])) {
            $areaQuery->andWhere('ag.id IN (:ag_name)');
        }

        $groupQuery = $connection->createQueryBuilder();
        $groupQuery->select(
            [
                'v.id as vehicle_id',
                'string_agg(vg.name, \', \') as vehicle_group_names_as_string',
            ]
        )
            ->from($vTable, 'v')
            ->innerJoin('v',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getAssociationMapping('vehicles')['joinTable']['name'],
                'vgs', 'v.id = vgs.vehicle_id')
            ->innerJoin('vgs', $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getTableName(), 'vg',
                'vg.id = vgs.vehicle_group_id')
            ->andWhere('v.id IN (:vehicleIds)')
            ->groupBy('v.id');

        $routeQuery = $connection->createQueryBuilder();
        $routeQuery->select(
            [
                'v.id AS vehicle_id',
                'ah.id AS area_history_id',
                'SUM(EXTRACT(EPOCH FROM (r.finished_at - r.started_at))) AS parking_time',
            ]
        )
            ->from($routeTable, 'r')
            ->innerJoin('r', $vTable, 'v', 'r.vehicle_id = v.id')
            ->innerJoin('v', $areaHistoryTable, 'ah', 'ah.vehicle_id = v.id')
            ->innerJoin('ah', $areaTable, 'a', 'ah.area_id = a.id')
            ->innerJoin('a', sprintf('(%s)', $teamQuery->getSQL()), 't', 't.id=a.team_id')
            ->andWhere(
                $routeQuery->expr()->and(
                    $routeQuery->expr()->eq('r.type', ':routeType'),
                    $routeQuery->expr()->lte('r.started_at', 'ah.departed'),
                    $routeQuery->expr()->gte('r.finished_at', 'ah.arrived')
                )
            )
            ->andWhere(
                $routeQuery->expr()->and(
                    $routeQuery->expr()->gte('ah.departed', ':dateFrom'),
                    $routeQuery->expr()->lte('ah.arrived', ':dateTo')
                )
            )
            ->andWhere($routeQuery->expr()->eq('a.status', ':status'))
            ->andWhere('v.id IN (:vehicleIds)')
            ->groupBy(['ah.id', 'v.id']);

        $driverQuery = $connection->createQueryBuilder()->select(
            [
                'string_agg(DISTINCT CASE WHEN u.name IS NULL THEN null ELSE CONCAT(u.name, \' \', u.surname) END, \', \') as driver_name',
                'dh.vehicle_id'
            ]
        )
            ->from($uTable, 'u')
            ->leftJoin('u', $dhTable, 'dh', 'u.id = dh.driver_id')
            ->andWhere(
                $routeQuery->expr()->and(
                    $routeQuery->expr()->lte('dh.startdate', ':dateTo'),
                    $routeQuery->expr()->or(
                        $routeQuery->expr()->gte('dh.finishdate', ':dateFrom'),
                        $routeQuery->expr()->isNull('dh.finishdate'),
                    )
                )
            )
            ->andWhere('dh.vehicle_id IN (:vehicleIds)')
            ->groupBy(['dh.vehicle_id']);

        $idlingQuery = $connection->createQueryBuilder();
        $idlingQuery->select(
            [
                'v.id AS vehicle_id',
                'ah.id AS area_history_id',
                'SUM(EXTRACT(EPOCH FROM (i.finished_at - i.started_at))) AS idling_time',
            ]
        )
            ->from($idlingTable, 'i')
            ->innerJoin('i', $vTable, 'v', 'i.vehicle_id = v.id')
            ->innerJoin('v', $areaHistoryTable, 'ah', 'ah.vehicle_id = v.id')
            ->innerJoin('ah', $areaTable, 'a', 'ah.area_id = a.id')
            ->innerJoin('a', sprintf('(%s)', $teamQuery->getSQL()), 't', 't.id=a.team_id')
            ->andWhere(
                $routeQuery->expr()->and(
                    $routeQuery->expr()->lte('i.started_at', 'ah.departed'),
                    $routeQuery->expr()->gte('i.finished_at', 'ah.arrived')
                )
            )
            ->andWhere(
                $routeQuery->expr()->and(
                    $routeQuery->expr()->gte('ah.departed', ':dateFrom'),
                    $routeQuery->expr()->lte('ah.arrived', ':dateTo')
                )
            )
            ->andWhere($idlingQuery->expr()->eq('a.status', ':status'))
            ->andWhere('v.id IN (:vehicleIds)')
            ->groupBy(['ah.id', 'v.id']);

        $subQb = $connection->createQueryBuilder();
        $subQb->select(
            [
                'a.id',
                'v.model',
                'v.id as vehicle_id',
                'v.defaultlabel as default_label',
                'v.regno as reg_no',
                'vd.name AS depot_name',
                'ah.arrived',
                'ah.departed',
                'a.name',
                'vg.vehicle_group_names_as_string',
                '\'\'::VARCHAR AS odometer',
                'd.driver_name AS driver',
                'r.parking_time::BIGINT AS parking_time',
                'i.idling_time::BIGINT AS idling_time',
                'a.ag_name'
            ]
        )
            ->from($areaHistoryTable, 'ah')
            ->innerJoin('ah', $vTable, 'v', 'ah.vehicle_id = v.id')
            ->innerJoin('ah', sprintf('(%s)', $areaQuery->getSQL()), 'a', 'ah.area_id=a.id')
            ->innerJoin('a', sprintf('(%s)', $teamQuery->getSQL()), 't', 't.id=a.team_id')
            ->leftJoin('v', $depotTable, 'vd', 'v.depot_id = vd.id')
            ->leftJoin('v', sprintf('(%s)', $groupQuery->getSQL()), 'vg', 'vg.vehicle_id=v.id')
            ->leftJoin('v', sprintf('(%s)', $driverQuery->getSQL()), 'd', 'd.vehicle_id=v.id')
            ->leftJoin('v', sprintf('(%s)', $routeQuery->getSQL()), 'r',
                'r.area_history_id=ah.id and r.vehicle_id=v.id')
            ->leftJoin('v', sprintf('(%s)', $idlingQuery->getSQL()), 'i',
                'i.area_history_id=ah.id and i.vehicle_id=v.id')
            ->andWhere(
                $subQb->expr()->and(
                    $subQb->expr()->gte('ah.arrived', ':dateFrom'),
                    $subQb->expr()->lte('ah.arrived', ':dateTo')
                )
            )
            ->andWhere('v.id IN (:vehicleIds)')
            ->andWhere($subQb->expr()->eq('a.status', ':status'));

        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                'a.id AS geofence_id',
                'a.ag_name',
                'a.model AS model',
                'a.reg_no AS reg_no',
                'a.default_label AS default_label',
                'a.depot_name AS depot',
                'a.arrived::timestamptz AS arrived_at',
                'a.departed::timestamptz AS departed_at',
                'a.name AS geofence',
                'a.vehicle_group_names_as_string as groups',
                'a.odometer AS odometer',
                'a.driver AS driver',
                'a.parking_time AS parking_time',
                'a.idling_time AS idling_time',
            ]
        )
            ->from(sprintf('(%s)', $subQb->getSQL()), 'a')
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'geofence'), $params['order'] ?? 'ASC')
            ->setParameter('status', Area::STATUS_ACTIVE)
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('routeType', Route::TYPE_STOP)
            ->setParameter('vehicleIds', $params['vehicles'], Connection::PARAM_INT_ARRAY)
            ->setParameter('_team_ids', $this->formatPostgresArrayString($params['teamId']));

        if (isset($params['ag_name'])) {
            $qb->setParameter('ag_name', $params['ag_name'], Connection::PARAM_INT_ARRAY);
        }

        if (isset($params['geofence'])) {
            $qb->andWhere('a.id = :geofence')
                ->setParameter('geofence', $params['geofence']);
        }

        if ($user->needToCheckUserGroup()) {
            $userAreas = $this->getEntityManager()->getRepository(UserGroup::class)->getUserAreasIdFromUserGroup($user);
            $qb->andWhere('a.id in (:areaIds)')->setParameter('areaIds', $userAreas, Connection::PARAM_INT_ARRAY);
        }

        if (isset($params['driver'])) {
            $qb->andWhere('LOWER(a.driver) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        if ($vehicleId) {
            $qb->select(
                'a.vehicle_id AS id, a.reg_no as reg_no')->groupBy('vehicle_id, reg_no')->orderBy('vehicle_id');
        }

        return $qb;
    }

    /**
     * @param array $params
     * @param User $user
     * @return \Doctrine\ORM\Query
     */
    public function getNotVisitedAreas(array $params, User $user)
    {
        $q = $this->getEntityManager()->createQueryBuilder();

        $subQuery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('aa.id')
            ->from(AreaHistory::class, 'ahh')
            ->innerJoin(Area::class, 'aa', Join::WITH, 'ahh.area = aa')
            ->andWhere('aa.team = :team')
            ->setParameter('team', $user->getTeam())
            ->andWhere('aa.status = :status')
            ->setParameter('status', Area::STATUS_ACTIVE)
            ->andWhere(
                $q->expr()->orX(
                    $q->expr()->andX(
                        $q->expr()->gte('ahh.arrived', ':dateFrom'),
                        $q->expr()->lte('ahh.arrived', ':dateTo')
                    ),
                    $q->expr()->andX(
                        $q->expr()->gte('ahh.departed', ':dateFrom'),
                        $q->expr()->lte('ahh.departed', ':dateTo')
                    ),
                    $q->expr()->andX(
                        $q->expr()->lt('ahh.arrived', ':dateFrom'),
                        $q->expr()->isNull('ahh.departed')
                    )
                )
            )
            ->groupBy('aa.id')
            ->getQuery();


        $q = $q->addSelect([
            'a.id AS geofence_id',
            'a.name AS geofence'
        ])
            ->addSelect('string_agg(ag.name, \', \') AS ag_name')
            ->from(Area::class, 'a')
//            ->leftJoin(AreaHistory::class, 'ah', Join::WITH, 'ah.area = a')
            ->leftJoin('a.groups', 'ag')
            ->andWhere('a.team = :team')
            ->setParameter('team', $user->getTeam())
            ->andWhere('a.status = :status')
            ->setParameter('status', Area::STATUS_ACTIVE)
            ->andWhere('a.id NOT IN (' . $subQuery->getDQL() . ')')
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'geofence_id'), $params['order'] ?? 'ASC')
            ->groupBy('a.id');

        if (isset($params['geofence'])) {
            $q->andWhere('a.id = :geofence')->setParameter('geofence', $params['geofence']);
        }

        if (isset($params['ag_name'])) {
            $q->andWhere('ag.id IN (:ag_name)')
                ->setParameter('ag_name', $params['ag_name'], Connection::PARAM_INT_ARRAY);
        }

        if ($user->needToCheckUserGroup()) {
            $userAreas = $this->getEntityManager()->getRepository(UserGroup::class)->getUserAreasIdFromUserGroup($user);
            $q->andWhere('a.id in (:areaIds)')->setParameter('areaIds', $userAreas, Connection::PARAM_INT_ARRAY);
        }

        return $q->getQuery();
    }

    /**
     * @param array $params
     * @param User $user
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getAreasSummary(array $params, User $user)
    {
        $qbData = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $dataQuery = $qbData->select(
            [
                'a.id',
                'SUM(EXTRACT(EPOCH FROM (ah.departed - ah.arrived)))::INTEGER AS total_time',
                'COUNT(a.id)::INTEGER AS number_of_visits',
                '(SUM(EXTRACT(EPOCH FROM (ah.departed - ah.arrived))))::INTEGER/(COUNT(a.id))::INTEGER AS average_time'
            ]
        )
            ->from('area', 'a')
            ->leftJoin('a', 'area_history', 'ah', 'ah.area_id = a.id')
            ->where(
                $qbData->expr()->and(
                    $qbData->expr()->gte('ah.arrived', ':dateFrom'),
                    $qbData->expr()->lte('ah.arrived', ':dateTo')
                )
            )
            ->andWhere('a.team_id in (:teamId)')->setParameter('teamId', $params['teamId'], Connection::PARAM_INT_ARRAY)
            ->andWhere($qbData->expr()->eq('a.status', ':status'))
            ->groupBy('a.id');

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb->addSelect([
            'a.id AS geofence_id',
            'a.name AS geofence',
            'aa.total_time',
            'aa.number_of_visits',
            'aa.average_time',
            'string_agg(ag.name, \', \') AS ag_name'
        ])->from('area', 'a')
            ->innerJoin('a', sprintf('(%s)', $dataQuery->getSQL()), 'aa', 'aa.id=a.id')
            ->leftJoin('a', 'areas_groups', 'ags', 'a.id = ags.area_id')
            ->leftJoin('ags', 'area_group', 'ag', 'ags.area_group_id = ag.id')
            ->andWhere('a.team_id in (:teamId)')->setParameter('teamId', $params['teamId'], Connection::PARAM_INT_ARRAY)
            ->andWhere($qb->expr()->eq('a.status', ':status'))
            ->setParameter('status', Area::STATUS_ACTIVE)
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->groupBy('a.id, aa.total_time, aa.number_of_visits, aa.average_time')
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'a.id'), $params['order'] ?? 'ASC');

        if (isset($params['geofence'])) {
            $qb->andHaving('a.id = :geofence')
                ->setParameter('geofence', $params['geofence']);
        }

        if (isset($params['ag_name'])) {
            $qb->andWhere('ag.id IN (:ag_name)')
                ->setParameter('ag_name', $params['ag_name'], Connection::PARAM_INT_ARRAY);
        }

        if (isset($params['numberOfVisits'])) {
            if (isset($params['numberOfVisits']['lt'])) {
                $qb->andHaving('COUNT(a.id) < :minNumberOfVisits')
                    ->setParameter('minNumberOfVisits', $params['numberOfVisits']['lt']);
            }

            if (isset($params['numberOfVisits']['gte'])) {
                $qb->andHaving('COUNT(a.id) >= :maxNumberOfVisits')
                    ->setParameter('maxNumberOfVisits', $params['numberOfVisits']['gte']);
            }
        }

        if ($user->needToCheckUserGroup()) {
            $userAreas = $this->getEntityManager()->getRepository(UserGroup::class)->getUserAreasIdFromUserGroup($user);
            $qb->andWhere('a.id in (:areaIds)')->setParameter('areaIds', $userAreas, Connection::PARAM_INT_ARRAY);
        }

        return $qb;
    }

    /**
     * @param \App\Entity\Vehicle[] $vehicles
     *
     * @return array
     */
    protected function getFlattenedVehicles(array $vehicles): array
    {
        $result = [];
        foreach ($vehicles as $vehicle) {
            $result['ids'][] = $vehicle->getId();
            $result['defaultLabels'][] = $vehicle->getDefaultLabel();
            $result['models'][] = $vehicle->getModel();
            // Set ID to "-1" (non-existing ID) for proper casting in PostgreSQL
            $result['depots'][] = $vehicle->getDepot() ? $vehicle->getDepot()->getId() : -1;
            $result['regNumbers'][] = $vehicle->getRegNo();
        }

        return $result;
    }

    /**
     * @param $values
     *
     * @return string
     */
    protected function formatPostgresArrayString($values)
    {
        $values = $values ?? [];
        foreach ($values as &$value) {
            $value = str_replace('"', '\"', $value);
        }

        return sprintf('{"%s"}', implode('","', $values));
    }
}
