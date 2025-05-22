<?php

namespace App\Service\Tracker\Command;

use App\Entity\Device;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerCommand as TrackerCommandEntity;
use App\Service\BaseService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use Doctrine\ORM\EntityManager;

class TrackerCommandService extends BaseService
{
    public const CUSTOM_COMMAND_TYPE = 0;
    public const IBUTTON_COMMAND_TYPE = 1;
    public const TEMPERATURE_AND_HUMIDITY_COMMAND_TYPE = 2;
    public const ODOMETER_COMMAND_TYPE = 3;
    public const RELAY_COMMAND_TYPE = 4;
    public const ALARM_COMMAND_TYPE = 5;

    public const ADD_ACTION_TYPE = 'add';
    public const DELETE_ACTION_TYPE = 'delete';
    public const LIST_ACTION_TYPE = 'list';
    public const SET_ACTION_TYPE = 'set';
    public const GET_ACTION_TYPE = 'get';

    public EntityManager $em;

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return false;
    }

    /**
     * @param string $driverSensorId
     * @param Device $device
     * @param string $type
     * @return TrackerCommandInterface|null
     */
    public function getIButtonCommand(
        string $driverSensorId,
        Device $device,
        string $type = self::ADD_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return null;
    }

    /**
     * @param string $sensorId
     * @param Device $device
     * @param string $type
     * @return TrackerCommandInterface|null
     */
    public function getTemperatureAndHumidityCommand(
        string $sensorId,
        Device $device,
        string $type = self::ADD_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return null;
    }

    /**
     * @param Device $device
     * @param string $type
     * @param int|null $value
     * @return TrackerCommandInterface|null
     */
    public function getOdometerCommand(
        Device $device,
        string $type = self::SET_ACTION_TYPE,
        ?int   $value = null
    ): ?TrackerCommandInterface {
        return null;
    }

    /**
     * @param Device $device
     * @param mixed|null $dateFrom
     * @param mixed|null $dateTo
     * @return TrackerCommandEntity[]|array|null
     */
    public function getOdometerRecords(Device $device, $dateFrom = null, $dateTo = null)
    {
        return $this->em->getRepository(TrackerCommandEntity::class)
            ->getRecordsWithTypeOdometerInRange($device, $dateFrom, $dateTo);
    }

    /**
     * @param Device $device
     * @param mixed|null $dateFrom
     * @param mixed|null $dateTo
     * @return TrackerCommandEntity|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOdometerLastRecord(Device $device, $dateFrom = null, $dateTo = null): ?TrackerCommandEntity
    {
        return $this->em->getRepository(TrackerCommandEntity::class)
            ->getLastRecordWithTypeOdometerInRange($device, $dateFrom, $dateTo);
    }

    /**
     * @return bool
     */
    public function wakeupDevice(): bool
    {
        return false;
    }

    public function getRelayCommand(
        Device $device,
        string $type = self::SET_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return null;
    }

    public function getOverSpeedingAlarmCommand(
        Device $device,
        string $type = self::SET_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return null;
    }
}
