<?php

namespace App\Repository;

use App\Entity\Depot;
use App\Entity\Device;
use App\Entity\Route;
use App\Entity\Speeding;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Util\ArrayHelper;
use App\Util\StringHelper;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use RuntimeException;

class SpeedingRepository extends EntityRepository
{
    /**
     * @param int $deviceId
     * @param $startDate
     * @return Speeding|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastSpeedingStartedFromDate(int $deviceId, $startDate): ?Speeding
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $qb->select('s')
            ->from(Speeding::class, 's')
            ->where($qb->expr()->eq('IDENTITY(s.device)', $deviceId))
            ->andWhere('s.finishedAt <= :startDate OR s.startedAt < :startDate')
            ->setParameter('startDate', $startDate)
            ->orderBy('s.startedAt', Criteria::DESC)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param int $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @return int|null
     */
    public function removeNewestSpeedingFromDate(int $deviceId, $dateFrom, $dateTo = null): ?int
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $query = $qb->delete(Speeding::class, 's')
            ->where($qb->expr()->eq('IDENTITY(s.device)', $deviceId))
            ->andWhere('s.startedAt >= :dateFrom')
            ->setParameter('dateFrom', $dateFrom);

        if ($dateTo) {
            $query->andWhere('s.startedAt < :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $result = $query
            ->getQuery()
            ->execute();

        return $result;
    }

    /**
     * @param int $deviceId
     * @param $dateFrom
     * @return \DateTime|null
     */
    public function getNewestSecondSpeedingTSWithTypeFromDate(int $deviceId, $dateFrom): ?\DateTime
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $result = $qb->select('s.startedAt')
            ->from(Speeding::class, 's')
            ->where($qb->expr()->eq('IDENTITY(s.device)', $deviceId))
            ->andWhere('s.startedAt > :dateFrom')
            ->setParameter('dateFrom', $dateFrom)
            ->orderBy('s.startedAt', Criteria::ASC)
            ->getQuery()
            ->setMaxResults(2)
            ->getResult();

        return isset($result[1]) ? $result[1]['startedAt'] : null;
    }

