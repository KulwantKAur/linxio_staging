<?php

declare(strict_types=1);

namespace App\Service\EventLog\Report\CriteriaBuilder\Filter;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterInterface;
use Doctrine\Common\Collections\Criteria;

/**
 *
 */
class VehicleIdFilter implements FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria
    {
        if (is_array($value)) {
//            return $criteria->andWhere(Criteria::expr()->in('vehicleId', $value));
            return $criteria->andWhere(Criteria::expr()->orX(
                Criteria::expr()->in('vehicleId', $value),
                Criteria::expr()->isNull('vehicleId')
            ));
        }

        return $criteria->andWhere(Criteria::expr()->eq('vehicleId', $value));
    }
}
