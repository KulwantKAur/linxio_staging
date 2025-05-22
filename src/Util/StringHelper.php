<?php

namespace App\Util;

use App\Service\BaseService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;

class StringHelper
{
    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    public static function toSnakeCase(string $input, $separator = '_'): string
    {
        $separator = preg_quote($separator);
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', $separator . '$0', $input)), $separator);
    }

    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    public static function toCamelCase(string $input, string $separator = '_'): string
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

    /**
     * @param $class
     *
     * @return string
     */
    public static function getClassName($class)
    {
        $class = is_object($class) ? ClassUtils::getClass($class) : $class;

        return strtolower(substr(strrchr($class, "\\"), 1));
    }

    /**
     * @param int $length
     * @return false|string
     */
    public static function generateRandomString(int $length = 32)
    {
        return substr(md5(mt_rand()), 0, $length);
    }

    /**
     * @param $string
     * @param string $e
     * @return string
     */
    public static function toLowerUcFirst($string, $e = 'utf-8')
    {
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) {
            $string = mb_strtolower($string, $e);
            $upper = mb_strtoupper($string, $e);
            preg_match('#(.)#us', $upper, $matches);
            $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
        } else {
            $string = ucfirst(strtolower($string));
        }

        return $string;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isNullString($value)
    {
        return $value === 'null';
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function stringToBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function isJson(...$args)
    {
        json_decode(...$args);

        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    public static function macToString(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        return str_replace(':', '', $value);
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    public static function stringToMac(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        $newValue = chunk_split($value, 2, ':');

        return trim($newValue, ':');
    }

    public static function replaceSpecialChars($string)
    {
        $string = str_replace('&', 'and', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('_', ' ', $string);

        return $string;
    }

    public static function removeFileNameSpecialChars($string)
    {
        $string = str_replace(' ', '-', $string);

        return preg_replace("/[^A-Za-z0-9\_\-\. ]/", '', $string);
    }

    public static function filterEmailArray(array $emails)
    {
        return array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
        });
    }

    public static function getOrder(array $data): string
    {
        return isset($data['sort']) && str_starts_with($data['sort'], '-') ? Criteria::DESC : Criteria::ASC;
    }

    public static function getSort(array $data, string $default = 'id', $camelToSnake = false): string
    {
        if (isset($data['sort']) && $camelToSnake) {
            $data['sort'] = BaseService::camelToSnake($data['sort']);
        }

        return isset($data['sort']) ? ltrim($data['sort'], ' -') : $default;
    }

    /**
     * @param string|float|int|null $val
     * @return bool
     */
    public static function isDecimal($val): bool
    {
        return is_numeric($val) && floor($val) != $val;
    }

    /**
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL)
            && preg_match('/@.+\./', $email);
    }

}
