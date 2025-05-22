<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder\Filter;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use Doctrine\Common\Collections\Criteria;

/**
 * Filter by who changed (user) the main entity by event
 */
class UserByFilter implements FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria
    {
        return $criteria
            ->andWhere(Criteria::expr()->eq('userBy', $value))
            ;
    }
}
