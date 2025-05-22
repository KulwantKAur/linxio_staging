<?php

declare(strict_types=1);

namespace App\Service\EventLog\Interfaces\CriteriaBuilder;

/**
 * Interface FilterFactoryInterface
 */
interface FilterFactoryInterface
{
    /**
     * @param string $filter
     *
     * @return mixed
     */
    public function create(string $filter);
}
