<?php

namespace App\Service\Route\Driver;

final class SetDriverExecutorRegistry implements SetDriverExecutorRegistryInterface
{
    private $registry = [];

    /**
     * {@inheritDoc}
     */
    public function addExecutors(array $executors): void
    {
        $this->registry = $executors;
    }

    /**
     * {@inheritDoc}
     */
    public function getExecutors(): array
    {
        return $this->registry;
    }
}
