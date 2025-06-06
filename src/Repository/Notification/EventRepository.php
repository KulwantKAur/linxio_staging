<?php

namespace App\Repository\Notification;

use App\Entity\Notification\Event;
use \Doctrine\ORM\EntityRepository;

/**
 * EventRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventRepository extends EntityRepository
{
    /**
     * @param array $types
     * @return mixed
     * @throws \Exception
     */
    public function getEvents(array $types)
    {
        if (array_diff($types, Event::ALLOWED_TYPES)) {
            throw new \Exception('Invalid type');
        }

        return $this
            ->createQueryBuilder('e')
            ->where('e.type IN (:types)')
            ->setParameter('types', $types)
            ->orderBy('e.id')
            ->getQuery()
            ->execute();
    }

    public function getEventByName(string $name, ?string $type = null): ?Event
    {
        $q = $this->createQueryBuilder('e')
            ->andWhere('e.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);

        if ($type) {
            $q->andWhere('e.type = :type')
                ->setParameter('type', $type);
        }

        return $q->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(86400)
            ->getOneOrNullResult();
    }

    public function getEventsByName(string $name, ?string $type = null)
    {
        $q = $this->createQueryBuilder('e')
            ->andWhere('e.name = :name')
            ->setParameter('name', $name);

        if ($type) {
            $q->andWhere('e.type = :type')
                ->setParameter('type', $type);
        }

        return $q->getQuery()
            ->useQueryCache(true)
            ->enableResultCache(86400)
            ->getResult();
    }
}
