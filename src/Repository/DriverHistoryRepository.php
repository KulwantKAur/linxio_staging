<?php

namespace App\Repository;

use App\Entity\DriverHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use Doctrine\Common\Collections\Criteria;

/**
 * DriverHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DriverHistoryRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Vehicle $vehicle
     * @param User $driver
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByUnfinishedHistory(Vehicle $vehicle, User $driver)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle = :vehicle')
            ->andWhere('dh.driver = :driver')
            ->andWhere('dh.finishDate IS NULL')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('driver', $driver)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function checkHistoryByDriverAndVehicle(User $driver, \DateTime $date)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.driver = :driver')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gt('dh.finishDate', ':date'),
                    $qb->expr()->gt('dh.startDate', ':date')
                )
            )
            ->setParameter('driver', $driver)
            ->setParameter('date', $date)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param Vehicle $vehicle
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLastHistoryByVehicle(Vehicle $vehicle)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle)
            ->orderBy('dh.startDate', Criteria::DESC)
            ->addOrderBy('dh.id', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUnfinishedLastHistoryByVehicle(Vehicle $vehicle)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle = :vehicle')
            ->andWhere('dh.finishDate IS NULL')
            ->setParameter('vehicle', $vehicle)
            ->orderBy('dh.startDate', Criteria::DESC)
            ->addOrderBy('dh.id', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Vehicle $vehicle
     * @param User $driver
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLastHistoryByVehicleAndNotTargetDriver(Vehicle $vehicle, User $driver)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle = :vehicle')
            ->andWhere('dh.driver <> :driver')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('driver', $driver)
            ->orderBy('dh.startDate', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $driver
     * @param User $currentUser
     * @param int $count
     * @return mixed
     */
    public function findByParams(User $driver, User $currentUser, ?int $count = 5)
    {
        $em = $driverHistory = $this->getEntityManager();

        $q = $em->createQueryBuilder()
            ->select('MAX(dh.startDate) as startDate')
            ->addSelect('current_driver.id as currentDriverId')
            ->addSelect('CONCAT(current_driver.name, \' \', current_driver.surname) as currentDriverFullName')
            ->addSelect(
                'CASE WHEN sum(case when dh.finishDate is null then 1 else 0 end) > 0 
                THEN :null 
                ELSE MAX(dh.finishDate) END 
                as finishDate'
            )
            ->addSelect('v.id as vehicleId')
            ->addSelect('CONCAT(v.make, \' \', v.makeModel) as model, v_type.name as type, v.regNo, v.defaultLabel, v.status')
            ->addSelect('CONCAT(dr.name, \' \', dr.surname) as fullName')
            ->from(DriverHistory::class, 'dh')
            ->join('dh.vehicle', 'v')
            ->leftJoin('v.type', 'v_type')
            ->leftJoin('v.driver', 'current_driver')
            ->join('dh.driver', 'dr')
            ->andWhere('dh.driver = :driver')
            ->andWhere('v.status in (:vehicleStatus)')
            ->setParameter('driver', $driver)
            ->setParameter('null', null)
            ->setParameter('vehicleStatus', Vehicle::ACTIVE_STATUSES_LIST)
            ->orderBy('startDate', Criteria::DESC)
            ->addGroupBy('vehicleId')
            ->addGroupBy('fullName')
            ->addGroupBy('current_driver.id')
            ->addGroupBy('v_type.name')
            ->setMaxResults($count);

        if ($currentUser->needToCheckUserGroup()) {
            $vehicleIds = $em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($currentUser);
            $q->andWhere('v.id in (:vehicleIds)')->setParameter('vehicleIds', $vehicleIds);
        }

        $history = $q->getQuery()->getResult();

        $vehicleIds = array_map(
            static function ($v) {
                return $v['vehicleId'];
            },
            $history
        );

        if (!$history) {
            return [];
        }

        $thTable = $em->getClassMetadata(TrackerHistoryLast::class)->getTableName();
        $thSubQuery = $em->getConnection()->createQueryBuilder();
        $thSubQuery
            ->select('__th.vehicle_id as vehicle_id')
            ->addSelect('MAX(__th.ts) as max_ts')
            ->from($thTable, '__th')
            ->groupBy('__th.vehicle_id')
            ->where($thSubQuery->expr()->in('__th.vehicle_id', $vehicleIds));

        $vehicleLastCoords = $this
            ->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->select('th.vehicle_id, th.lng, th.lat')
            ->from($thTable, 'th')
            ->innerJoin(
                'th',
                sprintf('(%s)', $thSubQuery->getSQL()),
                '_th',
                '_th.vehicle_id=th.vehicle_id AND _th.max_ts=th.ts'
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $vehicleLastCoordsMap = [];

        foreach ($vehicleLastCoords as $vehicleItem) {
            $vehicleLastCoordsMap[$vehicleItem['vehicle_id']] = [
                'lng' => $vehicleItem['lng'],
                'lat' => $vehicleItem['lat'],
            ];
        }

        foreach ($history as $kItem => $hItem) {
            if (isset($vehicleLastCoordsMap[$hItem['vehicleId']])) {
                $history[$kItem]['lng'] = $vehicleLastCoordsMap[$hItem['vehicleId']]['lng'];
                $history[$kItem]['lat'] = $vehicleLastCoordsMap[$hItem['vehicleId']]['lat'];
            } else {
                $history[$kItem]['lng'] = null;
                $history[$kItem]['lat'] = null;
            }
        }

        return $history;
    }

    /**
     * @param Vehicle $vehicle
     * @param $dateTime
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDriverByDateRange(Vehicle $vehicle, $dateTime)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle = :vehicle')
            ->andWhere('dh.startDate <= :dateTime')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('dateTime', $dateTime)
            ->orderBy('dh.startDate', Criteria::DESC);

        $query->andWhere(
            $query->expr()->orX(
                $query->expr()->gte('dh.finishDate', ':dateTime'),
                $query->expr()->isNull('dh.finishDate')
            )
        );

        return $query->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    public function findDriversByVehicleAndDate(int $vehicleId, $dateFrom, $dateTo): ?array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb->select('IDENTITY(dh.driver) as id')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('IDENTITY(dh.vehicle) = :vehicleId')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('dh.startDate', ':dateTo'),
                    $qb->expr()->orX(
                        $qb->expr()->gte('dh.finishDate', ':dateFrom'),
                        $qb->expr()->isNull('dh.finishDate')
                    )

                )
            )
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->groupBy('dh.driver');

        $result = $query->getQuery()->getResult();

        return $result ? array_column($result, 'id') : null;
    }

    public function findVehiclesByDriverAndDate(int $driverId, $dateFrom, $dateTo): ?array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb->select('IDENTITY(dh.vehicle) as id')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('IDENTITY(dh.driver) = :driverId')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('dh.startDate', ':dateTo'),
                    $qb->expr()->orX(
                        $qb->expr()->gte('dh.finishDate', ':dateFrom'),
                        $qb->expr()->isNull('dh.finishDate')
                    )

                )
            )
            ->setParameter('driverId', $driverId)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->groupBy('dh.vehicle');

        $result = $query->getQuery()->getResult();

        return $result ? array_column($result, 'id') : null;
    }

    public function findDriverHistoryByDateRange(Vehicle $vehicle, \DateTime $startDate, \DateTime $finishDate)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle = :vehicle')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('dh.startDate', ':dateTo'),
                    $qb->expr()->orX(
                        $qb->expr()->gte('dh.finishDate', ':dateFrom'),
                        $qb->expr()->isNull('dh.finishDate')
                    )

                )
            )
            ->setParameter('vehicle', $vehicle)
            ->setParameter('dateFrom', $startDate)
            ->setParameter('dateTo', $finishDate)->getQuery()->getResult();
    }

    public function findAnotherDHByDateRange(
        Vehicle $vehicle,
        User $driver,
        \DateTime $startDate,
        \DateTime $finishDate
    ) {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('dh')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('dh.vehicle <> :vehicle')
            ->andWhere('dh.driver = :driver')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->lte('dh.startDate', ':dateTo'),
                    $qb->expr()->orX(
                        $qb->expr()->gte('dh.finishDate', ':dateFrom'),
                        $qb->expr()->isNull('dh.finishDate')
                    )

                )
            )
            ->setParameter('vehicle', $vehicle)
            ->setParameter('driver', $driver)
            ->setParameter('dateFrom', $startDate)
            ->setParameter('dateTo', $finishDate)->getQuery()->getResult();
    }

    /**
     * @param int $driverId
     * @return array
     */
    public function findVehicleIdsByDriver(int $driverId): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('IDENTITY(dh.vehicle) as vehicleId')
            ->from(DriverHistory::class, 'dh')
            ->andWhere('IDENTITY(dh.driver) = :driverId')
            ->setParameter('driverId', $driverId)
            ->groupBy('dh.vehicle');
        $result = $query->getQuery()->getResult();

        return $result ? array_column($result, 'vehicleId') : [];
    }

    /**
     * @param int $driverId
     * @return int|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLastVehicleIdByDriver(int $driverId): ?int
    {
        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(dh.vehicle) as vehicleId')
            ->from(DriverHistory::class, 'dh')
            ->where('IDENTITY(dh.driver) = :driverId')
            ->orderBy('dh.startDate', Criteria::DESC)
            ->setParameter('driverId', $driverId)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $result ? $result['vehicleId'] : null;
    }
}
