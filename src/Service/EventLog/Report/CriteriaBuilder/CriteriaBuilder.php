<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder;

use App\Entity\EventLog\EventLog;
use App\Repository\EventLog\RepositoryInterface;
use App\Service\EventLog\Interfaces\CriteriaBuilder\CriteriaBuilderInterface;
use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterFactoryInterface;
use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;

/**
 * Class CriteriaBuilder
 */
class CriteriaBuilder implements CriteriaBuilderInterface
{
    /**
     * @var FilterFactoryInterface $filterFactory
     */
    private FilterFactoryInterface $filterFactory;

    /**
     * CriteriaBuilder constructor.
     *
     * @param FilterFactoryInterface $filterFactory
     */
    public function __construct(FilterFactoryInterface $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * @param array $filters
     * @param ArrayCollection $results
     *
     * @return mixed
     */
    public function filter(array $filters, ArrayCollection $results): ArrayCollection
    {
        $criteria = $this->build($filters);

        return $results->matching($criteria);
    }

    /**
     * @param array $filters
     *
     * @return Criteria
     */
    public function build(array $filters): Criteria
    {
        $criteria = Criteria::create();
        foreach ($filters as $filterName => $value) {
            $filter = $this->filterFactory->create($filterName);

            if ($filter instanceof FilterInterface) {
                $criteria = $filter->apply($criteria, $value);
            }
        }

        return $criteria;
    }
}
