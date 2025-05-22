<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder\Filter;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use Doctrine\Common\Collections\Criteria;

/**
 *
 */
class EntityIdFilter implements FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria
    {
        return $criteria
            ->andWhere(Criteria::expr()->eq('entityId', $value))
            ;
    }
}
