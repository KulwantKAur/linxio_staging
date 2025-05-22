<?php

namespace App\Service\Tracker\Command\Topflytech;

use App\Entity\Device;
use App\Service\Tracker\Command\Topflytech\Model\AlarmBaseCommand;
use App\Service\Tracker\Command\Topflytech\Model\DriverSensorIdBaseCommand;
use App\Service\Tracker\Command\Topflytech\Model\OdometerBaseCommand;
use App\Service\Tracker\Command\Topflytech\Model\RelayBaseCommand;
use App\Service\Tracker\Command\Topflytech\Model\TemperatureAndHumiditySensorBaseCommand;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Service\Tracker\Parser\Topflytech\Data;
use Doctrine\ORM\EntityManager;

/**
 * @example get version: 2525810010000108625220300940070176657273696F6E23
 * @example get version: 2626810010000108652840407313520176657273696F6E23
 */
class TopflytechTrackerCommandService extends TrackerCommandService
{
    public const COMMAND_SERIAL_NUMBER = '0001';
    public const COMMAND_LENGTH = '0010';
    public const SMS_VIA_NETWORK_TO_DEVICE_TYPE = '01';
    public const SMS_VIA_DEVICE_TO_MANAGER_PHONE_TYPE = '02';
    public const SMS_VIA_DEVICE_TO_SPECIFIC_NUMBER_TYPE = '03';

    public EntityManager $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    /**
     * @inheritDoc
     */
    public function getIButtonCommand(
        string $driverSensorId,
        Device $device,
        string $type = self::ADD_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return DriverSensorIdBaseCommand::resolve($driverSensorId, $device, $type);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTemperatureAndHumidityCommand(
        string $sensorId,
        Device $device,
        string $type = self::ADD_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return TemperatureAndHumiditySensorBaseCommand::resolve($sensorId, $device, $type);
    }

    /**
     * @inheritDoc
     */
    public function getOdometerCommand(
        Device $device,
        string $type = self::SET_ACTION_TYPE,
        ?int $value = null
    ): ?TrackerCommandInterface {
        return OdometerBaseCommand::resolve($device, $type, $value);
    }

    public function getRelayCommand(
        Device $device,
        string $type = self::SET_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return RelayBaseCommand::resolve($device, $type);
    }

    public function getOverSpeedingAlarmCommand(
        Device $device,
        string $type = self::SET_ACTION_TYPE
    ): ?TrackerCommandInterface {
        return AlarmBaseCommand::resolve($device, $type);
    }

    /**
     * @param string $protocol
     * @param string $imei
     * @param string $command
     * @return string
     */
    public static function formatCommandAsHEX(string $protocol, string $imei, string $command): string
    {
        $commandHex = bin2hex($command);

        return $protocol . Data::SETTING_MESSAGE_TYPE . self::COMMAND_LENGTH . self::COMMAND_SERIAL_NUMBER . '0' .
            $imei . self::SMS_VIA_NETWORK_TO_DEVICE_TYPE . $commandHex;
    }
}