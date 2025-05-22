<?php

namespace App\Util;

class ArrayHelper
{
    /**
     * @param array $data
     * @param string $key
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    public static function getValueFromArray(array $data, string $key, $defaultValue = null)
    {
        if (!($data[$key] ?? null)) {
            return $defaultValue;
        }

        return $data[$key];
    }

    /**
     * @param array $input
     * @param string $separator
     *
     * @return array
     */
    public static function keysToCamelCase(array $input, string $separator = '_'): array
    {
        $array = [];
        foreach ($input as $key => $value) {
            if (is_array($input[$key])) {
                $array[$key] = self::keysToCamelCase($input[$key], $separator);
            } else {
                $array[StringHelper::toCamelCase($key)] = $value;
            }
        }

        return $array;
    }

    /**
     * @param $values
     *
     * @return string
     */
    public static function formatPostgresArrayString($values)
    {
        $values = $values ?? [];
        foreach ($values as &$value) {
            $value = str_replace('"', '\"', $value);
        }

        return sprintf(
            '{"%s"}',
            implode('","', $values)
        );
    }

    public static function removeFromArrayByValue($needle, array $data)
    {
        if (($key = array_search($needle, $data)) !== false) {
            unset($data[$key]);
        }

        return $data;
    }

    public static function arraySpliceAfterKey($array, $key, $arrayToInsert)
    {
        $key_pos = array_search($key, array_keys($array));
        if ($key_pos !== false) {
            $key_pos++;
            $second_array = array_splice($array, $key_pos);
            $array = array_merge($array, $arrayToInsert, $second_array);
        }

        return $array;
    }
}
