<?php

declare(strict_types=1);

namespace App\Service\EventLog\Interfaces\CriteriaBuilder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Interface CriteriaBuilderInterface
 */
interface CriteriaBuilderInterface
{
    /**
     * @param array $filters
     * @param ArrayCollection $results
     *
     * @return mixed
     */
    public function filter(array $filters, ArrayCollection $results): ArrayCollection;

    /**
     * @param array $filters
     * @return Criteria
     */
    public function build(array $filters): Criteria;
}
