<?php

namespace App\Service\Tracker\Parser\Teltonika\SensorEventTypes;

abstract class BaseType
{
    public const ODOMETER = 'odometer';
    public const ENGINE_HOURS = 'engine_hours';
    public const TEMPERATURE_LEVEL = 'temperature_level';
    public const GSM_SIGNAL = 'gsm_signal';
    public const BATTERY_VOLTAGE = 'battery_voltage';
    public const EXTERNAL_VOLTAGE = 'external_voltage';
    public const IGNITION = 'ignition';
    public const MOVEMENT = 'movement';
    public const IBUTTON = 'ibutton';
    public const NON_EXISTING_EVENT_ID = 99999;

    abstract public static function getTotalOdometerId();

    abstract public static function getEngineHoursId();

    abstract public static function getTemperatureLevelId();

    abstract public static function getGSMSignalId();

    abstract public static function getBatteryVoltageId();

    abstract public static function getExternalVoltageId();

    abstract public static function getIgnitionId();

    abstract public static function getMovementId();

    abstract public static function getIButtonId();

    /**
     * @return string
     */
    public static function getModelName(): string
    {
        return static::$model;
    }

    /**
     * @param $modelName
     * @return BaseType
     * @throws \Exception
     */
    public static function getEventTypesModelByModelName($modelName): self
    {
        switch ($modelName) {
            case FM36M1::getModelName():
                return new FM36M1();
            case FMB920::getModelName():
                return new FMB920();
            default:
                return new FM3001();
        }
    }

    /**
     * @param $eventName
     * @return string
     * @throws \Exception
     */
    public static function getEventIdByEventName($eventName): string
    {
        switch ($eventName) {
            case self::ODOMETER:
                return static::getTotalOdometerId();
            case self::ENGINE_HOURS:
                return static::getEngineHoursId();
            case self::TEMPERATURE_LEVEL:
                return static::getTemperatureLevelId();
            case self::GSM_SIGNAL:
                return static::getGSMSignalId();
            case self::BATTERY_VOLTAGE:
                return static::getBatteryVoltageId();
            case self::EXTERNAL_VOLTAGE:
                return static::getExternalVoltageId();
            case self::IGNITION:
                return static::getIgnitionId();
            case self::MOVEMENT:
                return static::getMovementId();
            case self::IBUTTON:
                return static::getIButtonId();
            default:
                throw new \Exception('Unsupported event name: ' . $eventName);
        }
    }

    /**
     * @param $id
     * @return string
     * @throws \ReflectionException
     */
    public static function getNameById($id): ?string
    {
        $rc = new \ReflectionClass(static::class);
        $constants = $rc->getConstants();

        return strtolower(substr(array_search($id, $constants), 0, -3)) ?: null;
    }

    /**
     * @param $name
     * @return bool
     * @throws \ReflectionException
     */
    public static function hasNoConstant($name)
    {
        $rc = new \ReflectionClass(self::class);

        return !$rc->hasConstant($name);
    }
}
