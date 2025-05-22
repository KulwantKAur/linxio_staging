<?php

declare(strict_types=1);

namespace App\Service\EventLog\Interfaces;

/**
 * ReportFacadeInterface
 */
interface ReportFacadeInterface
{
    /**
     * @param array $params
     * @return array
     */
    public function findBy(array $params);
}
