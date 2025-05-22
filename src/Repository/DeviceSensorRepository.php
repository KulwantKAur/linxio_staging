<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\DeviceSensor;
use App\Entity\DeviceSensorType;
use App\Entity\Route;
use App\Entity\Sensor;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\Vehicle;
use App\Repository\Traits\FiltersTrait;
use App\Util\Doctrine\DoctrineHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

/**
 * DeviceSensorRepository
 */
class DeviceSensorRepository extends \Doctrine\ORM\EntityRepository
{
    use FiltersTrait;

    /**
     * @param string|null $sort
     * @return string|null
     */
    private function mapSortForVehicleList(?string $sort): ?string
    {
        switch ($sort) {
            case 'sensor_id':
                return 's.sensor_id';
            case 'label':
                return 's.label';
            case 'value':
                return 'temperature';
            default:
                return $sort;
        }
    }

    /**
     * @param string|null $sort
     * @return string|null
     */
    private function mapSortForSensorList(?string $sort): ?string
    {
        switch ($sort) {
            case 'vehicle':
                return 'v.defaultlabel';
            case 'label':
                return 's.label';
            case 'value':
                return 'temperature';
            default:
                return $sort;
        }
    }

    /**
     * @param $data
     * @param EntityManager $em
     * @param QueryBuilder $query
     * @param QueryBuilder $qb
     * @param $rTable
     * @param $trackerHistorySensorTable
     * @param $trackerHistoryTable
     * @param $vehicleIds
     * @return mixed
     */
    private function handlePositionSubQueryByVehicle(
        $data,
        $em,
        $query,
        $qb,
        $rTable,
        $trackerHistorySensorTable,
        $trackerHistoryTable,
        $vehicleIds
    ) {
        // performance hack: for chart, limit = 0 - position is not needed
        if (!isset($data['limit']) || $data['limit'] > 0) {
            $positionSubSelect = $em->getConnection()->createQueryBuilder()
                ->select(
                    'array_to_string((array_agg(r.address order by r.started_at DESC))[1:1], \', \') AS position'
                )
                ->from($rTable, 'r')
                ->leftJoin('r', $trackerHistorySensorTable, 'ths_sub', 'ths_sub.vehicle_id=r.vehicle_id')
                ->where('r.type = :stopped')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->lte('ths_sub.occurred_at', 'r.finished_at'),
                            $qb->expr()->gte('ths_sub.occurred_at', 'r.started_at')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->isNull('r.finished_at'),
                            $qb->expr()->gte('ths_sub.occurred_at', 'r.started_at')
                        )
                    )
                )
                ->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->lte('ths_sub.occurred_at', ':dateTo'),
                        $qb->expr()->gte('ths_sub.occurred_at', ':dateFrom')
                    )
                )
                ->andWhere('ths_sub.id = ths.id')
                ->groupBy('ths_sub.id')
                ->setParameter('stopped', Route::TYPE_STOP, Types::STRING);

            if ($vehicleIds) {
                $positionSubSelect->andWhere('ths_sub.vehicle_id IN (' . implode(', ', $vehicleIds) . ')');
            }

            $query->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($positionSubSelect));

            $coordinatesSubSelect = $em->getConnection()->createQueryBuilder()
                ->select('CONCAT(th.lat, \', \', th.lng) AS coordinates')
                ->from($trackerHistoryTable, 'th')
                ->where($qb->expr()->lte('th.ts', 'ths.occurred_at'))
                ->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->lte('th.ts', ':dateTo'),
                        $qb->expr()->gte('th.ts', ':dateFrom')
                    )
                )
                ->andWhere('th.vehicle_id = ths.vehicle_id')
                ->orderBy('th.ts', Criteria::DESC)
                ->setMaxResults(1);

            $query->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($coordinatesSubSelect));
        }

        return $query;
    }

    /**
     * @param $data
     * @param EntityManager $em
     * @param QueryBuilder $query
     * @param QueryBuilder $qb
     * @param $rTable
     * @param $trackerHistorySensorTable
     * @param $trackerHistoryTable
     * @return mixed
     */
    private function handlePositionSubQueryBySensor(
        $data,
        $em,
        $query,
        $qb,
        $rTable,
        $trackerHistorySensorTable,
        $trackerHistoryTable
    ) {
        // performance hack: for chart, limit = 0 - position is not needed
        if (!isset($data['limit']) || $data['limit'] > 0) {
            $positionSubSelect = $em->getConnection()->createQueryBuilder()
                ->select(
                    'array_to_string((array_agg(r.address order by r.started_at DESC))[1:1], \', \') AS position'
                )
                ->from($rTable, 'r')
                ->leftJoin('r', $trackerHistorySensorTable, 'ths_sub', 'ths_sub.vehicle_id = r.vehicle_id')
                ->where('r.type = :stopped')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->lte('ths_sub.occurred_at', 'r.finished_at'),
                            $qb->expr()->gte('ths_sub.occurred_at', 'r.started_at')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->isNull('r.finished_at'),
                            $qb->expr()->gte('ths_sub.occurred_at', 'r.started_at')
                        )
                    )
                )
                ->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->lte('ths_sub.occurred_at', ':dateTo'),
                        $qb->expr()->gte('ths_sub.occurred_at', ':dateFrom')
                    )
                )
                ->andWhere('ths_sub.id = ths.id')
                ->groupBy('ths_sub.id')
                ->setParameter('stopped', Route::TYPE_STOP, Types::STRING);

            $query->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($positionSubSelect));

            $coordinatesSubSelect = $em->getConnection()->createQueryBuilder()
                ->select('CONCAT(th.lat, \', \', th.lng) AS coordinates')
                ->from($trackerHistoryTable, 'th')
                ->where($qb->expr()->lte('th.ts', 'ths.occurred_at'))
                ->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->lte('th.ts', ':dateTo'),
                        $qb->expr()->gte('th.ts', ':dateFrom')
                    )
                )
                ->andWhere('th.device_id = ths.device_id')
                ->orderBy('th.ts', Criteria::DESC)
                ->setMaxResults(1);

            $query->addSelect(DoctrineHelper::addSubSelectFromQueryBuilder($coordinatesSubSelect));
        }

        return $query;
    }

    /**
     * @param Sensor $sensor
     * @param Device $device
     * @return DeviceSensor|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySensorAndDevice(Sensor $sensor, Device $device)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ds')
            ->from(DeviceSensor::class, 'ds')
            ->where('ds.device = :device')
            ->andWhere('ds.sensor = :sensor')
            ->setParameter('device', $device)
            ->setParameter('sensor', $sensor)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getOnlineDeviceSensors()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ds')
            ->from(DeviceSensor::class, 'ds')
            ->where('ds.status = :status')
            ->andWhere('ds.lastOccurredAt IS NOT NULL')
            ->setParameter('status', DeviceSensor::STATUS_ONLINE)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $data
     * @param bool $getVehiclesList
     * @return mixed
     */
    public function getDeviceTempAndHumiditySensorsSummaryByVehicle(
        $data,
        bool $getVehiclesList = false,
        bool $getSensorsList = false,
        bool $chart = false
    ) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $vehicleDefaultLabel = $data['vehicleDefaultLabel'];
        $vehicleRegNo = $data['vehicleRegNo'];
        $vehicleGroup = $data['vehicleGroup'];
        $vehicleDepot = $data['vehicleDepot'];
        $depotId = $data['depotId'];
        $noDepot = $data['noDepot'];
        $noGroups = $data['noGroups'];
        $vehicleId = $data['vehicleId'];
        $vehicleIds = $data['vehicleIds'];
        $sensorId = $data['sensorId'];
        $teamId = $data['teamId'];
        $order = $data['order'];
        $sort = $this->mapSortForVehicleList($data['sort']);

        $trackerHistorySensorTable = $em->getClassMetadata(TrackerHistorySensor::class)->getTableName();
        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $deviceSensorTable = $em->getClassMetadata(DeviceSensor::class)->getTableName();
        $deviceSensorTypeTable = $em->getClassMetadata(DeviceSensorType::class)->getTableName();
        $rTable = $em->getClassMetadata(Route::class)->getTableName();
        $vTable = $em->getClassMetadata(Vehicle::class)->getTableName();
        $sensorTable = $em->getClassMetadata(Sensor::class)->getTableName();

        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->from($trackerHistorySensorTable, 'ths')
            ->leftJoin('ths', $deviceSensorTable, 'ds', 'ths.device_sensor_id = ds.id')
            ->leftJoin('ths', $vTable, 'v', 'ths.vehicle_id = v.id')
            ->leftJoin('ds', $sensorTable, 's', 'ds.sensor_id = s.id')
            ->leftJoin('s', $deviceSensorTypeTable, 'dst', 's.type_id = dst.id')
            ->setParameter('dateFrom', $data['startDate'])
            ->setParameter('dateTo', $data['endDate'])
            ->setParameter('type', Route::TYPE_DRIVING)
            ->setParameter('stopped', Route::TYPE_STOP, Types::STRING);

        if ($getVehiclesList) {
            $query->select('v.id')->groupBy('v.id');
        } elseif ($getSensorsList) {
            $query->addSelect('s.id')
                ->addSelect('s.sensor_id AS device_sensor_ble_id')
                ->addSelect('s.label AS device_sensor_label')
                ->groupBy('s.id');
        } elseif ($chart) {
            $query->addSelect('to_char(ths.occurred_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') as occurred_at')
                ->addSelect('(ths.data->>\'temperature\')::float AS temperature')
                ->orderBy($sort, $order);
        } else {
            $query->addSelect('v.regno')
                ->addSelect('to_char(ths.occurred_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') as occurred_at')
                ->addSelect('s.sensor_id AS device_sensor_ble_id')
                ->addSelect('s.label AS device_sensor_label')
                ->addSelect(
                    'ths.id, ths.vehicle_id, ths.device_id, ths.device_sensor_id, (ths.data->>\'temperature\')::float AS temperature, (ths.data->>\'humidity\')::float AS humidity, (ths.data->>\'sensorBatteryPercentage\')::int AS battery_percentage, (ths.data->>\'ambientLightStatus\')::int AS ambient_light_status'
                )
                ->addSelect('s.type_id AS device_sensor_type')
                ->addSelect(
                    'NULLIF(CONCAT_WS(\'\', NULLIF(CONCAT(ths.data->>\'temperature\', \'°C, \'), \'°C, \'), NULLIF(CONCAT((ths.data->>\'humidity\')::float, \'%, \'), \'%, \'), (ths.data->>\'ambientLightStatus\')::int), \'\') AS value'
                )
                ->addSelect('dst.label AS device_sensor_type_label')
                ->addSelect('v.defaultlabel, v.model')
                ->orderBy($sort, $order);

            $query = $this->handlePositionSubQueryByVehicle(
                $data, $em, $query, $qb, $rTable, $trackerHistorySensorTable, $trackerHistoryTable, $vehicleIds
            );
        }

