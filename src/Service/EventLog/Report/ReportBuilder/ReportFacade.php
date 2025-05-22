<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\ReportBuilder;

use App\Entity\EventLog\EventLog;
use App\EntityManager\SlaveEntityManager;
use App\Service\EventLog\Interfaces\CriteriaBuilder\CriteriaBuilderInterface;
use App\Service\EventLog\Interfaces\ReportFacadeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ReportFacade
 */
class ReportFacade implements ReportFacadeInterface
{
    protected ArrayCollection $results;
    protected EntityManagerInterface $entityManager;
    protected SlaveEntityManager $emSlave;

//    private iterable $events;

    private CriteriaBuilderInterface $criteriaBuilder;

    public function __construct(
        CriteriaBuilderInterface $criteriaBuilder,
        EntityManagerInterface $entityManager,
        SlaveEntityManager $emSlave
    ) {
        $this->criteriaBuilder = $criteriaBuilder;
        $this->entityManager = $entityManager;
        $this->emSlave = $emSlave;
        $this->results = new ArrayCollection();
    }


    /**
     * @param array $params
     *
     * @return array|ArrayCollection|mixed
     */
    public function findBy(array $params)
    {
//        /** @var EventSearchInterface $events */
//        foreach ($this->events as $events) {
//            $this->aggregate($events->fetch());
//        }

        $criteria = $this->criteriaBuilder->build($params);

        return $this->findByCriteria($criteria);
    }

    /**
     * @param Criteria $criteria
     * @return mixed
     */
    public function findByCriteria(Criteria $criteria)
    {
        return $this->entityManager
            ->getRepository(EventLog::class)
            ->findByCriteria($criteria);
    }
}
