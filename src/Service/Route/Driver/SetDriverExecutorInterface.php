<?php

namespace App\Service\Route\Driver;

use App\Entity\DriverHistory;

interface SetDriverExecutorInterface
{
    /**
     * @param $driverHistoryId
     *
     * @return void
     */
    public function execute($driverHistoryId): void;
}
