<?php

namespace App\Service\Route\Driver;

interface SetDriverExecutorRegistryInterface
{
    /**
     * @param SetDriverExecutorInterface[] $executors
     *
     * @return void
     */
    public function addExecutors(array $executors): void;

    /**
     * @return SetDriverExecutorInterface[]
     */
    public function getExecutors(): array;
}
