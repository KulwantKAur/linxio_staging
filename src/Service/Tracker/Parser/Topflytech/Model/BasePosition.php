<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\MultipleDataInterface;
use App\Service\Tracker\Parser\DataHelper;

abstract class BasePosition implements MultipleDataInterface
{
    public const PACKET_MINIMAL_LENGTH = 100;

    abstract public static function createFromTextPayload(string $textPayload);

    /**
     * @param string $payload
     * @return string
     */
    public static function formatDeviceTemperature(string $payload)
    {
        $binaryValues = DataHelper::getBinaryFromHex($payload);
        $binaryValues = DataHelper::addZerosToStartOfString($binaryValues, 8);
        $sign = substr($binaryValues, 0, 1) == 1 ? '-' : '+';
        $value = bindec(substr($binaryValues, 1));

        return floatval($sign . $value);
    }

    /**
     * @param string $payload
     * @return string
     */
    public static function formatSetting1(string $payload)
    {
        // @todo
        return $payload;
    }

    /**
     * @param string $payload
     * @return string
     */
    public static function formatSetting2(string $payload)
    {
        // @todo
        return $payload;
    }

    /**
     * @param string $payload
     * @return string
     */
    public static function formatAcceleration(string $payload)
    {
        // @todo
        return $payload;
    }

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->dataAndGNSS;
    }
}
