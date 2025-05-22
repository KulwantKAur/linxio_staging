<?php

namespace App\Service\Traccar\Traits;

use App\Service\Traccar\TraccarMigrationService;

trait TraccarMigrationTrait
{
    /**
     * @return string
     */
    private function getVersionName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
    /**
     * @param string $sql
     * @return void
     */
    private function upTraccar(string $sql)
    {
        $queries = TraccarMigrationService::getMigrationQueriesUp($sql, $this->getVersionName());

        foreach ($queries as $query) {
            $this->addSql($query);
        }
    }

    /**
     * @param string $sql
     * @return void
     */
    private function downTraccar(string $sql)
    {
        $queries = TraccarMigrationService::getMigrationQueriesDown($sql, $this->getVersionName());

        foreach ($queries as $query) {
            $this->addSql($query);
        }
    }
}
