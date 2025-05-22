<?php

namespace App\Util;


class MetricHelper
{
    /**
     * @param $meters
     * @return false|float|null
     */
    public static function metersToKm($meters)
    {
        return $meters ? round($meters / 1000, 1) : null;
    }

    /**
     * @param $meters
     * @return string|null
     */
    public static function metersToHumanKm($meters): ?string
    {
        return $meters ? self::metersToKm($meters) . ' km' : null;
    }

    /**
     * @param $km
     * @return string|null
     */
    public static function kmToHumanKm($km): ?string
    {
        return $km ? $km . ' km' : null;
    }

    /**
     * @param $speed
     * @return string|null
     */
    public static function speedToHumanKmH($speed): ?string
    {
        return $speed ? $speed . 'km/h' : null;
    }

    public static function fieldsToKm(array $items, array $fields): array
    {
        foreach ($items as $key => $value) {
            if (in_array($key, $fields)) {
                $items[$key] = self::metersToKm($value);
            }
        }

        return $items;
    }
}
