<?php

declare(strict_types=1);

namespace App\Service\EventLog\Interfaces\CriteriaBuilder;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface FilterInterface
 */
interface FilterInterface
{
    /**
     * @param Criteria $criteria
     * @param $value
     *
     * @return Criteria
     */
    public function apply(Criteria $criteria, $value): Criteria;
}
