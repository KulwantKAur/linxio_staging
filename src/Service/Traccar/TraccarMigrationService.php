<?php

namespace App\Service\Traccar;

use Carbon\Carbon;

class TraccarMigrationService
{
    public const MIGRATION_JOB_NAME = 'traccar_migration_';
    public const CLEAR_JOB_NAME = 'traccar_clear_';
    public const MIGRATION_DURATION_MIN = 10;
    public static string $traccarDBName = 'traccar';

    /**
     * @return string
     */
    private static function getCronForMigration(): string
    {
        $min = Carbon::now()->addMinutes(2)->minute;

        return $min . ' * * * *';
    }

    /**
     * @return string
     */
    private static function getCronForClear(): string
    {
        $min = Carbon::now()->addMinutes(self::MIGRATION_DURATION_MIN + 2)->minute;

        return $min . ' * * * *';
    }

    /**
     * @return string
     */
    public static function getTraccarDBName(): string
    {
        self::$traccarDBName = getenv('TRACCAR_DATABASE_NAME') ?: 'traccar';

        return self::$traccarDBName;
    }

    /**
     * @param string $sql
     * @param string $versionName
     * @return array
     */
    public static function getMigrationQueriesUp(string $sql, string $versionName): array
    {
        $queries = [];
        $queries[] = 'SELECT cron.schedule(\'' . self::MIGRATION_JOB_NAME . $versionName . '\', \'' . self::getCronForMigration() . '\', $$' . $sql . '$$);';
        $queries[] = 'UPDATE cron.job SET database = \'' . self::getTraccarDBName() . '\' WHERE jobname = \'' . self::MIGRATION_JOB_NAME . $versionName . '\'';
        $queries[] = 'SELECT cron.schedule(\'' . self::CLEAR_JOB_NAME . $versionName . '\', \'' . self::getCronForClear() . '\', $$SELECT cron.unschedule(\'' . self::MIGRATION_JOB_NAME . $versionName . '\'); SELECT cron.unschedule(\'' . self::CLEAR_JOB_NAME . $versionName . '\');$$);';

        return $queries;
    }

    /**
     * @param string $sql
     * @param string $versionName
     * @return array
     */
    public static function getMigrationQueriesDown(string $sql, string $versionName): array
    {
        $queries = [];
        $queries[] = 'SELECT cron.schedule(\'' . self::MIGRATION_JOB_NAME . $versionName . '\', \'' . self::getCronForMigration() . '\', $$' . $sql . '$$);';
        $queries[] = 'UPDATE cron.job SET database = \'' . self::getTraccarDBName() . '\' WHERE jobname = \'' . self::MIGRATION_JOB_NAME . $versionName . '\'';
        $queries[] = 'SELECT cron.schedule(\'' . self::CLEAR_JOB_NAME . $versionName . '\', \'' . self::getCronForClear() . '\', $$SELECT cron.unschedule(\'' . self::MIGRATION_JOB_NAME . $versionName . '\'); SELECT cron.unschedule(\'' . self::CLEAR_JOB_NAME . $versionName . '\');$$);';

        return $queries;
    }
}
