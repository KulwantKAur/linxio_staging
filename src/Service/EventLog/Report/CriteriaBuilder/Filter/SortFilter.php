<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder\Filter;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use App\Util\StringHelper;
use Doctrine\Common\Collections\Criteria;

/**
 * Class SortFilter
 */
class SortFilter implements FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria
    {
        $sort = ltrim($value, ' -');
        $order = strpos($value, '-') === 0 ? Criteria::DESC : Criteria::ASC;

        return $criteria->orderBy([
            'eventDate' => $order
        ]);
    }
}
