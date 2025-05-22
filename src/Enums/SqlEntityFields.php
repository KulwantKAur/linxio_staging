<?php

namespace App\Enums;

class SqlEntityFields
{
    public const DEFAULT_LABEL = 'defaultlabel';
    public const STARTED_AT = 'started_at';
    public const STARTED_AT_DATE = 'started_at_date';
    public const STARTED_AT_TIME = 'started_at_time';
    public const FINISHED_AT = 'finished_at';
    public const FINISHED_AT_DATE = 'finished_at_date';
    public const FINISHED_AT_TIME = 'finished_at_time';
    public const SR_DATE = 'sr_date';
    public const SR_DATE_DATE = 'sr_date_date';
    public const SR_DATE_TIME = 'sr_date_time';
    public const SR_LAST_DATE = 'sr_last_date';
    public const SR_LAST_DATE_DATE = 'sr_last_date_date';
    public const SR_LAST_DATE_TIME = 'sr_last_date_time';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getAll()
    {
        $class = new \ReflectionClass(__CLASS__);

        return $class->getConstants();
    }
}
