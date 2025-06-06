<?php

namespace App\Repository\Tracker;

use App\Command\Tracker\UpdateWrongRoutesCommand;
use App\Entity\Route;
use App\Entity\RouteTemp;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\Service\Tracker\TrackerService;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;

/**
 * TrackerHistoryTempRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TrackerHistoryTempRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param bool|null $withMinDT
     * @param bool|null $withMaxDT
     * @return null|array
     */
    public function getNotCalculatedRoutesDeviceIds(
        ?bool $withMinDT = false,
        ?bool $withMaxDT = false
    ): ?array {
        $globalAllowedMinDT = Carbon::createFromTimestamp(TrackerService::getAllowedRecordTimestamp());
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT IDENTITY(tht.device) AS device_id')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.isCalculated = false')
            ->andWhere('tht.ts >= :globalAllowedMinDT')
            ->setParameter('globalAllowedMinDT', $globalAllowedMinDT);

        if ($withMinDT) {
            $allowedMinDT = (new Carbon())->subDays(UpdateWrongRoutesCommand::WRONG_ROUTES_FINISH_DAY_OFFSET);
            $query->andWhere('tht.ts >= :allowedMinDT')
                ->setParameter('allowedMinDT', $allowedMinDT);
        }

        if ($withMaxDT) {
            $allowedMaxDT = (new Carbon())->subDays(UpdateWrongRoutesCommand::WRONG_ROUTES_FINISH_DAY_OFFSET);
            $query->andWhere('tht.ts < :allowedMaxDT')
                ->setParameter('allowedMaxDT', $allowedMaxDT);
        }

        $result = $query->getQuery()->getResult();

        return $result ? array_column($result, 'device_id') : [];
    }

    /**
     * @param int $deviceId
     * @param bool|null $withMinDT
     * @param bool|null $withMaxDT
     * @return mixed|TrackerHistoryTemp
     */
    public function getNotCalculatedRoutesTrackerMaxAndMinRecords(
        int $deviceId,
        ?bool $withMinDT = false,
        ?bool $withMaxDT = false
    ): ?array {
        $globalAllowedMinDT = Carbon::createFromTimestamp(TrackerService::getAllowedRecordTimestamp());
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('MAX(tht.ts) AS max_ts, MIN(tht.ts) AS min_ts')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('IDENTITY(tht.device) = :deviceId')
            ->andWhere('tht.isCalculated = false')
            ->andWhere('tht.ts >= :globalAllowedMinDT')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('globalAllowedMinDT', $globalAllowedMinDT);

        if ($withMinDT) {
            $allowedMinDT = (new Carbon())->subDays(UpdateWrongRoutesCommand::WRONG_ROUTES_FINISH_DAY_OFFSET);
            $query->andWhere('tht.ts >= :allowedMinDT')
                ->setParameter('allowedMinDT', $allowedMinDT->toDateTimeString());
        }

        if ($withMaxDT) {
            $allowedMaxDT = (new Carbon())->subDays(UpdateWrongRoutesCommand::WRONG_ROUTES_FINISH_DAY_OFFSET);
            $query->andWhere('tht.ts < :allowedMaxDT')
                ->setParameter('allowedMaxDT', $allowedMaxDT->toDateTimeString());
        }

        $result = $query->getQuery()->getResult();

        return $result ? $result[0] : [];
    }

    /**
     * @param int $deviceId
     * @param $dateFrom
     * @param null $dateTo
     * @return \Doctrine\ORM\Query
     */
    public function getTrackerRecordsByDeviceQuery(int $deviceId, $dateFrom, $dateTo = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb->select('tht')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where($qb->expr()->eq('IDENTITY(tht.device)', $deviceId))
            ->andWhere('tht.ts >= :dateFrom')
            ->setParameter('dateFrom', $dateFrom)
            ->orderBy('tht.ts', Criteria::ASC);

        if ($dateTo) {
            $query->andWhere('tht.ts <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        return $query->getQuery();
    }

    /**
     * @param $deviceId
     * @return mixed|TrackerHistoryTemp
     */
    public function getNotCalculatedSpeedingTrackerMaxAndMinRecords($deviceId): ?array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $result = $qb->select('MAX(tht.ts) AS max_ts, MIN(tht.ts) AS min_ts')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where($qb->expr()->eq('IDENTITY(tht.device)', $deviceId))
            ->andWhere('tht.isCalculatedSpeeding = false')
            ->getQuery()
            ->getResult();

        return $result ? $result[0] : [];
    }

    /**
     * @return null|array
     */
    public function getNotCalculatedSpeedingDeviceIds(): ?array
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT IDENTITY(tht.device) AS device_id')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->andWhere('tht.isCalculatedSpeeding = false')
            ->getQuery()
            ->getResult();

        return $result ? array_column($result, 'device_id') : [];
    }

    /**
     * @return null|array
     */
    public function getNotCalculatedIdlingDeviceIds(): ?array
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT IDENTITY(tht.device) AS device_id')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.isCalculatedIdling = false')
            ->getQuery()
            ->getResult();

        return $result ? array_column($result, 'device_id') : [];
    }

    /**
     * @param $deviceId
     * @return array|null
     */
    public function getNotCalculatedIdlingTrackerMaxAndMinRecords($deviceId): ?array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $result = $qb->select('MAX(tht.ts) AS max_ts, MIN(tht.ts) AS min_ts')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where($qb->expr()->eq('IDENTITY(tht.device)', $deviceId))
            ->andWhere('tht.isCalculatedIdling = false')
            ->getQuery()
            ->getResult();

        return $result ? $result[0] : [];
    }

    /**
     * @return bool
     */
    public function removeOldCalculatedRecords(): bool
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        return $this->getEntityManager()
            ->createQueryBuilder()
            ->delete(TrackerHistoryTemp::class, 'tht')
            ->where($qb->expr()->lt('tht.createdAt', ':date'))
            ->setParameter('date', (new Carbon())->subDays(TrackerHistoryTemp::RECORDS_DAYS_TTL))
            ->getQuery()
            ->execute();
    }

    /**
     * @param Route $route
     * @return array
     */
    public function getSpeedsForRouteStatsQuery(Route $route): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('AVG(tht.speed) as avgSpeed, MAX(tht.speed) as maxSpeed')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.device = :device')
            ->andWhere('tht.speed IS NOT NULL')
            ->andWhere('tht.ts >= :dateFrom')
            ->andWhere('tht.ts <= :dateTo')
            ->setParameter('device', $route->getDevice())
            ->setParameter('dateFrom', $route->getStartedAt())
            ->setParameter('dateTo', $route->getFinishedAt())
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param Route $route
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function hasStartPointOfRoute(Route $route): bool
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('tht.id')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.device = :device')
            ->andWhere('tht.ts = :date')
            ->setParameter('device', $route->getDevice())
            ->setParameter('date', $route->getStartedAt())
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return boolval($result);
    }

    /**
     * @param Route $route
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function hasFinishPointOfRoute(Route $route): bool
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('tht.id')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.device = :device')
            ->andWhere('tht.ts = :date')
            ->setParameter('device', $route->getDevice())
            ->setParameter('date', $route->getFinishedAt())
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return boolval($result);
    }

    /**
     * @param int $deviceId
     * @param $startedAt
     * @param $finishedAt
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateTrackerHistoriesTempAsNotCalculatedForRoutes(int $deviceId, $startedAt, $finishedAt)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update(TrackerHistoryTemp::class, 'tht')
            ->set('tht.isCalculated', 'false')
            ->where('tht.device = :deviceId')
            ->andWhere('tht.ts >= :startedAt')
            ->andWhere('tht.ts <= :finishedAt')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('startedAt', $startedAt)
            ->setParameter('finishedAt', $finishedAt)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $deviceId
     * @param $startedAt
     * @param $finishedAt
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateTrackerHistoriesTempAsNotCalculatedForIdling(int $deviceId, $startedAt, $finishedAt)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update(TrackerHistoryTemp::class, 'tht')
            ->set('tht.isCalculatedIdling', 'false')
            ->where('tht.device = :deviceId')
            ->andWhere('tht.ts >= :startedAt')
            ->andWhere('tht.ts <= :finishedAt')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('startedAt', $startedAt)
            ->setParameter('finishedAt', $finishedAt)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $deviceId
     * @param $startedAt
     * @param $finishedAt
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateTrackerHistoriesTempAsNotCalculatedForSpeeding(int $deviceId, $startedAt, $finishedAt)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update(TrackerHistoryTemp::class, 'tht')
            ->set('tht.isCalculatedSpeeding', 'false')
            ->where('tht.device = :deviceId')
            ->andWhere('tht.ts >= :startedAt')
            ->andWhere('tht.ts <= :finishedAt')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('startedAt', $startedAt)
            ->setParameter('finishedAt', $finishedAt)
            ->getQuery()
            ->execute();
    }

    public function getTHTempByTH(TrackerHistory $trackerHistory)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('tht')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.trackerHistory = :th')
            ->setParameter('th', $trackerHistory)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param RouteTemp $route
     * @return Query
     */
    public function getHistoriesDataByRouteTemp(RouteTemp $route): Query
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('tht.id, tht.movement, tht.ignition, IDENTITY(tht.trackerHistory) AS th_id')
            ->from(TrackerHistoryTemp::class, 'tht')
            ->where('tht.device = :device')
            ->andWhere('tht.ts >= :dateFrom')
            ->andWhere('tht.ts <= :dateTo')
            ->setParameter('device', $route->getDevice())
            ->setParameter('dateFrom', $route->getStartedAt())
            ->setParameter('dateTo', $route->getFinishedAt())
            ->orderBy('tht.ts', Criteria::DESC)
            ->getQuery();
    }
}
