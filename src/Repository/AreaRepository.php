<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class AreaRepository extends EntityRepository
{
    /**
     * @param string $point
     * @param null $team
     * @param array $fields
     * @return mixed
     */
    public function findByPoint(string $point, $team = null, $fields = [])
    {
        if ($fields) {
            $fields = array_map(function ($item) {
                return 'a.' . $item;
            }, $fields);
        }

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(empty($fields) ? 'a' : implode(', ', $fields))
            ->from(Area::class, 'a')
            ->andWhere("ST_Contains(a.polygon, ST_GeomFromText('POINT($point)')) = true")
            ->andWhere('a.status = :status')->setParameter('status', Area::STATUS_ACTIVE);
        if ($team && is_array($team)) {
            $query->andWhere('a.team IN (:team)')->setParameter('team', $team);
        } elseif ($team) {
            $query->andWhere("a.team = :team")->setParameter('team', $team);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param Area $area
     * @return mixed
     */
    public function findVehiclesInArea(Area $area, array $vehicleIds)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('thl')
            ->from(TrackerHistoryLast::class, 'thl')
            ->join(Area::class, 'a', Expr\Join::WITH, 'a = :area')
            ->setParameter('area', $area)
            ->andWhere('thl.team = :team')
            ->andWhere('IDENTITY(thl.vehicle) in (:vehicleIds)')->setParameter('vehicleIds', $vehicleIds)
            ->andWhere("ST_Contains(a.polygon, ST_GeomFromText(concat('POINT(', thl.lng, ' ', thl.lat, ')'))) = true")
            ->andWhere("thl.vehicle IS NOT NULL")
            ->andWhere("a.team = :team")->setParameter('team', $area->getTeam());

        return $query->getQuery()->getResult();
    }

    public function getById($id, User $user)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from(Area::class, 'a')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id);

        if ($user->needToCheckUserGroup()) {
            $userAreas = $this->getEntityManager()->getRepository(UserGroup::class)->getUserAreasIdFromUserGroup($user);
            $qb->andWhere('a.id in (:areaIds)')->setParameter('areaIds', $userAreas);
        }

        if ($user->isInClientTeam()) {
            $qb->andWhere('a.team = :team')->setParameter('team', $user->getTeam());
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByPointAndIds(string $point, array $ids = [], array $groupIds = [], ?Team $team = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(a) as c')
            ->from(Area::class, 'a')
            ->andWhere("ST_Contains(a.polygon, ST_GeomFromText('POINT($point)')) = true")
            ->andWhere('a.status = :status')->setParameter('status', Area::STATUS_ACTIVE);

        if (count($ids)) {
            $query->andWhere('a.id IN (:ids)')->setParameter('ids', $ids);
        }

        if (count($groupIds)) {
            $query->innerJoin('a.groups', 'g')
                ->andWhere('g.id IN (:groupIds)')
                ->setParameter('groupIds', $groupIds);
        }

        if ($team) {
            $query->andWhere('a.team = :team')->setParameter('team', $team);
        }

        return (bool)$query->getQuery()->getSingleScalarResult();
    }
}
