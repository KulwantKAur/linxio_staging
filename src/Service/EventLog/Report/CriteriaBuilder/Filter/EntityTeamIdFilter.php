<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder\Filter;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use Doctrine\Common\Collections\Criteria;

/**
 * Filter for modified entity on event by teamId
 */
class EntityTeamIdFilter implements FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria
    {
        if (is_array($value)) {
            return $criteria
                ->andWhere(Criteria::expr()->in('entityTeamId', $value))
                ;
        } else {
            return $criteria
                ->andWhere(Criteria::expr()->eq('entityTeamId', $value))
                ;
        }
    }
}
