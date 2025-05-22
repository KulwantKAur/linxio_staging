<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE;

use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\BaseDataCode;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\DoorSensor;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\Relay;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\BLE\SOS;
use App\Service\Tracker\Parser\Topflytech\Model\BLE\TirePressureSensor;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\BLE\DriverID;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\BLE\TemperatureAndHumiditySensorService;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;

/**
 * @example
 */
class BLE extends BaseBLE
{
    /**
     * @param string $textPayload
     * @param string $protocol
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload, string $protocol = TcpDecoder::PROTOCOL_TLW1): self
    {
        return parent::createFromTextPayload($textPayload, $protocol);
    }

    /**
     * @param string $BLEDataCode
     * @param string $textPayload
     * @param string $protocol
     * @return BaseDataCode|array
     * @throws \Exception
     */
    public static function handleDataByDataCodeAndPayload(string $BLEDataCode, string $textPayload, string $protocol)
    {
        switch ($BLEDataCode) {
            case self::TIRE_PRESSURE_SENSOR_DATA_CODE:
                return TirePressureSensor::createFromTextPayload($textPayload);
            case self::SOS_DATA_CODE:
                return SOS::createFromTextPayload($textPayload);
            case self::DRIVER_ID_DATA_CODE:
                return DriverID::createFromTextPayload($textPayload);
            case self::TEMPERATURE_AND_HUMIDITY_SENSOR_DATA_CODE:
                return TemperatureAndHumiditySensorService::createFromTextPayload($textPayload);
            case self::DOOR_SENSOR_DATA_CODE:
                return DoorSensor::createFromTextPayload($textPayload);
            case self::RELAY_DATA_CODE:
                return Relay::createFromTextPayload($textPayload);
            default:
                break;
        }
    }
}
