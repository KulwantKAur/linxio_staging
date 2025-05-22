<?php

namespace App\Service\Redis\Models;

use App\Entity\Vehicle;
use App\Service\Redis\Interfaces\RedisModelInterface;

class VehicleRedisModel implements RedisModelInterface
{
    public const VEHICLES = 'vehicles.vehicle#';
    public const VEHICLE_ENGINE_ON_TIME = '.engine-on-time';
    public const VEHICLE_TODAY_DATA = '.todayData';
    public const VEHICLE_ENGINE_HISTORY = '.engineHistory';

    /**
     * @inheritDoc
     */
    public function getKeyById(int $id)
    {
        return self::VEHICLES . $id;
    }

    public static function getEngineOnTimeKey(Vehicle $vehicle): string
    {
        return self::VEHICLES . $vehicle->getId() . self::VEHICLE_ENGINE_ON_TIME;
    }

    public static function getTodayDataKey($vehicleId): string
    {
        return self::VEHICLES . $vehicleId . self::VEHICLE_TODAY_DATA;
    }

    public static function getEngineHistoryKey(int $vehicleId): string
    {
        return self::VEHICLES . $vehicleId . self::VEHICLE_ENGINE_HISTORY;
    }
}
