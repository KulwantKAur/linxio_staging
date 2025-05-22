<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\Depot;
use App\Entity\DeviceInstallation;
use App\Entity\DrivingBehavior;
use App\Entity\Route;
use App\Entity\RouteTemp;
use App\Entity\Setting;
use App\Entity\Speeding;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryIO;
use App\Entity\Tracker\TrackerIOType;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleEngineHours;
use App\Entity\VehicleGroup;
use App\Entity\VehicleOdometer;
use App\Report\Core\DTO\VehicleDaySummaryDTO;
use App\Service\DrivingBehavior\DrivingBehaviorService;
use App\Util\Doctrine\DoctrineHelper;
use App\Util\GeoHelper;
use App\Util\RouteHelper;
use App\Util\StringHelper;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use RuntimeException;

class VehicleRepository extends EntityRepository
{
    /**
     * @param EntityManager $em
     * @param QueryBuilder $qb
     * @return mixed
     */
    private function handlePositionOnSubQueryByTrackerHistory(EntityManager $em, QueryBuilder $qb)
    {
        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $rTable = $em->getClassMetadata(Route::class)->getTableName();

        return $em->getConnection()->createQueryBuilder()
            ->select(
                'array_to_string((array_agg(r.address order by r.started_at DESC))[1:1], \', \') AS position_on'
            )
            ->from($rTable, 'r')
            ->leftJoin(
                'r',
                $trackerHistoryTable,
                'th_input_on_position_sub',
                'th_input_on_position_sub.vehicle_id=r.vehicle_id'
            )
            ->where('r.type = :stopped')
            ->andWhere('r.started_at <= th_input_on_position_sub.ts')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('th_input_on_position_sub.ts', ':dateTo'),
                    $qb->expr()->gte('th_input_on_position_sub.ts', ':dateFrom'),
                    $qb->expr()->lte('r.started_at', ':dateTo'),
                    $qb->expr()->gte('r.finished_at', ':dateFrom'),
                )
            )
            ->andWhere('th_input_on_position_sub.id = thio.tracker_history_on_id')
            ->andWhere('th_input_on_position_sub.vehicle_id = thio.vehicle_id')
            ->setParameter('stopped', Route::TYPE_STOP, Types::STRING)
            ->setMaxResults(1);
    }

    /**
     * @param EntityManager $em
     * @param QueryBuilder $qb
     * @return mixed
     */
    private function handlePositionOffSubQueryByTrackerHistory(EntityManager $em, QueryBuilder $qb)
    {
        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $rTable = $em->getClassMetadata(Route::class)->getTableName();

        return $em->getConnection()->createQueryBuilder()
            ->select(
                'array_to_string((array_agg(r.address order by r.started_at DESC))[1:1], \', \') AS position_off'
            )
            ->from($rTable, 'r')
            ->leftJoin(
                'r',
                $trackerHistoryTable,
                'th_input_off_position_sub',
                'th_input_off_position_sub.vehicle_id=r.vehicle_id'
            )
            ->where('r.type = :stopped')
            ->andWhere('r.started_at <= th_input_off_position_sub.ts')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('th_input_off_position_sub.ts', ':dateTo'),
                    $qb->expr()->gte('th_input_off_position_sub.ts', ':dateFrom'),
                    $qb->expr()->lte('r.started_at', ':dateTo'),
                    $qb->expr()->gte('r.finished_at', ':dateFrom'),
                )
            )
            ->andWhere('th_input_off_position_sub.id = thio.tracker_history_off_id')
            ->andWhere('th_input_off_position_sub.vehicle_id = thio.vehicle_id')
            ->setParameter('stopped', Route::TYPE_STOP, Types::STRING)
            ->setMaxResults(1);
    }

    /**
     * @param EntityManager $em
     * @param QueryBuilder $qb
     * @param $dateFrom
     * @param $dateTo
     * @param bool|null $isAreaFilter
     * @return mixed
     */
    private function handleStartAreasSubQueryByTrackerHistory(
        EntityManager $em,
        QueryBuilder $qb,
        $dateFrom,
        $dateTo,
        ?bool $isAreaFilter = false
    ) {
        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $aTable = $em->getClassMetadata(Area::class)->getTableName();

        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->select('string_agg(a.name, \', \') AS start_areas_name')
            ->from($aTable, 'a')
            ->leftJoin(
                'a',
                $trackerHistoryTable,
                'th_start_area',
                'ST_Contains(a.polygon, ST_GeomFromText(concat(\'POINT(\', th_start_area.lng, \' \', th_start_area.lat, \')\'))) = true'
            )
            ->andWhere('a.status = :areaStatus')
            ->andWhere('a.team_id = :teamId')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('th_start_area.ts', ':dateTo'),
                    $qb->expr()->gte('th_start_area.ts', ':dateFrom')
                )
            )
            ->andWhere('th_start_area.lng IS NOT NULL')
            ->andWhere('th_start_area.lat IS NOT NULL')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->groupBy('th_start_area.id');

        if ($isAreaFilter) {
            $query->addSelect('th_start_area.id AS start_area_th_id');
        } else {
            $query->andWhere('th_start_area.id = thio.tracker_history_on_id');
        }

        return $query;
    }

    /**
     * @param EntityManager $em
     * @param QueryBuilder $qb
     * @param $dateFrom
     * @param $dateTo
     * @param bool|null $isAreaFilter
     * @return mixed
     */
    private function handleFinishAreasSubQueryByTrackerHistory(
        EntityManager $em,
        QueryBuilder $qb,
        $dateFrom,
        $dateTo,
        ?bool $isAreaFilter = false
    ) {
        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $aTable = $em->getClassMetadata(Area::class)->getTableName();
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->select('string_agg(a.name, \', \') AS finish_areas_name')
            ->from($aTable, 'a')
            ->leftJoin(
                'a',
                $trackerHistoryTable,
                'th_finish_area',
                'ST_Contains(a.polygon, ST_GeomFromText(concat(\'POINT(\', th_finish_area.lng, \' \', th_finish_area.lat, \')\'))) = true'
            )
            ->andWhere('a.status = :areaStatus')
            ->andWhere('a.team_id = :teamId')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('th_finish_area.ts', ':dateTo'),
                    $qb->expr()->gte('th_finish_area.ts', ':dateFrom')
                )
            )
            ->andWhere('th_finish_area.lng IS NOT NULL')
            ->andWhere('th_finish_area.lat IS NOT NULL')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->groupBy('th_finish_area.id');

        if ($isAreaFilter) {
            $query->addSelect('th_finish_area.id AS finish_area_th_id');
        } else {
            $query->andWhere('th_finish_area.id = thio.tracker_history_off_id');
        }

        return $query;
    }

    /**
     * @return mixed
     */
    public function getVehiclesWhichNotExistInDeviceInstallation()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->leftJoin(DeviceInstallation::class, 'di', Expr\Join::WITH, 'di.vehicle = v.id')
            ->where('di.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Depot $depot
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getVehiclesByDepotIterator(Depot $depot)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->where('v.depot = :depot')
            ->setParameter('depot', $depot)
            ->getQuery()
            ->iterate();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getVehicleIdsByTeam(User $user)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v.id')
            ->from(Vehicle::class, 'v', 'v.id')
            ->andWhere('v.team = :team')
            ->setParameter('team', $user->getTeam())
            ->andWhere('v.status IN (:statuses)')
            ->setParameter('statuses', Vehicle::LIST_STATUSES);

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $q->andWhere('v.id in (:vehicleIds)')->setParameter('vehicleIds', $userVehicles);
        }

        return $q->getQuery()->getResult();
    }

    /**
     * @param int $team
     * @return int|mixed|string
     */
    public function getVehicleIdByTeam(int $team)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v.id as client_vehicle_id')
            ->addSelect('ft.name as fuel_type')
            ->addSelect('v.regNo as registration')
            ->addSelect('v.vin as vin')
            ->addSelect('v.make as make')
            ->addSelect('v.makeModel as model')
            ->from(Vehicle::class, 'v', 'v.id')
            ->leftJoin('v.fuelType', 'ft')
            ->andWhere('IDENTITY(v.team) = :team')
            ->setParameter('team', $team)
            ->andWhere('v.status IN (:statuses)')
            ->setParameter('statuses', Vehicle::LIST_STATUSES);

        return $q->getQuery()->getResult();
    }

    /**
     * @param Team $team
     * @return mixed
     */
    public function getVehicleIdsByTeamWithoutStatuses(Team $team)
    {
        $results = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v.id')
            ->from(Vehicle::class, 'v', 'v.id')
            ->andWhere('v.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        return array_map(
            function ($id) {
                return $id['id'];
            },
            $results
        );
    }

    public function getVehicleIdListByTeam(Team $team)
    {
        $results = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v.id')
            ->from(Vehicle::class, 'v', 'v.id')
            ->andWhere('v.team = :team')
            ->andWhere('v.status IN (:statuses)')
            ->setParameter('statuses', Vehicle::LIST_STATUSES)
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        return array_map(
            function ($id) {
                return $id['id'];
            },
            $results
        );
    }

    public function getVehicleIdByRegNoExcludeCurrent(Team $team, Vehicle $vehicle, string $regNo)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v.id')
            ->from(Vehicle::class, 'v', 'v.id')
            ->andWhere('v.team = :team')
            ->setParameter('team', $team)
            ->andWhere('v.regNo = :regNo')
            ->setParameter('regNo', $regNo)
            ->andWhere('v.id != :id')
            ->setParameter('id', $vehicle->getId())
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param string $regNo
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getVehicleByRegNo(string $regNo)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->andWhere('lower(v.regNo) = lower(:regNo)')
            ->setParameter('regNo', $regNo)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param Team $team
     * @param $vehicleId
     * @param string $vin
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getVehicleIdByVinExcludeCurrent(Team $team, $vehicleId, string $vin)
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v.id')
            ->from(Vehicle::class, 'v', 'v.id')
            ->andWhere('v.team = :team')
            ->setParameter('team', $team)
            ->andWhere('v.vin = :vin')
            ->setParameter('vin', $vin);

        if ($vehicleId) {
            $q->andWhere('v.id != :id')
                ->setParameter('id', $vehicleId);
        }

        return $q->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * @param $params
     * @return \Doctrine\DBAL\Query\QueryBuilder
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getVehiclesSummary($params)
    {
        $em = $this->getEntityManager();
        $connection = $this->getEntityManager()->getConnection();
        $order = $params['order'] ?? 'ASC';
        $vTable = $em->getClassMetadata(Vehicle::class)->getTableName();
        $vgTable = $em->getClassMetadata(VehicleGroup::class)->getTableName();
        $dTable = $em->getClassMetadata(Depot::class)->getTableName();
        $thTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $uTable = $em->getClassMetadata(User::class)->getTableName();
        $routeQb = $connection->createQueryBuilder();
        $routeQb
            ->select(
                [
                    'r.vehicle_id',
                    'MAX(r.finish_odometer)::BIGINT AS end_odometer',
                    'MIN(r.start_odometer)::BIGINT AS start_odometer',
                    'SUM(CASE WHEN r.type=\'driving\' THEN r.distance END)::BIGINT AS distance',
                    'SUM(CASE WHEN r.type=\'driving\' and r.scope=\'private\' THEN r.distance END)::BIGINT AS private_distance',
                    'SUM(CASE WHEN r.type=\'driving\' and r.scope=\'work\' THEN r.distance END)::BIGINT AS work_distance',
                    'MAX(r.max_speed)::INTEGER AS max_speed',
                    'COUNT(CASE WHEN r.type=\'stopped\' THEN r.id END)::INTEGER as stops',
                    'SUM(CASE WHEN r.type=\'stopped\' THEN EXTRACT(EPOCH FROM (r.finished_at - r.started_at)) END)::BIGINT AS parking_time',
                    'SUM(CASE WHEN r.type=\'driving\' THEN EXTRACT(EPOCH FROM (r.finished_at - r.started_at)) END)::BIGINT AS driving_time',
                    'SUM(r.total_idle_duration::BIGINT) AS idling_time',
                    'MIN(th_start.engine_on_time) as min_engine_on_time',
                    'MAX(th_finish.engine_on_time) as max_engine_on_time',
                    'MAX(r.finished_at) AS max_finished_at',
                    'MIN(r.started_at) AS min_started_at',
                    'string_agg(DISTINCT(CONCAT(d.name, \' \', d.surname)), \', \') AS driverId'
                ]
            )
            ->from($em->getClassMetadata(Route::class)->getTableName(), 'r')
            ->innerJoin('r', $vTable, 'v', 'r.vehicle_id = v.id')
            ->leftJoin('r', $thTable, 'th_start', 'r.point_start_id = th_start.id')
            ->leftJoin('r', $thTable, 'th_finish', 'r.point_finish_id = th_finish.id')
            ->leftJoin('r', $uTable, 'd', 'r.driver_id = d.id')
            ->andWhere('v.id in (:vehicleIds)')
            ->andWhere(
                $routeQb->expr()->and(
                    $routeQb->expr()->lte('r.started_at', ':dateTo'),
                    $routeQb->expr()->gte('r.finished_at', ':dateFrom')
                )
            )
            ->groupBy('r.vehicle_id');

        $odometerQb = $em->getRepository(VehicleOdometer::class)->getLastOdometerCorrectionQueryBuilder();;

        $vehicleGroupQb = $connection->createQueryBuilder()
            ->select('string_agg(vg.name, \', \') AS groups, vgs.vehicle_id')
            ->from('vehicles_groups', 'vgs')
            ->leftJoin('vgs', $vgTable, 'vg', 'vg.id = vgs.vehicle_group_id')
            ->andWhere('vehicle_id in (:vehicleIds)')
            ->groupBy('vgs.vehicle_id');

        $excessiveSpeedQb = $connection->createQueryBuilder()
            ->select(
                [
                    'exc_speed.vehicle AS vehicle_id',
                    'COUNT(exc_speed.vehicle) AS exc_speed_count',
                ]
            )
            ->from(
                'get_excessive_speed_periods(:excessiveSpeedMap::JSON, :dateFrom::TIMESTAMP, :dateTo::TIMESTAMP, (ARRAY[:vehicleIds])::INT[]) ',
                'exc_speed'
            )
            ->groupBy('exc_speed.vehicle');

        $speedEventQb = $connection->createQueryBuilder()
            ->select(
                [
                    'db.vehicle_id',
                    'COUNT(db.harsh_acceleration) +
                    (SELECT calc_event_score((MAX(db.odometer) - MIN(db.odometer))::DECIMAL, COUNT(db.harsh_acceleration))) +
                    COUNT(db.harsh_braking) +
                    COUNT(db.harsh_cornering) +
                    (SELECT calc_event_score((MAX(db.odometer) - MIN(db.odometer))::DECIMAL, COUNT(db.harsh_acceleration))) +
                    (SELECT calc_event_score((MAX(db.odometer) - MIN(db.odometer))::DECIMAL, COUNT(db.harsh_braking))) +
                    (SELECT calc_event_score((MAX(db.odometer) - MIN(db.odometer))::DECIMAL, COUNT(db.harsh_cornering)))::INTEGER AS speeding_event_count'
                ]
            )
            ->from($this->getEntityManager()->getClassMetadata(DrivingBehavior::class)->getTableName(), 'db')
            ->innerJoin('db', $vTable, 'v', 'db.vehicle_id = v.id')
            ->andWhere('db.ts BETWEEN :dateFrom AND :dateTo')
            ->andWhere('v.id in (:vehicleIds)')
            ->groupBy('db.vehicle_id');

        return $connection->createQueryBuilder()
            ->select(
                [
                    'v.id AS id',
                    'NULLIF(v.model, \'\') AS model',
                    'v.regno AS reg_no',
                    'v.regno AS regno',
                    'v.defaultlabel AS default_label',
                    'vd.name AS depot',
                    'vg.groups AS groups',
                    'r.private_distance AS private_distance',
                    'r.work_distance AS work_distance',
                    'r.distance AS distance',
                    'COALESCE(get_v_last_accuracy(v.id, r.min_started_at),0) + COALESCE(get_v_start_odometer(v.id, r.min_started_at, r.max_finished_at),0) AS start_odometer',
                    'COALESCE(get_v_last_accuracy(v.id, r.max_finished_at),0) + COALESCE(get_v_finish_odometer(v.id, r.min_started_at, r.max_finished_at),0) AS end_odometer',
                    'r.max_speed AS max_speed',
                    'r.stops AS stops',
                    'r.parking_time AS parking_time',
                    'r.driving_time AS driving_time',
                    'r.idling_time AS idling_time',
                    '(SELECT calc_event_score(r.distance::DECIMAL, es.exc_speed_count::INT))::DECIMAL AS eco_drive_scores',
                    'es.exc_speed_count AS eco_speeding_events',
                    'se.speeding_event_count AS speeding_events',
                    '(r.min_engine_on_time)::BIGINT as min_engine_on_time',
                    '(r.max_engine_on_time)::BIGINT as max_engine_on_time',
                    '(max_engine_on_time - min_engine_on_time)::BIGINT as engine_on_time',
                    'r.driverId as driver_id'
                ]
            )
            ->from($vTable, 'v')
            ->leftJoin('v', $dTable, 'vd', 'v.depot_id = vd.id')
            ->leftJoin('v', sprintf('(%s)', $routeQb->getSQL()), 'r', 'r.vehicle_id = v.id')
            ->leftJoin('v', sprintf('(%s)', $vehicleGroupQb->getSQL()), 'vg', 'vg.vehicle_id = v.id')
            ->leftJoin('v', sprintf('(%s)', $excessiveSpeedQb->getSQL()), 'es', 'es.vehicle_id = v.id')
            ->leftJoin('v', sprintf('(%s)', $speedEventQb->getSQL()), 'se', 'se.vehicle_id = v.id')
            ->leftJoin('v', sprintf('(%s)', $odometerQb->getSQL()), 'vo', 'vo.vehicle_id = v.id')
            ->andWhere('v.id in (:vehicleIds)')
            ->setParameter('vehicleIds', $params['vehicles'], Connection::PARAM_INT_ARRAY)
            ->orderBy(
                StringHelper::toSnakeCase($params['sort'] ?? 'id'),
                $order . ' NULLS LAST'
            )
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('excessiveSpeedMap', json_encode($params['excSpeedMap']));
    }

    public function getVehiclesDaySummary(VehicleDaySummaryDTO $params, $getVehiclesList = false)
    {
        $em = $this->getEntityManager();
        $connection = $this->getEntityManager()->getConnection();
        $vTable = $this->getEntityManager()->getClassMetadata(Vehicle::class)->getTableName();
        $vgTable = $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getTableName();
        $dTable = $this->getEntityManager()->getClassMetadata(Depot::class)->getTableName();
        $thTable = $this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName();

        $routeQb = $connection->createQueryBuilder();
        $routeQb
            ->select(
                [
                    'r.vehicle_id',
                    'MAX(r.finish_odometer)::BIGINT AS end_odometer',
                    'MIN(r.start_odometer)::BIGINT AS start_odometer',
                    'SUM(CASE WHEN r.type=\'driving\' THEN r.distance END)::BIGINT AS distance',
                    'SUM(CASE WHEN r.scope=\'private\' THEN r.distance END)::BIGINT AS private_distance',
                    'SUM(CASE WHEN r.scope=\'work\' THEN r.distance END)::BIGINT AS work_distance',
                    'MAX(r.max_speed)::INTEGER AS max_speed',
                    'COUNT(CASE WHEN r.type=\'stopped\' THEN r.id END)::INTEGER as stops',
                    'SUM(CASE WHEN r.type=\'stopped\' THEN EXTRACT(EPOCH FROM (r.finished_at - r.started_at)) END)::BIGINT AS parking_time',
                    'SUM(CASE WHEN r.type=\'driving\' THEN EXTRACT(EPOCH FROM (r.finished_at - r.started_at)) END)::BIGINT AS driving_time',
                    'MAX(r.finished_at) AS max_finished_at',
                    'MIN(r.started_at) AS min_started_at',
                    'MIN(th_start.engine_on_time) as min_engine_on_time',
                    'MAX(th_finish.engine_on_time) as max_engine_on_time'
                ]
            )
            ->from($this->getEntityManager()->getClassMetadata(Route::class)->getTableName(), 'r')
            ->innerJoin('r', $vTable, 'v', 'r.vehicle_id = v.id')
            ->leftJoin('r', $thTable, 'th_start', 'r.point_start_id = th_start.id')
            ->leftJoin('r', $thTable, 'th_finish', 'r.point_finish_id = th_finish.id')
            ->andWhere('v.id in (:vehicleIds)')
            ->groupBy('r.vehicle_id');

        $routeQb->andWhere(
            $routeQb->expr()->and(
                $routeQb->expr()->lte('r.started_at', ':dateTo'),
                $routeQb->expr()->gte('r.finished_at', ':dateFrom')
            )
        );
//        }

        $lastMaxOdometer = $connection->createQueryBuilder();
        $lastMaxOdometer
            ->select(
                [
                    'r.vehicle_id',
                    'MAX(r.finish_odometer)::BIGINT AS max_odometer',
                ]
            )
            ->from($this->getEntityManager()->getClassMetadata(Route::class)->getTableName(), 'r')
            ->innerJoin('r', $vTable, 'v', 'r.vehicle_id = v.id')
            ->andWhere('v.id in (:vehicleIds)')
            ->andWhere(
                $lastMaxOdometer->expr()->and(
                    $lastMaxOdometer->expr()->lte('r.started_at', ':dateTo')
                )
            )
            ->groupBy('r.vehicle_id');

        $odometerQb = $em->getRepository(VehicleOdometer::class)->getLastOdometerCorrectionQueryBuilder();

        // Get latest odometer reading of vehicle 
        $lastOdometerByTimeSQL = "
            SELECT DISTINCT ON (r.vehicle_id)
                r.vehicle_id,
                r.finish_odometer::BIGINT AS last_recorded_odometer,
                r.finished_at
            FROM {$this->getEntityManager()->getClassMetadata(Route::class)->getTableName()} r
            INNER JOIN {$vTable} v ON r.vehicle_id = v.id
            WHERE v.id IN (:vehicleIds)
              AND r.finished_at <= :dateTo
            ORDER BY r.vehicle_id, r.finished_at DESC
        ";

        $vehicleGroupQb = $connection->createQueryBuilder()
            ->select('string_agg(vg.name, \', \') AS groups, vgs.vehicle_id')
            ->from('vehicles_groups', 'vgs')
            ->leftJoin('vgs', $vgTable, 'vg', 'vg.id = vgs.vehicle_group_id')
            ->andWhere('vehicle_id in (:vehicleIds)')
            ->groupBy('vgs.vehicle_id');


        $excessiveSpeedQb = $connection->createQueryBuilder()
            ->select(
                [
                    'exc_speed.vehicle AS vehicle_id',
                    'COUNT(exc_speed.vehicle) AS exc_speed_count',
                ]
            )
            ->from(
                'get_excessive_speed_periods(:excessiveSpeedMap::JSON, :dateFrom::TIMESTAMP, :dateTo::TIMESTAMP, (ARRAY[:vehicleIds])::INT[]) ',
                'exc_speed'
            )
            ->groupBy('exc_speed.vehicle');

        $speedEventQb = $connection->createQueryBuilder();
        $speedEventQb = $speedEventQb
            ->select(
                [
                    's.vehicle_id',
                    'COUNT(s)::INTEGER AS speeding_event_count'
                ]
            )
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->andWhere('s.vehicle_id in (:vehicleIds)')
            ->andWhere(
                $speedEventQb->expr()->and(
                    $speedEventQb->expr()->lte('s.started_at', ':dateTo'),
                    $speedEventQb->expr()->gte('s.finished_at', ':dateFrom')
                )
            )
            ->groupBy('s.vehicle_id');

        if ($getVehiclesList) {
            $connection = $connection->createQueryBuilder()->select('v.id')
                ->addSelect(
                    [
                        'v.id AS id',
                        'NULLIF(v.model, \'\') AS model',
                        'v.regno AS reg_no',
                        'v.regno AS regno',
                        'v.defaultlabel AS default_label',
                        'vd.name AS depot',
                        'vg.groups AS groups'
                    ]
                );
        } else {
            $connection = $connection->createQueryBuilder()->select(
                [
                    'v.id AS id',
                    'v.defaultlabel',
                    'v.regno',
                    'v.model',
                    'vg.groups',
                    'vd.name as depot',
                    'get_v_last_accuracy(v.id, r.min_started_at)',
                    'COALESCE(r.work_distance, 0) AS work_distance',
                    'COALESCE(r.private_distance, 0) AS private_distance',
                    'COALESCE(get_v_last_accuracy(v.id, r.min_started_at),0) + COALESCE(get_v_start_odometer(v.id, r.min_started_at, r.max_finished_at), lmo.max_odometer, 0) AS start_odometer',
                    'COALESCE(get_v_last_accuracy(v.id, r.max_finished_at),0) + COALESCE(get_v_finish_odometer(v.id, r.min_started_at, r.max_finished_at), lmo.max_odometer, 0) AS end_odometer',
                    'lod.last_recorded_odometer AS last_recorded_odometer',
                    'lod.finished_at AS last_odometer_timestamp',
                    'r.distance AS distance',
                    '(r.min_engine_on_time)::BIGINT as min_engine_on_time',
                    '(r.max_engine_on_time)::BIGINT as max_engine_on_time',
                    '(max_engine_on_time - min_engine_on_time)::BIGINT as engine_on_time',
                    'r.max_speed AS max_speed',
                    'COALESCE(r.driving_time, 0) AS driving_time',
                    'COALESCE(r.stops, 0) AS stops',
                    'COALESCE(r.parking_time, 0) AS parking_time',
                    'COALESCE(se.speeding_event_count, 0) AS speeding_events',
                    '(SELECT calc_event_score(r.distance::DECIMAL, COALESCE(es.exc_speed_count::INT, 0)))::DECIMAL AS eco_drive_scores',
                    'COALESCE(es.exc_speed_count, 0) AS eco_speeding_events',
                    'max_finished_at',
                    'min_started_at',
                ]
            );
        }

        if (!$getVehiclesList) {
            $connection = $connection
                ->leftJoin('v', sprintf('(%s)', $excessiveSpeedQb->getSQL()), 'es', 'es.vehicle_id = v.id')
                ->leftJoin('v', sprintf('(%s)', $speedEventQb->getSQL()), 'se', 'se.vehicle_id = v.id')
                ->leftJoin('v', sprintf('(%s)', $odometerQb->getSQL()), 'vo', 'vo.vehicle_id = v.id')
//                ->leftJoin('v', sprintf('(%s)', $engineHoursQb->getSQL()), 'eh', 'eh.vehicle_id = v.id')
                ->leftJoin('v', sprintf('(%s)', $routeQb->getSQL()), 'r', 'r.vehicle_id = v.id')
                ->leftJoin('v', sprintf('(%s)', $lastMaxOdometer->getSQL()), 'lmo', 'lmo.vehicle_id = v.id')
                ->leftJoin('v', "($lastOdometerByTimeSQL)", 'lod', 'lod.vehicle_id = v.id');
        }

        return $connection
            ->from($vTable, 'v')
            ->leftJoin('v', $dTable, 'vd', 'v.depot_id = vd.id')
            ->leftJoin('v', sprintf('(%s)', $vehicleGroupQb->getSQL()), 'vg', 'vg.vehicle_id = v.id')
            ->andWhere('v.id in (:vehicleIds)')
            ->setParameter('vehicleIds', $params->vehicles, Connection::PARAM_INT_ARRAY)
            ->setParameter('dateFrom', $params->startDate)
            ->setParameter('dateTo', $params->endDate)
            ->setParameter('excessiveSpeedMap', json_encode($params->vehicles));
    }

    /**
     * @param \App\Entity\Vehicle[] $vehicles
     *
     * @return array
     */
    public static function getFlattenedVehicles(array $vehicles): array
    {
        $result = [];
        foreach ($vehicles as $vehicle) {
            $result['ids'][] = $vehicle->getId();
            $result['models'][] = $vehicle->getModel();
            $result['defaultLabels'][] = $vehicle->getDefaultLabel();
            // Set ID to "-1" (non-existing ID) for proper casting in PostgresQL
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
    public static function formatPostgresArrayString($values)
    {
        $values = $values ?? [];
        foreach ($values as &$value) {
            $value = str_replace('"', '\"', $value);
        }

        return sprintf(
            '{"%s"}',
            implode('","', $values)
        );
    }

    /**
     * QueryBuilder object to string for using in subquery
     * @param QueryBuilder $qb
     * @param string|null $alias
     * @param string|null $type
     * @return string
     */
    private function convertSubselectToSQLString(QueryBuilder $qb, ?string $alias = null, ?string $type = null): string
    {
        $buff = sprintf('(%s)', $qb->getSQL());

        if ($type) {
            $buff = sprintf('%s::%s', $buff, $type);
        }

        if ($alias) {
            $buff = sprintf('%s as %s', $buff, $alias);
        }

        return $buff;
    }

    /**
     * @return QueryBuilder
     */
    private function getQb(): QueryBuilder
    {
        return $this->getEntityManager()->getConnection()->createQueryBuilder();
    }

    /**
     * @param Vehicle $vehicle
     * @param $dateFrom
     * @param $dateTo
     * @return null
     */
    public function getTotalOdometer(Vehicle $vehicle, $dateFrom, $dateTo)
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('(MAX(th.odometer) - MIN(th.odometer)) as total_distance')
            ->from($this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(), 'th')
            ->where('th.vehicle_id = :vehicleId')
            ->andWhere('th.ts BETWEEN :dateFrom AND :dateTo')
            ->andWhere('th.odometer IS NOT NULL')
            ->setParameter('vehicleId', $vehicle->getId())
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->execute()
            ->fetchAll();

        return $result ? array_sum(array_column($result, 'total_distance')) : null;
    }

    public function getTotalOdometerArray(array $vehicles, $dateFrom, $dateTo)
    {
        if (empty($vehicles)) {
            return [];
        }

        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('th_vehicle_distance(v.id, :dateFrom, :dateTo) as total_distance, v.id as vehicle_id')
            ->from($this->getEntityManager()->getClassMetadata(Vehicle::class)->getTableName(), 'v')
            ->where('v.id in (' . implode(', ', $vehicles) . ')')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->execute()
            ->fetchAllAssociative();

        $data = [];
        foreach ($result as $item) {
            $data[$item['vehicle_id']] = $item['total_distance'];
        }

        return $data;
    }

    /**
     * @param Vehicle $vehicle
     * @param $dateFrom
     * @param $dateTo
     * @return null
     */
    public function getTotalDrivingTime(Vehicle $vehicle, $dateFrom, $dateTo)
    {
        $drivingBehaviorCalcType = $vehicle->getTeam()->getSettingsByName(Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE);

        if ($drivingBehaviorCalcType
            && $drivingBehaviorCalcType->getValue() === DrivingBehaviorService::CALCULATION_TYPE_ROUTE) {
            $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
                ->select(
                    'EXTRACT(EPOCH FROM r.started_at)::INTEGER as started_at, 
                EXTRACT(EPOCH FROM r.finished_at)::INTEGER as finished_at'
                )
                ->from($this->getEntityManager()->getClassMetadata(RouteTemp::class)->getTableName(), 'r')
                ->where('r.vehicle_id = :vehicleId')
                ->andWhere('r.started_at <= :dateTo')
                ->andWhere('r.finished_at >= :dateFrom')
                ->andWhere('r.type = :routeType')
                ->setParameter('vehicleId', $vehicle->getId())
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
                ->setParameter('routeType', Route::TYPE_DRIVING)
                ->execute()
                ->fetchAll();

            return $result ? RouteHelper::calcDrivingTimeByRoutes($result, $dateFrom, $dateTo) : null;
        } else {
            $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
                ->select(
                    'EXTRACT(EPOCH FROM th.ts)::INTEGER as ts, th.ignition::TEXT, th.movement::TEXT'
                )
                ->from($this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(), 'th')
                ->where('th.vehicle_id = :vehicleId')
                ->andWhere('th.ts BETWEEN :dateFrom AND :dateTo')
                ->setParameter('vehicleId', $vehicle->getId())
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
                ->orderBy('th.ts')
                ->execute()
                ->fetchAll();

            return $result ? GeoHelper::calcDrivingTimeAccordingToDeviceStatus($result) : null;
        }
    }

    public function getTotalDrivingTimeForArray(array $vehicles, $dateFrom, $dateTo)
    {
        if (empty($vehicles)) {
            return null;
        }
        $vehicleIds = array_values(array_map(fn(Vehicle $v) => $v->getId(), $vehicles));

        $drivingBehaviorCalcType = reset($vehicles)->getTeam()->getSettingsByName(Setting::DRIVING_BEHAVIOR_CALCULATION_TYPE);

        if ($drivingBehaviorCalcType
            && $drivingBehaviorCalcType->getValue() === DrivingBehaviorService::CALCULATION_TYPE_ROUTE
        ) {
            $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
                ->select(
                    'EXTRACT(EPOCH FROM r.started_at)::INTEGER as started_at, 
                EXTRACT(EPOCH FROM r.finished_at)::INTEGER as finished_at, vehicle_id'
                )
                ->from($this->getEntityManager()->getClassMetadata(RouteTemp::class)->getTableName(), 'r')
                ->andWhere('r.vehicle_id IN (' . implode(', ', $vehicleIds) . ')')
                ->andWhere('r.started_at <= :dateTo')
                ->andWhere('r.finished_at >= :dateFrom')
                ->andWhere('r.type = :routeType')
                ->setParameter('vehicleIds', implode(', ', $vehicleIds))
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
                ->setParameter('routeType', Route::TYPE_DRIVING)
                ->execute()
                ->fetchAllAssociative();

            return $result ? RouteHelper::calcDrivingTimeByRoutesVehicleArray($result, $dateFrom, $dateTo) : [];
        } else {
            $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
                ->select(
                    'EXTRACT(EPOCH FROM th.ts)::INTEGER as ts, th.ignition::INTEGER, th.movement::INTEGER, th.vehicle_id::INTEGER'
                )
                ->from($this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(), 'th')
                ->andWhere('th.vehicle_id IN (' . implode(', ', $vehicleIds) . ')')
                ->andWhere('th.ts BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
                ->orderBy('th.ts')
                ->execute()
                ->fetchAllAssociative();

            return $result ? GeoHelper::calcDrivingTimeAccordingToDeviceStatusForVehicleArray($result) : [];
        }
    }

    /**
     * @param User $user
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getVehicleById(User $user, $id)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->andWhere('v.id = :id')
            ->setParameter('id', $id);

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $qb->andWhere('v.id in (:vehicleIds)')->setParameter('vehicleIds', $userVehicles);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getVehicleByIdForInstall(User $user, $id)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->andWhere('v.id = :id')
            ->setParameter('id', $id);

        if ($user->isInClientTeam()) {
            $qb->andWhere('v.team = :team')->setParameter('team', $user->getTeam());
        }

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $qb->andWhere('v.id in (:vehicleIds)')->setParameter('vehicleIds', $userVehicles)
                ->orWhere('v.createdBy = :user')->setParameter('user', $user);
        }

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    public function getVehicleCountByTeam(Team $team): int
    {
        return (int)$this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(v.id)')
            ->from(Vehicle::class, 'v')
            ->andWhere('v.team = :team')
            ->andWhere('v.status IN (:statuses)')
            ->setParameter('team', $team)
            ->setParameter('statuses', Vehicle::LIST_STATUSES)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function vehiclesIOListQuery(array $data)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $dateFrom = $data['startDate'];
        $dateTo = $data['endDate'];
        $vehicleIds = $data['vehicleIds'];
        $inputLabel = $data['inputLabel'];
        $inputTypeIds = $data['inputTypeIds'];
        $order = $data['order'];
        $sort = ($data['sort'] === 'inputTypeIds' || $data['sort'] === NULL) ? 'defaultlabel' : $data['sort'];
        $areaStartId = $data['areaStartId'];
        $areaFinishId = $data['areaFinishId'];
        $teamId = $data['teamId'];

        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $vTable = $em->getClassMetadata(Vehicle::class)->getTableName();
        $driverTable = $em->getClassMetadata(User::class)->getTableName();
        $depotTable = $em->getClassMetadata(Depot::class)->getTableName();
        $vgTable = $em->getClassMetadata(VehicleGroup::class)->getTableName();
        $trackerHistoryIOTable = $em->getClassMetadata(TrackerHistoryIO::class)->getTableName();
        $trackerIOTypeTable = $em->getClassMetadata(TrackerIOType::class)->getTableName();

        $groupsSubQuery = $em->getConnection()->createQueryBuilder()
            ->select('string_agg(vg.name, \', \') AS groups, vgs.vehicle_id')
            ->from('vehicles_groups', 'vgs')
            ->leftJoin('vgs', $vgTable, 'vg', 'vg.id =vgs.vehicle_group_id')
            ->groupBy('vgs.vehicle_id');

        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->from($trackerHistoryIOTable, 'thio')
            ->leftJoin('thio', $trackerHistoryTable, 'th_on', 'th_on.id = thio.tracker_history_on_id')
            ->leftJoin('thio', $trackerHistoryTable, 'th_off', 'th_off.id = thio.tracker_history_off_id')
            ->leftJoin('thio', $trackerIOTypeTable, 'tiot', 'tiot.id = thio.type_id')
            ->leftJoin('thio', $driverTable, 'u', 'thio.driver_id = u.id')
            ->leftJoin('thio', $vTable, 'v', 'thio.vehicle_id = v.id')
            ->leftJoin('v', $depotTable, 'depot', 'v.depot_id = depot.id')
            ->leftJoin(
                'v',
                sprintf('(%s)', $groupsSubQuery->getSQL()),
                'vgs',
                'vgs.vehicle_id = v.id'
            )
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->setParameter('type', Route::TYPE_DRIVING)
            ->setParameter('stopped', Route::TYPE_STOP, Types::STRING)
            ->setParameter('teamId', $teamId);

        $positionOnSubSelect = $this->handlePositionOnSubQueryByTrackerHistory($em, $qb);
        $positionOffSubSelect = $this->handlePositionOffSubQueryByTrackerHistory($em, $qb);
        $startAreasSubSelect = $this->handleStartAreasSubQueryByTrackerHistory($em, $qb, $dateFrom, $dateTo);
        $finishAreasSubSelect = $this->handleFinishAreasSubQueryByTrackerHistory($em, $qb, $dateFrom, $dateTo);

        $query->addSelect('v.defaultlabel, v.regno, v.model')
            ->addSelect('CONCAT(u.name, \' \', u.surname) as driver_name')
            ->addSelect('vgs.groups')
            ->addSelect('depot.name as depot_name, depot.id as depot_id')
            ->addSelect('tiot.name AS input_type, tiot.label AS input_label')
            ->addSelect(
                'thio.id, thio.vehicle_id, thio.device_id, to_char(thio.occurred_at_on, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS ts_on, thio.value_on AS input_value_on'
            )
            ->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($positionOnSubSelect))
            ->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($startAreasSubSelect))
            ->addSelect('to_char(thio.occurred_at_off, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS ts_off, thio.value_off AS input_value_off')
            ->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($positionOffSubSelect))
            ->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($finishAreasSubSelect))
            ->addSelect('NULL AS fuel_consumed')
            ->addSelect('(th_off.odometer - th_on.odometer)::INT AS distance')
            ->addSelect('EXTRACT(EPOCH FROM (thio.occurred_at_off - thio.occurred_at_on))::INT AS duration')
            ->addSelect(
                [
                    '(SUM ((th_off.odometer - th_on.odometer)::INT) OVER ()) as distance_total',
                    '(SUM (EXTRACT(EPOCH FROM (thio.occurred_at_off - thio.occurred_at_on))::INT) OVER ()) as duration_total'
                ]
            )
            ->orderBy($sort, $order)
            ->setParameter('stopped', Route::TYPE_STOP, Types::STRING)
            ->setParameter('areaStatus', Area::STATUS_ACTIVE);


        $query->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte('thio.occurred_at_on', ':dateTo'),
                $qb->expr()->gte('thio.occurred_at_on', ':dateFrom')
            )
        );

        if ($vehicleIds) {
            $query->andWhere('thio.vehicle_id IN (' . implode(', ', $vehicleIds) . ')');
        }
        if ($inputTypeIds) {
            $query->andWhere('thio.type_id IN (' . implode(', ', $inputTypeIds) . ')');
        }
        if ($inputLabel) {
            $query->andWhere('LOWER(tiot.label) LIKE LOWER(:inputLabel)')
                ->setParameter('inputLabel', $inputLabel . '%');
        }

        if ($data['driverId'] ?? null) {
            $query->andWhere('u.id IN (:driverId)')
                ->setParameter('driverId', $data['driverId'], Connection::PARAM_INT_ARRAY);
        }

        if ($areaStartId) {
            $startAreaSubQuery = $this->handleStartAreasSubQueryByTrackerHistory($em, $qb, $dateFrom, $dateTo, true);
            $startAreaSubQuery->andWhere('a.id = :areaFrom');
            $query->setParameter('areaFrom', $areaStartId);
            $query->rightJoin(
                'thio',
                sprintf('(%s)', $startAreaSubQuery->getSQL()),
                'start_areas_filter',
                'start_areas_filter.start_area_th_id = thio.tracker_history_on_id'
            );
        }
        if ($areaFinishId) {
            $finishAreaSubQuery = $this->handleFinishAreasSubQueryByTrackerHistory($em, $qb, $dateFrom, $dateTo, true);
            $finishAreaSubQuery->andWhere('a.id = :areaTo');
            $query->setParameter('areaTo', $areaFinishId);
            $query->rightJoin(
                'thio',
                sprintf('(%s)', $finishAreaSubQuery->getSQL()),
                'finish_areas_filter',
                'finish_areas_filter.finish_area_th_id = thio.tracker_history_off_id'
            );
        }

        return $query;
    }

    /**
     * @param array $vehicleIds
     * @return mixed
     */
    public function getVehiclesByIds(array $vehicleIds)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->where('v.id IN (:vehicleIds)')
            ->setParameter('vehicleIds', $vehicleIds)
            ->getQuery()
            ->getResult();
    }

    public function getVehiclesByDepotIdsAndTeam(array $depotIds, Team $team)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->andWhere('IDENTITY(v.depot) IN (:depotIds)')
            ->andWhere('v.team = :team')
            ->setParameter('depotIds', $depotIds)
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    public function getVehiclesByIdsAndTeam(array $vehicleIds, Team $team)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->andWhere('v.id IN (:vehicleIds)')
            ->andWhere('v.team = :team')
            ->andWhere('v.status IN (:statuses)')
            ->setParameter('statuses', Vehicle::LIST_STATUSES)
            ->setParameter('team', $team)
            ->setParameter('vehicleIds', $vehicleIds)
            ->getQuery()
            ->getResult();
    }

    public function getVehiclesByTeam(Team $team)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->andWhere('v.team = :team')
            ->andWhere('v.status IN (:statuses)')
            ->setParameter('statuses', Vehicle::LIST_STATUSES)
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Team $team
     * @param array $deviceIds
     * @return array|null
     */
    public function getVehiclesWithDeviceData(Team $team, array $deviceIds = []): ?array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v AS vehicle, ltr.ts AS lastDataReceivedAt, ltr.ignition, ltr.movement')
            ->from(Vehicle::class, 'v')
            ->leftJoin('v.device', 'd')
            ->leftJoin('d.lastTrackerRecord', 'ltr')
            ->where('v.device IS NOT NULL')
            ->andWhere('d.lastTrackerRecord IS NOT NULL')
            ->andWhere('v.team = :team')
            ->setParameter('team', $team);

        if ($deviceIds) {
            $qb->andWhere('d.id IN (:deviceIds)')
                ->setParameter('deviceIds', $deviceIds);
        }

        return $qb->getQuery()->getResult();
    }

    public function getVehicleForDrivingBehaviour(array $ids)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v, depot, groups')
            ->from(Vehicle::class, 'v')
            ->leftJoin('v.depot', 'depot')
            ->leftJoin('v.groups', 'groups')
            ->andWhere('v.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->indexBy('v', 'v.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Team $team
     * @param array $deviceIds
     * @return Query
     */
    public function getVehiclesForDriverAutoLogoutQuery(Team $team, array $deviceIds = []): Query
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vehicle::class, 'v')
            ->where('v.team = :team')
            ->andWhere('v.driver IS NOT NULL')
            ->setParameter('team', $team);

        if ($deviceIds) {
            $qb->andWhere('v.device IN (:deviceIds)')
                ->setParameter('deviceIds', $deviceIds);
        }

        return $qb->getQuery();
    }
}
