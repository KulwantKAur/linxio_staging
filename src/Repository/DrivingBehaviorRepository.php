<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\DrivingBehavior;
use App\Entity\Idling;
use App\Entity\Speeding;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Util\ArrayHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class DrivingBehaviorRepository extends EntityRepository
{

    /**
     * QueryBuilder object to string for using in subquery
     * @param QueryBuilder $qb
     * @param string|null $alias
     * @param string|null $type
     * @return string
     */
    private function _(QueryBuilder $qb, ?string $alias=null, ?string $type = null): string
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
     * @param $params
     *
     * @return array
     */
    public function getVehicleEcoSpeedDetails($params): array
    {
        $deviceQb = $this->getQb();
        $allDevicesVehicleHad = $deviceQb->select('DISTINCT di.device_id AS id')
            ->from('device_installation', 'di')
            ->where(
                $deviceQb->expr()->andX(
                    $deviceQb->expr()->eq('di.vehicle_id', ':vehicleId'),
                    $deviceQb->expr()->orX(
                        ':dateFrom BETWEEN di.installDate AND di.uninstallDate',
                        ':dateTo BETWEEN di.installDate AND di.uninstallDate',
                        $deviceQb->expr()->andX(
                            $deviceQb->expr()->lte('di.uninstalldate', ':dateTo'),
                            $deviceQb->expr()->gte('di.installdate', ':dateFrom')
                        ),
                        $deviceQb->expr()->andX(
                            $deviceQb->expr()->lte('di.installdate', ':dateTo'),
                            $deviceQb->expr()->isNull('di.uninstalldate')
                        )
                    )
                )
            )
            ->setParameter('vehicleId', $params['vehicleId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->executeQuery()
            ->fetchAllAssociative();

        if (!$allDevicesVehicleHad) {
            return [];
        }

        $qb = $this->getQb();
        $qb->select(
            [
                's.id',
                'EXTRACT(EPOCH FROM (s.finished_at - s.started_at)) as duration',
                's.avg_speed',
                's.distance as total_distance',
                's.started_at as start_date',
                's.finished_at as end_date',
                'STRING_AGG(th.lat || \' \' || th.lng, \', \' order by th.ts) as coordinates',
            ]
        )
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->leftJoin('s', $this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(), 'th', 's.device_id=th.device_id AND th.ts BETWEEN s.started_at AND s.finished_at')
            ->where($qb->expr()->eq('s.vehicle_id', ':vehicleId'))
            ->andWhere(
                $qb->expr()->gte('s.started_at', ':dateFrom'),
                $qb->expr()->lte('s.finished_at', ':dateTo')
            )
            ->andWhere('s.device_id IN (:deviceIds)')
            ->groupBy('s.id')
            ->orderBy('s.started_at')
            ->setParameter('deviceIds',  array_column($allDevicesVehicleHad, 'id'), Connection::PARAM_INT_ARRAY)
            ->setParameter('vehicleId', $params['vehicleId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        $result = $qb->executeQuery()->fetchAllAssociative();

        return ArrayHelper::keysToCamelCase($result);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getVehicleIdlingDetails($params): array
    {
        $deviceQb = $this->getQb();
        $allDevicesVehicleHad = $deviceQb->select('DISTINCT di.device_id AS id')
            ->from('device_installation', 'di')
            ->where(
                $deviceQb->expr()->and(
                    $deviceQb->expr()->eq('di.vehicle_id', ':vehicleId'),
                    $deviceQb->expr()->or(
                        ':dateFrom BETWEEN di.installDate AND di.uninstallDate',
                        ':dateTo BETWEEN di.installDate AND di.uninstallDate',
                        $deviceQb->expr()->and(
                            $deviceQb->expr()->lte('di.uninstalldate', ':dateTo'),
                            $deviceQb->expr()->gte('di.installdate', ':dateFrom')
                        ),
                        $deviceQb->expr()->and(
                            $deviceQb->expr()->lte('di.installdate', ':dateTo'),
                            $deviceQb->expr()->isNull('di.uninstalldate')
                        )
                    )
                )
            )
            ->setParameter('vehicleId', $params['vehicleId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->execute()
            ->fetchAllAssociative();

        if (!$allDevicesVehicleHad) {
            return [];
        }

        $qb = $this->getQb();
        $qb->select(
            [
                'i.id',
                'i.duration',
                'i.started_at AS start_date',
                'i.finished_at AS end_date',
                'th.lng',
                'th.lat',
            ]
        )
            ->from($this->getEntityManager()->getClassMetadata(Idling::class)->getTableName(), 'i')
            ->leftJoin('i', $this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(), 'th', 'i.point_start_id=th.id')
            ->where($qb->expr()->eq('i.vehicle_id', ':vehicleId'))
            ->andWhere(
                $qb->expr()->gte('i.started_at', ':dateFrom'),
                $qb->expr()->lte('i.finished_at', ':dateTo')
            )
            ->andWhere('i.device_id IN (:deviceIds)')
            ->andWhere($qb->expr()->gte('i.duration', ':excessiveIdling'))
            ->andWhere('th.lat <> 0')
            ->andWhere('th.lng <> 0')
            ->andWhere('th.lat IS NOT NULL')
            ->andWhere('th.lng IS NOT NULL')
            ->setParameter(
                'deviceIds',
                array_column($allDevicesVehicleHad, 'id'),
                Connection::PARAM_INT_ARRAY
            )
            ->setParameter('vehicleId', $params['vehicleId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('excessiveIdling', $params['excessiveIdling'])
        ;

        $result = $qb->execute()->fetchAllAssociative() ?: [];

        return ArrayHelper::keysToCamelCase($result);
    }

    /**
     * @param $type
     * @param $params
     * @return mixed[]
     */
    public function getVehicleHarshDetails($type, $params): array
    {
        $qb = $this->getQb();
        $qb->select(
            [
                'dbh.ts as ts',
                'dbh.lng as lng',
                'dbh.lat as lat',
            ]
        )
        ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'dbh')
        ->where(
            $qb->expr()->andX(
                $qb->expr()->eq(
                    sprintf(
                        'dbh.%s',
                        [
                            'harsh-acceleration' => 'harsh_acceleration',
                            'harsh-braking' => 'harsh_braking',
                            'harsh-cornering' => 'harsh_cornering',
                        ][$type]
                    ),
                    1
                ),
                $qb->expr()->andX(
                    $qb->expr()->gte('dbh.ts', ':startDate::TIMESTAMP'),
                    $qb->expr()->lte('dbh.ts', ':endDate::TIMESTAMP')
                ),
                $qb->expr()->eq(
                    'dbh.vehicle_id',
                    ':vehicleId'
                )
            )
        )
        ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
        ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
        ->setParameter('vehicleId', $params['vehicleId'], \PDO::PARAM_INT)
        ->orderBy('dbh.ts', 'ASC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @todo not using
     * @param $params
     * @return mixed[]
     */
    public function getSummaryVehicleReportAll($params)
    {
        $qb = $this->getQb();
        $qb
            ->select(
                [
                    'v.id as vehicleId',
                    'agr_bh.count_harsh_acceleration::INT as harshAccelerationCount',
                    'agr_bh.count_harsh_braking::INT as harshBrakingCount',
                    'agr_bh.count_harsh_cornering::INT as harshCorneringCount',
                    'agr_bh.total_distance::DECIMAL as totalDistance',
                    'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                    'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                    'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                    'agr_idl.idling_count::INT as idlingCount',
                    'extract(\'epoch\' from agr_idl.idling_intervals_sum::INTERVAL) as idlingTotalTime',
                    'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                    'agr_exc_speed.sum_distance::DECIMAL as ecoSpeedTotalDistance',
                    'extract(\'epoch\' from agr_driving.driving_intervals_time::INTERVAL) as drivingTotalTime',
                    'array_to_json(agr_bh.drivers) as driverIds',
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(agr_bh.total_distance::DECIMAL, coalesce(agr_idl.idling_count, 0)::INT)'
                        ]),
                        'excessiveIdling',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(agr_bh.total_distance::DECIMAL, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                        ]),
                        'ecoSpeed',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_speeding(coalesce(agr_exc_speed.exc_speed_count, 0)::INT, agr_bh.total_distance::DECIMAL, agr_exc_speed.sum_distance::DECIMAL)'
                        ]),
                        'speeding',
                        'DECIMAL'
                    ),
                ]
            )->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.vehicle_id as vehicle_id',
                        'COUNT(bh.harsh_acceleration) as count_harsh_acceleration',
                        'COUNT(bh.harsh_braking) as count_harsh_braking',
                        'COUNT(bh.harsh_cornering) as count_harsh_cornering',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score((MAX(bh.odometer) - MIN(bh.odometer))::DECIMAL, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score((MAX(bh.odometer) - MIN(bh.odometer))::DECIMAL, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score((MAX(bh.odometer) - MIN(bh.odometer))::DECIMAL, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                        '(MAX(bh.odometer) - MIN(bh.odometer)) as total_distance',
                        'ARRAY(SELECT DISTINCT bh.driver_id::INT WHERE bh.driver_id IS NOT NULL) as drivers'
                    ])
                    ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->andX(
                                $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                            ),
                            'bh.vehicle_id=ANY((ARRAY[:vehicleIds])::INT[])'
                        )
                    )
                    ->groupBy('bh.vehicle_id')
                    ->addGroupBy('bh.driver_id ')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'idl.vehicle as vehicle_id',
                        'COUNT(idl.vehicle) as idling_count',
                        'SUM(idl.end_period - idl.start_period) as idling_intervals_sum',
                    ])
                    ->from('get_idling_periods(:startDate::TIMESTAMP, :endDate::TIMESTAMP, (ARRAY[:vehicleIds])::INT[])', 'idl')
                    ->where(
                        $qb->expr()->gte('(idl.end_period - idl.start_period)', ':idling::INTERVAL')
                    )
                    ->groupBy('idl.vehicle')
                ),
                'agr_idl',
                'agr_idl.vehicle_id = agr_bh.vehicle_id'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                            'exc_speed.vehicle as vehicle_id',
                            'COUNT(exc_speed.vehicle) as exc_speed_count',
                            'SUM(distance) as sum_distance',
                        ])
                        ->from('get_excessive_speed_periods(:excSpeedMap::JSON, :startDate::TIMESTAMP, :endDate::TIMESTAMP, (ARRAY[:vehicleIds])::INT[])', 'exc_speed')
                        ->groupBy('exc_speed.vehicle')
                ),
                'agr_exc_speed',
                'agr_exc_speed.vehicle_id = agr_bh.vehicle_id'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'driving.vehicle as vehicle_id',
                        'SUM(driving.end_period - driving.start_period) as driving_intervals_time'
                    ])
                    ->from('get_excessive_speed_periods(\'{}\'::JSON, :startDate::TIMESTAMP, :endDate::TIMESTAMP, (ARRAY[:vehicleIds])::INT[])', 'driving')
                    ->groupBy('driving.vehicle')
                ),
                'agr_driving',
                'agr_driving.vehicle_id = agr_bh.vehicle_id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(Vehicle::class)->getTableName(),
                'v',
                'v.id=agr_bh.vehicle_id')
            ->where('v.id IN (:vehicleIds)')
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('vehicleIds', $params['vehicleIds'], Connection::PARAM_INT_ARRAY)
            ->setParameter('idling', sprintf('%s second', $params['idling']),\PDO::PARAM_STR)
            ->setParameter('excSpeedMap',  json_encode($params['excSpeedMap']), \PDO::PARAM_STR)
        ;

        if (isset($params['order'])) {
            foreach ($params['order'] as $key=>$order) {
                if (!$key) {
                    $qb->orderBy(...$order);
                } else {
                    $qb->addOrderBy(...$order);
                }
            }
        }

        if (isset($params['offset'])) {
            $qb->setFirstResult($params['offset']);
        }

        if (isset($params['limit'])) {
            $qb->setMaxResults($params['limit']);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getSummaryVehicleReport($params, $key)
    {
        $qb = $this->getQb();
        $qb->select(
                [
                    'v.id as vehicleId',
                    'agr_bh.count_harsh_acceleration::INT as harshAccelerationCount',
                    'agr_bh.count_harsh_braking::INT as harshBrakingCount',
                    'agr_bh.count_harsh_cornering::INT as harshCorneringCount',
                    'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                    'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                    'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                    'agr_idl.idling_count::INT as idlingCount',
                    'agr_idl.idlingTotalTime as idlingTotalTime',
                    'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                    'agr_exc_speed.sum_distance::DECIMAL as ecoSpeedTotalDistance',
                    'agr_bh.drivers as driverIds',
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_idl.idling_count, 0)::INT)'
                        ]),
                        'excessiveIdling',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                        ]),
                        'ecoSpeed',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_speeding(coalesce(agr_exc_speed.exc_speed_count, 0)::INT, :totalDistance, agr_exc_speed.sum_distance::DECIMAL)'
                        ]),
                        'speeding',
                        'DECIMAL'
                    ),
                ]
            )->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.vehicle_id as vehicle_id',
                        'COUNT(bh.harsh_acceleration) as count_harsh_acceleration',
                        'COUNT(bh.harsh_braking) as count_harsh_braking',
                        'COUNT(bh.harsh_cornering) as count_harsh_cornering',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                        'json_agg(DISTINCT bh.driver_id) as drivers'
                    ])
                        ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                        ->where(
                            $qb->expr()->andX(
                                $qb->expr()->andX(
                                    $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                    $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                                ),
                                'bh.vehicle_id=:vehicleId'
                            )
                        )
                        ->groupBy('bh.vehicle_id')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'v',
                $this->_(
                    $this->getQb()->select([
                        'idl.vehicle_id as vehicle_id',
                        'COUNT(idl.vehicle_id) as idling_count',
                        'SUM(idl.duration) as idlingTotalTime',
                    ])
                        ->from('idling', 'idl')
                        ->leftJoin('idl', $this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(), 'th', 'idl.point_start_id=th.id')
                        ->andWhere('th.lat <> 0')
                        ->andWhere('th.lng <> 0')
                        ->andWhere('th.lat IS NOT NULL')
                        ->andWhere('th.lng IS NOT NULL')
                        ->where('idl.vehicle_id = :vehicleId')
                        ->andWhere('idl.duration > :excessiveIdling')
                        ->andWhere($qb->expr()->and(
                            $qb->expr()->gte('idl.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('idl.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('idl.vehicle_id')
                ),
                'agr_idl',
                'agr_idl.vehicle_id = v.id'
            )
            ->leftJoin(
                'v',
                $this->_(
                    $this->getQb()->select([
                        'exc_speed.vehicle_id as vehicle_id',
                        'COUNT(exc_speed.vehicle_id) as exc_speed_count',
                        'SUM(distance) as sum_distance',
                    ])
                        ->from('speeding', 'exc_speed')
                        ->where('exc_speed.vehicle_id = :vehicleId')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('exc_speed.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('exc_speed.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('exc_speed.vehicle_id')
                ),
                'agr_exc_speed',
                'agr_exc_speed.vehicle_id = v.id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(Vehicle::class)->getTableName(),
                'v',
                'v.id=agr_bh.vehicle_id')
            ->where('v.id = :vehicleId')
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('vehicleId', $params['vehicleIds'][$key], \PDO::PARAM_INT)
            ->setParameter('totalDistance', $params['totalDistance'][$key], \PDO::PARAM_INT)
            ->setParameter('drivingTotalTime', $params['drivingTotalTime'][$key], \PDO::PARAM_INT)
            ->setParameter('excessiveIdling', $params['excessiveIdling'][$key], \PDO::PARAM_INT)
        ;

        return $qb->execute()->fetchAssociative();
    }

    public function getSummaryDriverReport($params, $key)
    {
        $qb = $this->getQb();
        $qb->select(
                [
                    'u.id as driverId',
                    'agr_bh.count_harsh_acceleration::INT as harshAccelerationCount',
                    'agr_bh.count_harsh_braking::INT as harshBrakingCount',
                    'agr_bh.count_harsh_cornering::INT as harshCorneringCount',
                    'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                    'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                    'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                    'agr_idl.idling_count::INT as idlingCount',
                    'agr_idl.idlingTotalTime as idlingTotalTime',
                    'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                    'agr_exc_speed.sum_distance::DECIMAL as ecoSpeedTotalDistance',
                    'agr_bh.vehicles as vehicleIds',
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_idl.idling_count, 0)::INT)'
                        ]),
                        'excessiveIdling',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                        ]),
                        'ecoSpeed',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_speeding(coalesce(agr_exc_speed.exc_speed_count, 0)::INT, :totalDistance, agr_exc_speed.sum_distance::DECIMAL)'
                        ]),
                        'speeding',
                        'DECIMAL'
                    ),
                ]
            )->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.driver_id as driver_id',
                        'COUNT(bh.harsh_acceleration) as count_harsh_acceleration',
                        'COUNT(bh.harsh_braking) as count_harsh_braking',
                        'COUNT(bh.harsh_cornering) as count_harsh_cornering',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                        'json_agg(DISTINCT bh.vehicle_id) as vehicles'
                    ])
                        ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                        ->where(
                            $qb->expr()->andX(
                                $qb->expr()->andX(
                                    $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                    $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                                ),
                                'bh.driver_id=:driverId'
                            )
                        )
                        ->groupBy('bh.driver_id')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'idl.driver_id as driver_id',
                        'COUNT(idl.driver_id) as idling_count',
                        'SUM(idl.duration) as idlingTotalTime',
                    ])
                        ->from('idling', 'idl')
                        ->where('idl.driver_id = :driverId')
                        ->andWhere('idl.duration > :excessiveIdling')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('idl.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('idl.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('idl.driver_id')
                ),
                'agr_idl',
                'agr_idl.driver_id = agr_bh.driver_id'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'exc_speed.driver_id as driver_id',
                        'COUNT(exc_speed.driver_id) as exc_speed_count',
                        'SUM(distance) as sum_distance',
                    ])
                        ->from('speeding', 'exc_speed')
                        ->where('exc_speed.driver_id = :driverId')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('exc_speed.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('exc_speed.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('exc_speed.driver_id')
                ),
                'agr_exc_speed',
                'agr_exc_speed.driver_id = agr_bh.driver_id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(User::class)->getTableName(),
                'u',
                'u.id=agr_bh.driver_id')
            ->where('u.id = :driverId')
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('driverId', $params['driverIds'][$key], \PDO::PARAM_INT)
            ->setParameter('totalDistance', $params['totalDistance'][$key], \PDO::PARAM_INT)
            ->setParameter('drivingTotalTime', $params['drivingTotalTime'][$key], \PDO::PARAM_INT)
            ->setParameter('excessiveIdling', $params['excessiveIdling'][$key], \PDO::PARAM_INT)
        ;

        return $qb->execute()->fetch();
    }

    /**
     * @param $params
     * @return array|null
     */
    public function getVehicleScores(array $params): ?array
    {
        $qb = $this->getQb();
        $qb->select(
                [
                    'v.id as vehicleId',
                    'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                    'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                    'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                    'agr_idl.idling_count::INT as idlingCount',
                    'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_idl.idling_count, 0)::INT)'
                        ]),
                        'excessiveIdlingScore',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                        ]),
                        'ecoSpeedScore',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_speeding(coalesce(agr_exc_speed.exc_speed_count, 0)::INT, :totalDistance, agr_exc_speed.sum_distance::DECIMAL)'
                        ]),
                        'speeding',
                        'DECIMAL'
                    ),
                ]
            )
            ->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.vehicle_id as vehicle_id',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                    ])
                    ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->andX(
                                $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                            ),
                            'bh.vehicle_id=:vehicleId::INT'
                        )
                    )
                    ->groupBy('bh.vehicle_id')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'v',
                $this->_(
                    $this->getQb()->select([
                        'idl.vehicle_id as vehicle_id',
                        'COUNT(idl.vehicle_id) as idling_count',
                    ])
                        ->from('idling', 'idl')
                        ->where('idl.vehicle_id = :vehicleId')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('idl.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('idl.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('idl.vehicle_id')
                ),
                'agr_idl',
                'agr_idl.vehicle_id = v.id'
            )
            ->leftJoin(
                'v',
                $this->_(
                    $this->getQb()->select([
                        'exc_speed.vehicle_id as vehicle_id',
                        'COUNT(exc_speed.vehicle_id) as exc_speed_count',
                        'SUM(distance) as sum_distance',
                    ])
                        ->from('speeding', 'exc_speed')
                        ->where($qb->expr()->andX(
                            $qb->expr()->gte('exc_speed.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('exc_speed.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('exc_speed.vehicle_id')
                ),
                'agr_exc_speed',
                'agr_exc_speed.vehicle_id = v.id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(Vehicle::class)->getTableName(),
                'v',
                'v.id=agr_bh.vehicle_id')
            ->where('v.id = :vehicleId')
            ->setParameter('vehicleId', $params['vehicleId'], \PDO::PARAM_STR)
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('totalDistance', $params['totalDistance'], \PDO::PARAM_INT)
        ;

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result ? $result[0] : null;
    }

    /**
     * @param $params
     * @return array|null
     */
    public function getDriverScores(array $params): ?array
    {
        $qb = $this->getQb();
        $qb->select(
            [
                'u.id as driverId',
                'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                'agr_idl.idling_count::INT as idlingCount',
                'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                $this->_(
                    $this->getQb()->select([
                        'calc_event_score(:totalDistance, coalesce(agr_idl.idling_count, 0)::INT)'
                    ]),
                    'excessiveIdlingScore',
                    'DECIMAL'
                ),
                $this->_(
                    $this->getQb()->select([
                        'calc_event_score(:totalDistance, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                    ]),
                    'ecoSpeedScore',
                    'DECIMAL'
                ),
                $this->_(
                    $this->getQb()->select([
                        'calc_speeding(coalesce(agr_exc_speed.exc_speed_count, 0)::INT, :totalDistance, agr_exc_speed.sum_distance::DECIMAL)'
                    ]),
                    'speeding',
                    'DECIMAL'
                ),
            ]
        )
            ->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.driver_id as driver_id',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                    ])
                        ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                        ->where(
                            $qb->expr()->andX(
                                $qb->expr()->andX(
                                    $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                    $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                                ),
                                'bh.driver_id=:driverId::INT'
                            )
                        )
                        ->groupBy('bh.driver_id')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'idl.driver_id as driver_id',
                        'COUNT(idl.driver_id) as idling_count',
                    ])
                        ->from('idling', 'idl')
                        ->where('idl.driver_id = :driverId')
                        ->andWhere('idl.duration > :excessiveIdling')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('idl.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('idl.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('idl.driver_id')
                ),
                'agr_idl',
                'agr_idl.driver_id = agr_bh.driver_id'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'exc_speed.driver_id as driver_id',
                        'COUNT(exc_speed.driver_id) as exc_speed_count',
                        'SUM(distance) as sum_distance',
                    ])
                        ->from('speeding', 'exc_speed')
                        ->where($qb->expr()->andX(
                            $qb->expr()->gte('exc_speed.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('exc_speed.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('exc_speed.driver_id')
                ),
                'agr_exc_speed',
                'agr_exc_speed.driver_id = agr_bh.driver_id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(User::class)->getTableName(),
                'u',
                'u.id=agr_bh.driver_id')
            ->where('u.id = :driverId')
            ->setParameter('driverId', $params['driverId'], \PDO::PARAM_STR)
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('totalDistance', $params['totalDistance'], \PDO::PARAM_INT)
            ->setParameter('excessiveIdling', $params['excessiveIdling'], \PDO::PARAM_INT)
        ;

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result ? $result[0] : null;
    }

    /**
     * @param array $params
     * @return array|null
     */
    public function getVehicleEventsCountWithScores(array $params): ?array
    {
        $qb = $this->getQb();
        $qb->select(
                [
                    'v.id as vehicleId',
                    'agr_idl.idling_count::INT as idlingCount',
                    'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                    'agr_bh.count_harsh_acceleration::INT as harshAccelerationCount',
                    'agr_bh.count_harsh_braking::INT as harshBrakingCount',
                    'agr_bh.count_harsh_cornering::INT as harshCorneringCount',
                    'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                    'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                    'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_idl.idling_count, 0)::INT)'
                        ]),
                        'excessiveIdlingScore',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                        ]),
                        'ecoSpeedScore',
                        'DECIMAL'
                    ),
                ]
            )
            ->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.vehicle_id as vehicle_id',
                        'COUNT(bh.harsh_acceleration) as count_harsh_acceleration',
                        'COUNT(bh.harsh_braking) as count_harsh_braking',
                        'COUNT(bh.harsh_cornering) as count_harsh_cornering',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                    ])
                    ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->andX(
                                $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                            ),
                            'bh.vehicle_id=:vehicleId::INT'
                        )
                    )
                    ->groupBy('bh.vehicle_id')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'idl.vehicle_id as vehicle_id',
                        'COUNT(idl.vehicle_id) as idling_count',
                    ])
                        ->from('idling', 'idl')
                        ->where('idl.vehicle_id = :vehicleId')
                        ->andWhere('idl.duration > :excessiveIdling')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('idl.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('idl.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('idl.vehicle_id')
                ),
                'agr_idl',
                'agr_idl.vehicle_id = agr_bh.vehicle_id'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'exc_speed.vehicle_id as vehicle_id',
                        'COUNT(exc_speed.vehicle_id) as exc_speed_count',
                        'SUM(distance) as sum_distance',
                    ])
                        ->from('speeding', 'exc_speed')
                        ->where($qb->expr()->andX(
                            $qb->expr()->gte('exc_speed.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('exc_speed.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('exc_speed.vehicle_id')
                ),
                'agr_exc_speed',
                'agr_exc_speed.vehicle_id = agr_bh.vehicle_id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(Vehicle::class)->getTableName(),
                'v',
                'v.id=agr_bh.vehicle_id')
            ->where('v.id = :vehicleId')
            ->setParameter('vehicleId', $params['vehicleId'], \PDO::PARAM_STR)
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('totalDistance', $params['totalDistance'], \PDO::PARAM_INT)
            ->setParameter('excessiveIdling', $params['excessiveIdling'], \PDO::PARAM_INT)
        ;

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result ? $result[0] : null;
    }

    /**
     * @param array $params
     * @return array|null
     */
    public function getDriverEventsCountWithScores(array $params): ?array
    {
        $qb = $this->getQb();
        $qb->select(
                [
                    'u.id as driverId',
                    'agr_idl.idling_count::INT as idlingCount',
                    'agr_exc_speed.exc_speed_count::INT as ecoSpeedEventCount',
                    'agr_bh.count_harsh_acceleration::INT as harshAccelerationCount',
                    'agr_bh.count_harsh_braking::INT as harshBrakingCount',
                    'agr_bh.count_harsh_cornering::INT as harshCorneringCount',
                    'coalesce(agr_bh.harsh_acceleration, 100)::DECIMAL as harshAccelerationScore',
                    'coalesce(agr_bh.harsh_braking, 100)::DECIMAL as harshBrakingScore',
                    'coalesce(agr_bh.harsh_cornering, 100)::DECIMAL as harshCorneringScore',
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_idl.idling_count, 0)::INT)'
                        ]),
                        'excessiveIdlingScore',
                        'DECIMAL'
                    ),
                    $this->_(
                        $this->getQb()->select([
                            'calc_event_score(:totalDistance, coalesce(agr_exc_speed.exc_speed_count, 0)::INT)'
                        ]),
                        'ecoSpeedScore',
                        'DECIMAL'
                    ),
                ]
            )
            ->from(
                $this->_(
                    $this->getQb()->select([
                        'bh.driver_id as driver_id',
                        'COUNT(bh.harsh_acceleration) as count_harsh_acceleration',
                        'COUNT(bh.harsh_braking) as count_harsh_braking',
                        'COUNT(bh.harsh_cornering) as count_harsh_cornering',
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_acceleration))'
                            ),
                            'harsh_acceleration'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_braking))'
                            ),
                            'harsh_braking'
                        ),
                        $this->_(
                            $this->getQb()->select(
                                'calc_event_score(:totalDistance, COUNT(bh.harsh_cornering))'
                            ),
                            'harsh_cornering'
                        ),
                    ])
                    ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'bh')
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->andX(
                                $qb->expr()->gte('bh.ts', ':startDate::TIMESTAMP'),
                                $qb->expr()->lte('bh.ts', ':endDate::TIMESTAMP')
                            ),
                            'bh.driver_id=:driverId::INT'
                        )
                    )
                    ->groupBy('bh.driver_id')
                ),
                'agr_bh'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'idl.driver_id as driver_id',
                        'COUNT(idl.driver_id) as idling_count',
                    ])
                        ->from('idling', 'idl')
                        ->where('idl.driver_id = :driverId')
                        ->andWhere('idl.duration > :excessiveIdling')
                        ->andWhere($qb->expr()->andX(
                            $qb->expr()->gte('idl.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('idl.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('idl.driver_id')
                ),
                'agr_idl',
                'agr_idl.driver_id = agr_bh.driver_id'
            )
            ->leftJoin(
                'agr_bh',
                $this->_(
                    $this->getQb()->select([
                        'exc_speed.driver_id as driver_id',
                        'COUNT(exc_speed.driver_id) as exc_speed_count',
                        'SUM(distance) as sum_distance',
                    ])
                        ->from('speeding', 'exc_speed')
                        ->where($qb->expr()->andX(
                            $qb->expr()->gte('exc_speed.started_at', ':startDate::TIMESTAMP'),
                            $qb->expr()->lte('exc_speed.finished_at', ':endDate::TIMESTAMP')
                        ))
                        ->groupBy('exc_speed.driver_id')
                ),
                'agr_exc_speed',
                'agr_exc_speed.driver_id = agr_bh.driver_id'
            )
            ->rightJoin(
                'agr_bh',
                $this->_em->getClassMetadata(User::class)->getTableName(),
                'u',
                'u.id=agr_bh.driver_id')
            ->where('u.id = :driverId')
            ->setParameter('driverId', $params['driverId'], \PDO::PARAM_STR)
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('totalDistance', $params['totalDistance'], \PDO::PARAM_INT)
            ->setParameter('excessiveIdling', $params['excessiveIdling'], \PDO::PARAM_INT)
        ;

        $result = $qb->executeQuery()->fetchAllAssociative();

        return $result ? $result[0] : null;
    }

    /**
     * @param $type
     * @param $params
     * @return mixed[]
     */
    public function getDriverHarshDetails($type, $params): array
    {
        $qb = $this->getQb();
        $qb->select(
            [
                'dbh.ts as ts',
                'dbh.lng as lng',
                'dbh.lat as lat',
            ]
        )
            ->from($this->_em->getClassMetadata(DrivingBehavior::class)->getTableName(), 'dbh')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq(
                        sprintf(
                            'dbh.%s',
                            [
                                'harsh-acceleration' => 'harsh_acceleration',
                                'harsh-braking' => 'harsh_braking',
                                'harsh-cornering' => 'harsh_cornering',
                            ][$type]
                        ),
                        1
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->gte('dbh.ts', ':startDate::TIMESTAMP'),
                        $qb->expr()->lte('dbh.ts', ':endDate::TIMESTAMP')
                    ),
                    $qb->expr()->eq(
                        'dbh.driver_id',
                        ':driverId'
                    )
                )
            )
            ->setParameter('startDate', $params['startDate'], \PDO::PARAM_STR)
            ->setParameter('endDate', $params['endDate'], \PDO::PARAM_STR)
            ->setParameter('driverId', $params['driverId'], \PDO::PARAM_INT)
            ->orderBy('dbh.ts', 'ASC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param $distance
     * @param $count
     * @return float|null
     */
    public function getScoreByDistanceAndCount($distance, $count): ?float
    {
        $qb = $this->getQb();
        $qb->select(
            [
                'calc_event_score(:totalDistance, :count) AS score',
            ]
        )
            ->setParameter('totalDistance', $distance, \PDO::PARAM_INT)
            ->setParameter('count', $count, \PDO::PARAM_STR)
        ;

        $result = $qb->execute()->fetch();

        return $result ? $result['score'] : null;
    }

    /**
     * @param Vehicle $vehicle
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSpeedingCountByVehicle(Vehicle $vehicle)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Speeding::class, 's')
            ->where('s.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Vehicle $vehicle
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getIdlingCountByVehicle(Vehicle $vehicle)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Idling::class, 'i')
            ->where('i.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Vehicle $vehicle
     * @param \DateTime $startedAt
     *
     * @return Query
     */
    public function getDrivingBehaviorByVehicleAndStartedDateQb(Vehicle $vehicle, \DateTime $startedAt): Query
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $qb->select('db')
            ->from(DrivingBehavior::class, 'db')
            ->where($qb->expr()->eq('IDENTITY(db.vehicle)', ':vehicle'))
            ->andWhere($qb->expr()->gte('db.ts', ':startedAt'))
            ->setParameter('vehicle', $vehicle)
            ->setParameter('startedAt', $startedAt)
            ->getQuery();
    }

    /**
     * @param int $thId
     * @param int $odometer
     * @return mixed
     */
    public function updateOdometerByTrackerHistoryId(
        int $thId,
        int $odometer
    ) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->update(DrivingBehavior::class, 'db')
            ->set('db.odometer', $odometer)
            ->where('IDENTITY(db.trackerHistory) = :thId')
            ->setParameter('thId', $thId);

        return $query->getQuery()->execute();
    }

    /**
     * @param Device $device
     * @param Vehicle|null $vehicle
     * @param int $odometer
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function updateOdometerByRangeAndDevice(
        Device $device,
        ?Vehicle $vehicle,
        int $odometer,
        $dateFrom,
        $dateTo
    ) {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->update(DrivingBehavior::class, 'db')
            ->set('db.odometer', $odometer)
            ->where('db.device = :device')
            ->setParameter('device', $device)
            ->andWhere('db.ts >= :dateFrom')
            ->setParameter('dateFrom', $dateFrom)
            ->andWhere('db.ts <= :dateTo')
            ->setParameter('dateTo', $dateTo);

        if ($vehicle) {
            $query->andWhere('db.vehicle = :vehicle')
                ->setParameter('vehicle', $vehicle);
        }

        return $query->getQuery()->execute();
    }
}
