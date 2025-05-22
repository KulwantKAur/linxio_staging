<?php

namespace App\Service\Tracker\Command\Topflytech\Model;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Service\Tracker\Command\Topflytech\Model\TLD1DADE\TemperatureAndHumiditySensorCommand as TemperatureAndHumiditySensorCommandTLD1DADE;
use App\Service\Tracker\Command\Topflytech\Model\TLD2\TemperatureAndHumiditySensorCommand as TemperatureAndHumiditySensorCommandTLD2;
use App\Service\Tracker\Command\Topflytech\Model\TLP1\TemperatureAndHumiditySensorCommand as TemperatureAndHumiditySensorCommandTLP1;
use App\Service\Tracker\Command\Topflytech\Model\TLW1AndTLD1AE\TemperatureAndHumiditySensorCommand as TemperatureAndHumiditySensorCommandTLW1AndTLD1AE;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Util\StringHelper;

abstract class TemperatureAndHumiditySensorBaseCommand extends BaseCommand
{
    /** @var string $BLESensorId */
    private $BLESensorId;

    /**
     * @param string $BLESensorId
     * @param Device $device
     * @param string $actionType
     */
    public function __construct(
        string $BLESensorId,
        Device $device,
        string $actionType = TrackerCommandService::ADD_ACTION_TYPE
    ) {
        parent::__construct($device, $actionType);
        $this->BLESensorId = $BLESensorId;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?int
    {
        return TrackerCommandService::TEMPERATURE_AND_HUMIDITY_COMMAND_TYPE;
    }

    /**
     * @return string
     */
    public function getBLESensorId(): string
    {
        return $this->BLESensorId;
    }

    /**
     * @return string
     */
    public function getBLESensorIdAsMAC(): string
    {
        return StringHelper::stringToMac($this->getBLESensorId());
    }

    /**
     * @param string $BLESensorId
     */
    public function setBLESensorId(string $BLESensorId): void
    {
        $this->BLESensorId = $BLESensorId;
    }

    /**
     * @param string $sensorId
     * @param Device $device
     * @param string $type
     * @return static
     * @throws \Exception
     */
    public static function resolve(string $sensorId, Device $device, string $type): TrackerCommandInterface
    {
        $modelName = $device->getModelName();

        switch ($modelName) {
            case DeviceModel::TOPFLYTECH_TLD1_A_E:
            case DeviceModel::TOPFLYTECH_TLW1:
            case DeviceModel::TOPFLYTECH_TLD2_L:
            case DeviceModel::TOPFLYTECH_TLW1_4:
            case DeviceModel::TOPFLYTECH_TLW1_8:
            case DeviceModel::TOPFLYTECH_TLW1_10:
            case DeviceModel::TOPFLYTECH_TLD1:
            case DeviceModel::TOPFLYTECH_TLD1_D:
            case DeviceModel::TOPFLYTECH_TLD2_D:
                return new TemperatureAndHumiditySensorCommandTLW1AndTLD1AE($sensorId, $device, $type);
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
                return new TemperatureAndHumiditySensorCommandTLD1DADE($sensorId, $device, $type);
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                return new TemperatureAndHumiditySensorCommandTLD2($sensorId, $device, $type);
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                return new TemperatureAndHumiditySensorCommandTLP1($sensorId, $device, $type);
            default:
                throw new \Exception('Unsupported device model name: ' . $modelName);
        }
    }
}