//        if ($data['limit'] ?? null) {
//            $query->setMaxResults($data['limit']);
//        }

        $query->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte('ths.occurred_at', ':dateTo'),
                $qb->expr()->gte('ths.occurred_at', ':dateFrom')
            )
        )
            ->andWhere('ths.is_nullable_data = false')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('dst.name', ':tempAndHumidityName'),
                    $qb->expr()->eq('dst.name', ':trackingBeaconName')
                )
            )
            ->setParameter('tempAndHumidityName', DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE)
            ->setParameter('trackingBeaconName', DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE);

        if ($vehicleDefaultLabel) {
            if (is_array($vehicleDefaultLabel)) {
                $q = [];
                foreach ($vehicleDefaultLabel as $index => $label) {
                    $q[] = $qb->expr()->like('LOWER(v.defaultLabel)', ":label$index");
                    $query->setParameter("label$index", strtolower($label) . '%');
                }
                $query->andWhere(new Orx($q));
            } else {
                $query->andWhere('LOWER(v.defaultLabel) LIKE LOWER(:defaultLabel)')
                    ->setParameter('defaultLabel', $vehicleDefaultLabel . '%');
            }
        }

        if ($vehicleRegNo) {
            $query->andWhere('LOWER(v.regno) LIKE LOWER(:regNo)')->setParameter('regNo', $vehicleRegNo . '%');
        }
        if ($vehicleDepot) {
            $query->andWhere('depot_id = :depotId')->setParameter('depotId', $vehicleDepot);
        }
        if ($vehicleId) {
            $query->andWhere('ths.vehicle_id = :vehicleId')->setParameter('vehicleId', $vehicleId);
        }
        if ($vehicleIds) {
            $query->andWhere('ths.vehicle_id IN (' . implode(', ', $vehicleIds) . ')');
        }
        if ($sensorId) {
            $query->andWhere('s.id = :sensorId')->setParameter('sensorId', $sensorId);
        }

        $query = $this->addDepotIdFilter($query, $depotId, $noDepot);
        $query = $this->addGroupsFilter($query, $vehicleGroup, $noGroups);

        if ($teamId) {
            $query->andWhere('v.team_id IN (' . implode(', ', $teamId) . ')')->setParameter('teamId', $teamId);
        }

        return $query;
    }

    /**
     * @param $data
     * @param bool $getSensorsList
     * @return mixed
     */
    public function getDeviceTempAndHumiditySensorsSummaryBySensor($data, $getSensorsList = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $dateFrom = $data['startDate'];
        $dateTo = $data['endDate'];
        $sensorId = $data['sensorId'];
        $sensorIds = $data['sensorIds'];
        $sensorLabel = $data['label'];
        $sensorBLEId = $data['sensorBLEId'];
        $order = $data['order'];
        $teamId = $data['teamId'];
        $sort = $this->mapSortForSensorList($data['sort']);

        $trackerHistorySensorTable = $em->getClassMetadata(TrackerHistorySensor::class)->getTableName();
        $trackerHistoryTable = $em->getClassMetadata(TrackerHistory::class)->getTableName();
        $deviceSensorTable = $em->getClassMetadata(DeviceSensor::class)->getTableName();
        $deviceSensorTypeTable = $em->getClassMetadata(DeviceSensorType::class)->getTableName();
        $rTable = $em->getClassMetadata(Route::class)->getTableName();
        $sensorTable = $em->getClassMetadata(Sensor::class)->getTableName();
        $vTable = $em->getClassMetadata(Vehicle::class)->getTableName();

        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->from($trackerHistorySensorTable, 'ths')
            ->leftJoin('ths', $deviceSensorTable, 'ds', 'ths.device_sensor_id = ds.id')
            ->leftJoin('ds', $sensorTable, 's', 'ds.sensor_id = s.id')
            ->leftJoin('s', $deviceSensorTypeTable, 'dst', 's.type_id = dst.id')
            ->leftJoin('ths', $vTable, 'v', 'ths.vehicle_id = v.id')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->setParameter('type', Route::TYPE_DRIVING)
            ->setParameter('stopped', Route::TYPE_STOP, Types::STRING);

        if ($getSensorsList) {
            $query->select('s.id')->groupBy('s.id');
        } else {
            $query
                ->addSelect('s.sensor_id AS device_sensor_ble_id')
                ->addSelect('s.label AS device_sensor_label')
                ->addSelect('to_char(ths.occurred_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS occurred_at')
                ->addSelect('v.regno')
                ->addSelect(
                    'ths.id, ths.device_sensor_id, ths.vehicle_id, ths.device_id, (ths.data->>\'temperature\')::float AS temperature, (ths.data->>\'humidity\')::float AS humidity, (ths.data->>\'sensorBatteryPercentage\')::int AS battery_percentage, (ths.data->>\'ambientLightStatus\')::int AS ambient_light_status'
                )
                ->addSelect(
                    'NULLIF(CONCAT_WS(\'\', NULLIF(CONCAT(ths.data->>\'temperature\', \'°C, \'), \'°C, \'), NULLIF(CONCAT((ths.data->>\'humidity\')::float, \'%, \'), \'%, \'), (ths.data->>\'ambientLightStatus\')::int), \'\') AS value'
                )
                ->addSelect('s.type_id AS device_sensor_type')
                ->addSelect('dst.label AS device_sensor_type_label')
                ->addSelect('v.defaultlabel')
                ->orderBy($sort, $order);

            $query = $this->handlePositionSubQueryBySensor(
                $data, $em, $query, $qb, $rTable, $trackerHistorySensorTable, $trackerHistoryTable
            );
        }

        $query->andWhere(
            $qb->expr()->andX(
                $qb->expr()->lte('ths.occurred_at', ':dateTo'),
                $qb->expr()->gte('ths.occurred_at', ':dateFrom')
            )
        )
            ->andWhere('ths.is_nullable_data = false')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('dst.name', ':tempAndHumidityName'),
                    $qb->expr()->eq('dst.name', ':trackingBeaconName')
                )
            )
            ->setParameter('tempAndHumidityName', DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE)
            ->setParameter('trackingBeaconName', DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE);

        if ($sensorLabel) {
            $query->andWhere('LOWER(s.label) LIKE LOWER(:sensorLabel)')
                ->setParameter('sensorLabel', $sensorLabel . '%');
        }
        if ($sensorBLEId) {
            $query->andWhere('LOWER(s.sensor_id) LIKE LOWER(:sensorBLEId)')
                ->setParameter('sensorBLEId', $sensorBLEId . '%');
        }
        if ($sensorId) {
            $query->andWhere('s.id = :sensorId')->setParameter('sensorId', $sensorId);
        }
        if ($sensorIds) {
            $query->andWhere('s.id IN (' . implode(', ', $sensorIds) . ')');
        }
        if ($teamId) {
            $query->andWhere('s.team_id IN (' . implode(', ', $teamId) . ')')->setParameter('teamId', $teamId);
        }

        return $query;
    }

    /**
     * @param Sensor $sensor
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function getTrackerHistoriesSensorBySensor(
        Sensor $sensor,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ) {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select(['IDENTITY(ds.device) AS deviceId', 'ths.occurredAt'])
            ->from(DeviceSensor::class, 'ds')
            ->leftJoin('ds.trackerHistoriesSensor', 'ths')
            ->where('ds.sensor = :sensor')
            ->andWhere('ths.occurredAt >= :dateFrom')
            ->andWhere('ths.occurredAt <= :dateTo')
            ->setParameter('sensor', $sensor)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->orderBy('ths.occurredAt', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }
}
