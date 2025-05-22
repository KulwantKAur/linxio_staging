<?php

namespace App\Service\Redis\Models;

use App\Entity\Device;
use App\Service\Redis\Interfaces\RedisModelInterface;

class DeviceRedisModel implements RedisModelInterface
{
    public const DEVICES = 'devices.device#';
    public const DEVICE_LAST_DATA_RECEIVED = '.lastDataReceived';
    public const DEVICE_LAST_DATA_RECEIVED_TTL = 31536000;
    public const DEVICE_LAST_ELASTICSEARCH_UPDATE = '.lastElasticsearchUpdate';
    public const DEVICE_EXCEEDING_SPEED_LIMIT_DATE = '.exceedingSpeedLimitDate';

    /**
     * @inheritDoc
     */
    public function getKeyById(int $id)
    {
        return self::DEVICES . $id;
    }

    public static function getByName(int $deviceId, string $name): string
    {
        return self::DEVICES . $deviceId . $name;
    }

    public static function getLastDataReceivedKey(Device $device): string
    {
        return self::DEVICES . $device->getId() . self::DEVICE_LAST_DATA_RECEIVED;
    }

    public static function getLastElasticsearchUpdate(Device $device): string
    {
        return self::DEVICES . $device->getId() . self::DEVICE_LAST_ELASTICSEARCH_UPDATE;
    }
}
