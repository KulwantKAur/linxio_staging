<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser;

/**
 * Class DataHelper
 * @package App\Service\Tracker\Parser
 */
class DataHelper
{
    /**
     * @param array $data
     * @param string $value
     * @return mixed|null
     */
    public static function formatValueIgnoreZero(array $data, string $value)
    {
        return (isset($data[$value]) && $data[$value] != 0) ? $data[$value] : null;
    }

    /**
     * @param string $value
     * @param int $limit
     * @return string
     */
    public static function addZerosToEndOfString(string $value, $limit = 16): string
    {
        $strLength = strlen($value);

        if ($strLength < $limit) {
            $value .= str_repeat('0', $limit - $strLength);
        }

        return $value;
    }

    /**
     * @param string $value
     * @param int $limit
     * @return string
     */
    public static function addZerosToStartOfString(string $value, $limit = 16): string
    {
        $strLength = strlen($value);

        if ($strLength < $limit) {
            $value = str_repeat('0', $limit - $strLength) . $value;
        }

        return $value;
    }

    /**
     * @param $value
     * @return string|null
     */
    public static function getBinaryFromHex($value): ?string
    {
        return decbin(hexdec($value));
    }

    /**
     * @param $value
     * @return string|null
     */
    public static function implodeStringWithDot($value): ?string
    {
        return implode('.', str_split($value));
    }

    /**
     * @param float|null $value
     * @return float|null
     */
    public static function formatMilliValue(?float $value): ?float
    {
        return ($value && (floor($value) < 100) && (floor($value) > 0)) ? $value * 1000 : $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function increaseValueToMilli($value)
    {
        return $value ? $value * 1000 : $value;
    }

    /**
     * @param string $value
     * @return float|null
     */
    public static function hexToFloatDCBA(string $value): ?float
    {
        $pack = pack('H*', $value);
        $data = $pack ? unpack('f', $pack) : null;

        return $data ? array_shift($data) : null;
    }

    /**
     * @param string $textPayload
     * @param int $integerLength
     * @param int $fractionLength
     * @return float
     */
    public static function formatIntegerAndFraction(string $textPayload, $integerLength = 2, $fractionLength = 2)
    {
        $integer = intval(substr($textPayload, 0, $integerLength));
        $fraction = substr($textPayload, $integerLength, $fractionLength);

        return floatval(implode('.', [$integer, $fraction]));
    }

    /**
     * @param string $value
     * @return float|null
     */
    public static function hexToSignedInt(string $value): ?float
    {
        $pack = pack('s', hexdec($value));
        $data = $pack ? unpack('s', $pack) : null;

        return $data ? array_shift($data) : null;
    }
}
