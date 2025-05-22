<?php

declare(strict_types=1);

namespace App\Service\EventLog\Factory;

use App\Service\EventLog\Interfaces\CriteriaBuilder\FilterFactoryInterface;

/**
 * Class FilterFactory
 */
class FilterFactory implements FilterFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(string $filter)
    {
        $className = ucfirst($filter).'Filter';
        $filterObj = 'App\Service\EventLog\Report\CriteriaBuilder\Filter\\'.$className;

        if (class_exists($filterObj)) {
            return new $filterObj();
        }
    }
}
