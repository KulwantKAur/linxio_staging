<?php

namespace App\Enums;

class EntityFields
{
    public const DEFAULT_LABEL = 'defaultLabel';
    public const STARTED_AT = 'startedAt';
    public const STARTED_AT_DATE = 'startedAtDate';
    public const STARTED_AT_TIME = 'startedAtTime';
    public const FINISHED_AT = 'finishedAt';
    public const FINISHED_AT_DATE = 'finishedAtDate';
    public const FINISHED_AT_TIME = 'finishedAtTime';
    public const START_DATE = 'startDate';
    public const END_DATE = 'endDate';
    public const ARRIVED_AT = 'arrivedAt';
    public const ARRIVED_AT_DATE = 'arrivedAtDate';
    public const ARRIVED_AT_TIME = 'arrivedAtTime';
    public const DEPARTED_AT = 'departedAt';
    public const DEPARTED_AT_DATE = 'departedAtDate';
    public const DEPARTED_AT_TIME = 'departedAtTime';
    public const LAST_MODIFIED = 'lastModified';
    public const LAST_MODIFIED_DATE = 'lastModifiedDate';
    public const LAST_MODIFIED_TIME = 'lastModifiedTime';
    public const CREATED_AT = 'createdAt';
    public const CREATED_AT_DATE = 'createdAtDate';
    public const CREATED_AT_TIME = 'createdAtTime';
    public const OCCURRED_AT = 'occurredAt';
    public const OCCURRED_AT_TIME = 'occurredAtTime';
    public const OCCURRED_AT_DATE = 'occurredAtDate';

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
