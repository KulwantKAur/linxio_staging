<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\DataAndGNSS;

/**
 * @example
 */
abstract class BaseDataCode
{
    /**
     * @return array|null
     */
    abstract public function getDataArray(): ?array;

    /**
     * @param string $textPayload
     * @return mixed
     */
    abstract public static function createFromTextPayload(string $textPayload);

    /**
     * @return null
     */
    public function getGpsData()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getLocationData()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getMovement()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getIgnition()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getInternalBatteryVoltage()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getDriverIdTag()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getDriverSensorData()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getTempAndHumidityData()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getSOSData()
    {
        return null;
    }

    /**
     * @return boolean
     */
    public function isSOSAlarm()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isNullableData()
    {
        return false;
    }

    /**
     * @return DataAndGNSS|null
     */
    public function getDataAndGNSS(): ?DataAndGNSS
    {
        return $this->dataAndGNSS ?? null;
    }
}
