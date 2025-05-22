<?php

namespace App\Service\Streamax\Model;

use Carbon\Carbon;

abstract class StreamaxModel
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this;
    }

    /**
     * @param string $dateTime
     * @return Carbon
     */
    public function getDateTimeFromString(string $dateTime): Carbon
    {
        return Carbon::parse($dateTime);
    }

    /**
     * @param string|array $fields
     * @return array
     */
    public static function convertStringToArray(mixed $fields): array
    {
        if (is_string($fields)) {
            $fields = json_decode($fields, true);
        }

        return $fields;
    }
}