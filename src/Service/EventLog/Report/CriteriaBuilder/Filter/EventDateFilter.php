<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder\Filter;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use Doctrine\Common\Collections\Criteria;

/**
 *
 */
class EventDateFilter implements FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria
    {
        $startDate = $value['startDate'] ?? null;
        $endDate = $value['endDate'] ?? null;

        return $criteria
            ->andWhere(
                Criteria::expr()->andX(
                    Criteria::expr()->gte('eventDate', $startDate),
                    Criteria::expr()->lte('eventDate', $endDate),
                )
            )
            ;
    }
}