    /**
     * @param int $vehicleId
     * @return \DateTime|null
     */
    public function getVehicleSpeeding(int $vehicleId): ?array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $qb->select('s')
            ->from(Speeding::class, 's')
            ->where($qb->expr()->eq('IDENTITY(s.vehicle)', $vehicleId))
            ->orderBy('s.startedAt', Criteria::ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $params
     * @return \Doctrine\DBAL\Query\QueryBuilder
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getSpeedingByParams(array $params)
    {
        $connection = $this->getEntityManager()->getConnection();

        $vehicleQb = $connection->createQueryBuilder()
            ->select(
                [
                    'unnest(:_ids::INTEGER[]) AS id',
                    'unnest(:_models::VARCHAR[]) AS model',
                    'unnest(:_defaultLabels::VARCHAR[]) AS default_label',
                    'unnest(:_regNumbers::VARCHAR[]) AS reg_no',
                    'unnest(:_depots::INTEGER[]) AS depot_id',
                    'unnest(:_ecoSpeeds::VARCHAR[]) AS eco_speed',
                ]
            );

        if (!$this->getEntityManager()->getClassMetadata(VehicleGroup::class)->hasAssociation('vehicles')) {
            throw new RuntimeException(
                sprintf('The class %s doesn\'t contain association "vehicle".', VehicleGroup::class)
            );
        }

        $groupQb = $connection->createQueryBuilder();
        $groupQb->select(
            [
                'v.id as vehicle_id',
                'string_agg(vg.name, \', \') as vehicle_group_names_as_string',
            ]
        )
            ->from(sprintf('(%s)', $vehicleQb->getSQL()), 'v')
            ->innerJoin(
                'v',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getAssociationMapping(
                    'vehicles'
                )['joinTable']['name'],
                'vgs',
                'v.id = vgs.vehicle_id'
            )
            ->innerJoin(
                'vgs',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getTableName(),
                'vg',
                'vg.id = vgs.vehicle_group_id'
            )
            ->groupBy('v.id');

        $flattenedVehicles = $this->getFlattenedVehicles($params['vehicles']);

        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                's.id',
                'v.default_label AS default_label',
                'v.reg_no AS reg_no',
                'v.model',
                'CONCAT_WS(\' \', u.name, u.surname) AS driver',
                'u.id as driver_id',
                'vg.vehicle_group_names_as_string AS groups',
                'vd.name AS depot',
                'to_char(s.started_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS started_at',
                'to_char(s.finished_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS finished_at',
                's.distance',
                's.avg_speed AS avg_speed',
                's.max_speed AS max_speed',
                's.eco_speed as posted_limit'
            ]
        )
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->innerJoin('s', sprintf('(%s)', $vehicleQb->getSQL()), 'v', 's.vehicle_id=v.id')
            ->leftJoin(
                's',
                $this->getEntityManager()->getClassMetadata(User::class)->getTableName(),
                'u',
                'u.id=s.driver_id'
            )
            ->leftJoin('v', sprintf('(%s)', $groupQb->getSQL()), 'vg', 'vg.vehicle_id=v.id')
            ->leftJoin(
                'v',
                $this->getEntityManager()->getClassMetadata(Depot::class)->getTableName(),
                'vd',
                'v.depot_id = vd.id'
            )
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'id'), $params['order'] ?? 'ASC')
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('_ids', $this->formatPostgresArrayString($flattenedVehicles['ids']))
            ->setParameter('_models', $this->formatPostgresArrayString($flattenedVehicles['models']))
            ->setParameter('_defaultLabels', $this->formatPostgresArrayString($flattenedVehicles['defaultLabels']))
            ->setParameter('_depots', $this->formatPostgresArrayString($flattenedVehicles['depots']))
            ->setParameter('_regNumbers', $this->formatPostgresArrayString($flattenedVehicles['regNumbers']))
            ->setParameter('_ecoSpeeds', $this->formatPostgresArrayString($flattenedVehicles['ecoSpeeds']));

        if (isset($params['driver'])) {
            $qb->andWhere('LOWER(CONCAT_WS(\' \', u.name, u.surname)) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        if ($params['driverId'] ?? null) {
            $qb->andWhere('u.id IN (:driverId)')
                ->setParameter('driverId', $params['driverId'], Connection::PARAM_INT_ARRAY);
        }

        return $qb;
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public function getCountSpeedingByParams(array $params): int
    {
        $connection = $this->getEntityManager()->getConnection();

        $vehicleQb = $connection->createQueryBuilder()
            ->select('unnest(:_ids::INTEGER[]) AS id');

        $flattenedVehicles = $this->getFlattenedVehicles($params['vehicles']);

        $qb = $connection->createQueryBuilder();
        $qb->select('COUNT(s.id)')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->innerJoin('s', sprintf('(%s)', $vehicleQb->getSQL()), 'v', 's.vehicle_id=v.id')
            ->leftJoin(
                's',
                $this->getEntityManager()->getClassMetadata(User::class)->getTableName(),
                'u',
                'u.id=s.driver_id'
            )
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('_ids', $this->formatPostgresArrayString($flattenedVehicles['ids']));

        if (isset($params['driver'])) {
            $qb->andWhere('LOWER(CONCAT_WS(\' \', u.name, u.surname)) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        $result = $qb->execute()->fetch();

        if (!$result) {
            return 0;
        }

        return array_shift($result);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getGroupedVehicles(array $params): array
    {
        $flattenedVehicles = $this->getFlattenedVehicles($params['vehicles']);

        $connection = $this->getEntityManager()
            ->getConnection();

        $vehicleQb = $connection->createQueryBuilder()
            ->select(
                [
                    'unnest(:_ids::INTEGER[]) AS id',
                    'unnest(:_models::VARCHAR[]) AS model',
                    'unnest(:_defaultLabels::VARCHAR[]) AS default_label',
                    'unnest(:_regNumbers::VARCHAR[]) AS reg_no',
                    'unnest(:_depots::INTEGER[]) AS depot_id',
                ]
            )
            ->setParameter('_ids', $this->formatPostgresArrayString($flattenedVehicles['ids']))
            ->setParameter('_models', $this->formatPostgresArrayString($flattenedVehicles['models']))
            ->setParameter('_defaultLabels', $this->formatPostgresArrayString($flattenedVehicles['defaultLabels']))
            ->setParameter('_depots', $this->formatPostgresArrayString($flattenedVehicles['depots']))
            ->setParameter('_regNumbers', $this->formatPostgresArrayString($flattenedVehicles['regNumbers']));

        if (!$this->getEntityManager()->getClassMetadata(VehicleGroup::class)->hasAssociation('vehicles')) {
            throw new RuntimeException(
                sprintf('The class %s doesn\'t contain association "vehicle".', VehicleGroup::class)
            );
        }

        $groupQb = $connection->createQueryBuilder();
        $groupQb->select(
            [
                'v.id as vehicle_id',
                'string_agg(vg.name, \', \') as vehicle_group_names_as_string',
            ]
        )
            ->from(sprintf('(%s)', $vehicleQb->getSQL()), 'v')
            ->innerJoin(
                'v',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getAssociationMapping(
                    'vehicles'
                )['joinTable']['name'],
                'vgs',
                $groupQb->expr()->eq('v.id', 'vgs.vehicle_id')
            )
            ->innerJoin(
                'vgs',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getTableName(),
                'vg',
                $groupQb->expr()->eq('vg.id', 'vgs.vehicle_group_id')
            )
            ->groupBy('v.id');

        $speedingQb = $connection->createQueryBuilder();
        $speedingQb->select('s.vehicle_id')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->where(
                $speedingQb->expr()->andX(
                    $speedingQb->expr()->gte('s.started_at', ':dateFrom'),
                    $speedingQb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->groupBy('s.vehicle_id')
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        if (isset($params['driver'])) {
            $speedingQb->innerJoin(
                's',
                $this->getEntityManager()->getClassMetadata(User::class)->getTableName(),
                'u',
                $speedingQb->expr()->eq('s.driver_id', 'u.id')
            )
                ->andWhere('LOWER(CONCAT_WS(\' \', u.name, u.surname)) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                'v.id',
                'v.default_label',
                'v.model',
                'v.reg_no',
                'vd.name AS depot',
                'vg.vehicle_group_names_as_string AS groups',
            ]
        )
            ->from(sprintf('(%s)', $vehicleQb->getSQL()), 'v')
            ->innerJoin('v', sprintf('(%s)', $speedingQb->getSQL()), 's', $qb->expr()->eq('s.vehicle_id', 'v.id'))
            ->leftJoin('v', sprintf('(%s)', $groupQb->getSQL()), 'vg', $qb->expr()->eq('vg.vehicle_id', 'v.id'))
            ->leftJoin(
                'v',
                $this->getEntityManager()->getClassMetadata(Depot::class)->getTableName(),
                'vd',
                'v.depot_id = vd.id'
            )
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'id'), $params['order'] ?? 'ASC')
            ->setMaxResults($params['limit'])
            ->setFirstResult($params['offset']);

        $parameters = $speedingQb->getParameters() + $vehicleQb->getParameters();
        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        $result = $qb->execute()
            ->fetchAll();

        return ArrayHelper::keysToCamelCase($result);
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public function getTotalOfGroupedVehicles(array $params): int
    {
        $flattenedVehicles = $this->getFlattenedVehicles($params['vehicles']);

        $connection = $this->getEntityManager()
            ->getConnection();

        $vehicleQb = $connection->createQueryBuilder()
            ->select(
                [
                    'unnest(:_ids::INTEGER[]) AS id',
                    'unnest(:_models::VARCHAR[]) AS model',
                    'unnest(:_defaultLabels::VARCHAR[]) AS default_label',
                    'unnest(:_regNumbers::VARCHAR[]) AS reg_no',
                    'unnest(:_depots::INTEGER[]) AS depot_id',
                ]
            )
            ->setParameter('_ids', $this->formatPostgresArrayString($flattenedVehicles['ids']))
            ->setParameter('_models', $this->formatPostgresArrayString($flattenedVehicles['models']))
            ->setParameter('_defaultLabels', $this->formatPostgresArrayString($flattenedVehicles['defaultLabels']))
            ->setParameter('_depots', $this->formatPostgresArrayString($flattenedVehicles['depots']))
            ->setParameter('_regNumbers', $this->formatPostgresArrayString($flattenedVehicles['regNumbers']));

        if (!$this->getEntityManager()->getClassMetadata(VehicleGroup::class)->hasAssociation('vehicles')) {
            throw new RuntimeException(
                sprintf('The class %s doesn\'t contain association "vehicle".', VehicleGroup::class)
            );
        }

        $speedingQb = $connection->createQueryBuilder();
        $speedingQb->select('s.vehicle_id')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->where(
                $speedingQb->expr()->andX(
                    $speedingQb->expr()->gte('s.started_at', ':dateFrom'),
                    $speedingQb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->groupBy('s.vehicle_id')
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        if (isset($params['driver'])) {
            $speedingQb->innerJoin(
                's',
                $this->getEntityManager()->getClassMetadata(User::class)->getTableName(),
                'u',
                $speedingQb->expr()->eq('s.driver_id', 'u.id')
            )
                ->andWhere('LOWER(CONCAT_WS(\' \', u.name, u.surname)) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        $qb = $connection->createQueryBuilder();
        $qb->select('COUNT(v.id)')
            ->from(sprintf('(%s)', $vehicleQb->getSQL()), 'v')
            ->innerJoin('v', sprintf('(%s)', $speedingQb->getSQL()), 's', $qb->expr()->eq('s.vehicle_id', 'v.id'));

        $parameters = $speedingQb->getParameters() + $vehicleQb->getParameters();
        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        $result = $qb->execute()
            ->fetch();

        if (!$result) {
            return 0;
        }

        return array_shift($result);
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public function getTotalOfSpeedingByVehicle(array $params): int
    {
        $connection = $this->getEntityManager()
            ->getConnection();

        $qb = $connection->createQueryBuilder();
        $qb->select('COUNT(s.id)')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->where($qb->expr()->eq('s.vehicle_id', ':vehicleId'))
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('vehicleId', $params['vehicleId']);

        $result = $qb->execute()
            ->fetch();

        if (!$result) {
            return 0;
        }

        return array_shift($result);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getSpeedingByVehicle(array $params): array
    {
        $connection = $this->getEntityManager()
            ->getConnection();

        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                's.id',
                's.avg_speed AS avg_speed',
                's.max_speed AS max_speed',
                'to_char(s.started_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS started_at',
                'to_char(s.finished_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS finished_at',
                's.distance',
                'CONCAT_WS(\' \', u.name, u.surname) AS driver',
                's.eco_speed as posted_limit',
            ]
        )
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->leftJoin(
                's',
                $this->getEntityManager()->getClassMetadata(User::class)->getTableName(),
                'u',
                $qb->expr()->eq('s.driver_id', 'u.id')
            )
            ->where($qb->expr()->eq('s.vehicle_id', ':vehicleId'))
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate'])
            ->setParameter('vehicleId', $params['vehicleId']);

        $result = $qb->execute()
            ->fetchAll();

        return ArrayHelper::keysToCamelCase($result);
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return array
     */
    public function getSpeedingsGroupedByDriver(array $params, User $user): array
    {
        $connection = $this->getEntityManager()
            ->getConnection();

        $flattenedVehicles = $this->getFlattenedUser($params['users']);

        $userQb = $connection->createQueryBuilder()
            ->select(
                [
                    'unnest(:_ids::INTEGER[]) AS id',
                    'unnest(:_names::VARCHAR[]) AS name',
                    'unnest(:_surnames::VARCHAR[]) AS surname',
                ]
            )
            ->setParameter('_ids', $this->formatPostgresArrayString($flattenedVehicles['ids']))
            ->setParameter('_names', $this->formatPostgresArrayString($flattenedVehicles['names']))
            ->setParameter('_surnames', $this->formatPostgresArrayString($flattenedVehicles['surnames']));

        $speedingQb = $connection->createQueryBuilder();
        $speedingQb
            ->select('s.driver_id')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->innerJoin('s', sprintf('(%s)', $userQb->getSQL()), 'u', $speedingQb->expr()->eq('u.id', 's.driver_id'))
            ->where(
                $speedingQb->expr()->andX(
                    $speedingQb->expr()->gte('s.started_at', ':dateFrom'),
                    $speedingQb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->groupBy('s.driver_id')
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $speedingQb->andWhere('s.vehicle_id in (' . implode(', ', $userVehicles) . ')');
        }

        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                'u.id AS id',
                'CONCAT_WS(\' \', u.name, u.surname) AS driver',
            ]
        )
            ->from(sprintf('(%s)', $speedingQb->getSQL()), 's')
            ->innerJoin(
                's',
                $this->getEntityManager()->getClassMetadata(User::class)->getTableName(),
                'u',
                $qb->expr()->eq('u.id', 's.driver_id')
            )
            ->groupBy('u.id')
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'id'), $params['order'] ?? 'ASC')
            ->setMaxResults($params['limit'])
            ->setFirstResult($params['offset']);

        if (isset($params['driver'])) {
            $qb->andWhere('LOWER(CONCAT_WS(\' \', u.name, u.surname)) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        $parameters = $speedingQb->getParameters() + $userQb->getParameters();
        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        return $qb->execute()
            ->fetchAll();
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return int
     */
    public function getTotalOfSpeedingsGroupedByDriver(array $params, User $user): int
    {
        $connection = $this->getEntityManager()
            ->getConnection();

        $flattenedVehicles = $this->getFlattenedUser($params['users']);

        $userQuery = $connection->createQueryBuilder()
            ->select(
                [
                    'unnest(:_ids::INTEGER[]) AS id',
                    'unnest(:_names::VARCHAR[]) AS name',
                    'unnest(:_surnames::VARCHAR[]) AS surname',
                ]
            )
            ->setParameter('_ids', $this->formatPostgresArrayString($flattenedVehicles['ids']))
            ->setParameter('_names', $this->formatPostgresArrayString($flattenedVehicles['names']))
            ->setParameter('_surnames', $this->formatPostgresArrayString($flattenedVehicles['surnames']));

        $subQb = $connection->createQueryBuilder();
        $subQb->select('s.driver_id')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->innerJoin('s', sprintf('(%s)', $userQuery->getSQL()), 'u', $subQb->expr()->eq('u.id', 's.driver_id'))
            ->where(
                $subQb->expr()->andX(
                    $subQb->expr()->gte('s.started_at', ':dateFrom'),
                    $subQb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->groupBy('s.driver_id')
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        if (isset($params['driver'])) {
            $subQb->andWhere('LOWER(CONCAT_WS(\' \', u.name, u.surname)) LIKE LOWER(:driver)')
                ->setParameter('driver', $params['driver'] . '%');
        }

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $subQb->andWhere('s.vehicle_id in (' . implode(', ', $userVehicles) . ')');
        }

        $qb = $connection->createQueryBuilder();
        $qb->select('COUNT(s.driver_id)')
            ->from(sprintf('(%s)', $subQb->getSQL()), 's');

        $parameters = $userQuery->getParameters() + $subQb->getParameters();
        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        $result = $qb->execute()
            ->fetch();

        if (!$result) {
            return 0;
        }

        return array_shift($result);
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getSpeedingByDriver(array $params, User $user): array
    {
        $connection = $this->getEntityManager()
            ->getConnection();


        $vehicleQb = $connection->createQueryBuilder();
        $vehicleQb->select('s.vehicle_id')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->where($vehicleQb->expr()->eq('s.driver_id', ':driverId'))
            ->andWhere(
                $vehicleQb->expr()->andX(
                    $vehicleQb->expr()->gte('s.started_at', ':dateFrom'),
                    $vehicleQb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->groupBy('s.vehicle_id')
            ->setParameter('driverId', $params['driverId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        $groupQb = $connection->createQueryBuilder();
        $groupQb->select(
            [
                'v.vehicle_id as vehicle_id',
                'string_agg(vg.name, \', \') as vehicle_group_names_as_string',
            ]
        )
            ->from(sprintf('(%s)', $vehicleQb->getSQL()), 'v')
            ->innerJoin(
                'v',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getAssociationMapping(
                    'vehicles'
                )['joinTable']['name'],
                'vgs',
                'v.vehicle_id = vgs.vehicle_id'
            )
            ->innerJoin(
                'vgs',
                $this->getEntityManager()->getClassMetadata(VehicleGroup::class)->getTableName(),
                'vg',
                $groupQb->expr()->eq('vg.id', 'vgs.vehicle_group_id')
            )
            ->groupBy('v.vehicle_id');

        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                's.id AS id',
                's.avg_speed AS avg_speed',
                's.max_speed AS max_speed',
                'to_char(s.started_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS started_at',
                'to_char(s.finished_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS finished_at',
                's.distance AS distance',
                'v.model AS model',
                'v.defaultlabel AS default_label',
                'v.regno AS reg_no',
                'vg.vehicle_group_names_as_string AS groups',
                'vd.name AS depot',
                's.eco_speed as posted_limit'
            ]
        )
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->leftJoin(
                's',
                $this->getEntityManager()->getClassMetadata(Vehicle::class)->getTableName(),
                'v',
                $qb->expr()->eq('v.id', 's.vehicle_id')
            )
            ->leftJoin('s', sprintf('(%s)', $groupQb->getSQL()), 'vg', $qb->expr()->eq('vg.vehicle_id', 's.vehicle_id'))
            ->leftJoin(
                'v',
                $this->getEntityManager()->getClassMetadata(Depot::class)->getTableName(),
                'vd',
                $qb->expr()->eq('v.depot_id', 'vd.id')
            )
            ->where($qb->expr()->eq('s.driver_id', ':driverId'))
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->orderBy(StringHelper::toSnakeCase($params['sort'] ?? 'id'), $params['order'] ?? 'ASC')
            ->setMaxResults($params['limit'])
            ->setFirstResult($params['offset'])
            ->setParameter('driverId', $params['driverId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $qb->andWhere('s.vehicle_id in (' . implode(', ', $userVehicles) . ')');
        }

        $result = $qb->execute()
            ->fetchAll();

        return ArrayHelper::keysToCamelCase($result);
    }

    /**
     * @param array $params
     *
     * @param User $user
     * @return int
     */
    public function getTotalOfSpeedingByDriver(array $params, User $user): int
    {
        $connection = $this->getEntityManager()
            ->getConnection();


        $qb = $connection->createQueryBuilder();
        $qb->select('COUNT(s.id)')
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->where($qb->expr()->eq('s.driver_id', ':driverId'))
            ->setParameter('driverId', $params['driverId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        if ($user->needToCheckUserGroup()) {
            $userVehicles = $this->getEntityManager()->getRepository(UserGroup::class)
                ->getUserVehiclesIdFromUserGroup($user);
            $qb->andWhere('s.vehicle_id in (' . implode(', ', $userVehicles) . ')');
        }

        $result = $qb->execute()
            ->fetch();

        if (!$result) {
            return 0;
        }

        return array_shift($result);
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
            $result['models'][] = $vehicle->getModel();
            $result['defaultLabels'][] = $vehicle->getDefaultLabel();
            $result['regNumbers'][] = $vehicle->getRegNo();
            $result['ecoSpeeds'][] = $vehicle->getEcoSpeed();
            // Set ID to "-1" (non-existing ID) for proper casting in PostgresQL
            $result['depots'][] = $vehicle->getDepot() ? $vehicle->getDepot()->getId() : -1;
        }

        return $result;
    }

    /**
     * @param \App\Entity\User[] $users
     *
     * @return array
     */
    protected function getFlattenedUser(array $users): array
    {
        $result = [];
        foreach ($users as $user) {
            $result['ids'][] = $user->getId();
            $result['names'][] = $user->getName();
            $result['surnames'][] = $user->getSurname();
        }

        return $result;
    }

    /**
     * @param $values
     * @return string
     */
    protected function formatPostgresArrayString($values)
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
     * @param array $params
     *
     * @return array
     */
    public function getSpeedingSetByDriver(array $params): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $qb = $connection->createQueryBuilder();
        $qb->select(
            [
                's.id',
                'EXTRACT(EPOCH FROM (s.finished_at - s.started_at))::INT as duration',
                's.avg_speed',
                's.distance as total_distance',
                'to_char(s.started_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS start_date',
                'to_char(s.finished_at, \'YYYY-MM-DD"T"HH24:MI:SS"+00:00"\') AS end_date',
                'STRING_AGG(th.lat || \' \' || th.lng, \', \' order by th.ts) as coordinates',
            ]
        )
            ->from($this->getEntityManager()->getClassMetadata(Speeding::class)->getTableName(), 's')
            ->leftJoin(
                's',
                $this->getEntityManager()->getClassMetadata(TrackerHistory::class)->getTableName(),
                'th',
                's.driver_id=th.driver_id AND th.ts BETWEEN s.started_at AND s.finished_at'
            )
            ->where($qb->expr()->eq('s.driver_id', ':driverId'))
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->gte('s.started_at', ':dateFrom'),
                    $qb->expr()->lte('s.finished_at', ':dateTo')
                )
            )
            ->groupBy('s.id')
            ->setParameter('driverId', $params['driverId'])
            ->setParameter('dateFrom', $params['startDate'])
            ->setParameter('dateTo', $params['endDate']);

        $result = $qb->execute()->fetchAll();

        return ArrayHelper::keysToCamelCase($result);
    }

    /**
     * @param Vehicle $vehicle
     * @param \DateTime $startedAt
     *
     * @return Query
     */
    public function getSpeedingByVehicleAndStartedDateQb(Vehicle $vehicle, \DateTime $startedAt): Query
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $qb->select('s')
            ->from(Speeding::class, 's')
            ->where($qb->expr()->eq('IDENTITY(s.vehicle)', ':vehicle'))
            ->andWhere($qb->expr()->gte('s.startedAt', ':startedAt'))
            ->setParameter('vehicle', $vehicle)
            ->setParameter('startedAt', $startedAt)
            ->getQuery();
    }

    /**
     * @param int $deviceId
     * @param \DateTime $startDate
     * @param \DateTime $finishDate
     * @param int $duration
     * @return mixed
     */
    public function getSpeedingByForEvents(
        int $deviceId,
        \DateTime $startDate,
        \DateTime $finishDate,
        int $duration
    ) {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $qb->select('s')
            ->from(Speeding::class, 's')
            ->where($qb->expr()->eq('IDENTITY(s.device)', ':deviceId'))
            ->andWhere($qb->expr()->gte('s.startedAt', ':startDate'))
            ->andWhere($qb->expr()->lte('s.finishedAt', ':finishDate'))
            ->andWhere('s.duration > :duration')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('duration', $duration)
            ->setParameter('startDate', $startDate)
            ->setParameter('finishDate', $finishDate)
            ->getQuery()->getResult();
    }

    public function updateSpeedingDriverByRoute(Route $route)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->update(Speeding::class, 's')
            ->set('s.driver', ':driver')
            ->andWhere('s.device = :device')
            ->andWhere('s.startedAt >= :startedAt')
            ->andWhere('s.finishedAt <= :finishedAt')
            ->setParameter('driver', $route->getDriver())
            ->setParameter('device', $route->getDevice())
            ->setParameter('startedAt', $route->getStartedAt())
            ->setParameter('finishedAt', $route->getFinishedAt());

        $query->getQuery()->execute();
    }
}
