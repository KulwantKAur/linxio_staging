<?php

namespace App\Service\Tracker\Command\Topflytech\Model;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Service\Tracker\Command\Topflytech\Model\TLD1DADE\DriverSensorIdCommand as DriverSensorIdCommandTLD1DADE;
use App\Service\Tracker\Command\Topflytech\Model\TLD2\DriverSensorIdCommand as DriverSensorIdCommandTLD2;
use App\Service\Tracker\Command\Topflytech\Model\TLP1\DriverSensorIdCommand as DriverSensorIdCommandTLP1;
use App\Service\Tracker\Command\Topflytech\Model\TLW1AndTLD1AE\DriverSensorIdCommand as DriverSensorIdCommandTLW1AndTLD1AE;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;
use App\Util\StringHelper;

abstract class DriverSensorIdBaseCommand extends BaseCommand
{
    /** @var string $BLEMACId */
    private $BLEMACId;

    /**
     * @param string $BLEMACId
     * @param Device $device
     * @param string $actionType
     */
    public function __construct(
        string $BLEMACId,
        Device $device,
        string $actionType = TrackerCommandService::ADD_ACTION_TYPE
    ) {
        parent::__construct($device, $actionType);
        $this->BLEMACId = $BLEMACId;
    }

    /**
     * @return string
     */
    public function getBLEMACId(): string
    {
        return $this->BLEMACId;
    }

    /**
     * @return string
     */
    public function getBLEMACIdAsMAC(): string
    {
        return StringHelper::stringToMac($this->getBLEMACId());
    }

    /**
     * @param string $BLEMACId
     */
    public function setBLEMACId(string $BLEMACId): void
    {
        $this->BLEMACId = $BLEMACId;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?int
    {
        return TrackerCommandService::IBUTTON_COMMAND_TYPE;
    }

    /**
     * @param string $driverSensorId
     * @param Device $device
     * @param string $type
     * @return static
     * @throws \Exception
     */
    public static function resolve(string $driverSensorId, Device $device, string $type): TrackerCommandInterface
    {
        $modelName = $device->getModelName();

        switch ($modelName) {
            case DeviceModel::TOPFLYTECH_TLD1_A_E:
            case DeviceModel::TOPFLYTECH_TLW1:
            case DeviceModel::TOPFLYTECH_TLW1_4:
            case DeviceModel::TOPFLYTECH_TLW1_8:
            case DeviceModel::TOPFLYTECH_TLW1_10:
            case DeviceModel::TOPFLYTECH_TLD1:
            case DeviceModel::TOPFLYTECH_TLD1_D:
            case DeviceModel::TOPFLYTECH_TLD2_D:
                return new DriverSensorIdCommandTLW1AndTLD1AE($driverSensorId, $device, $type);
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
                return new DriverSensorIdCommandTLD1DADE($driverSensorId, $device, $type);
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                return new DriverSensorIdCommandTLD2($driverSensorId, $device, $type);
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                return new DriverSensorIdCommandTLP1($driverSensorId, $device, $type);
            default:
                throw new \Exception('Unsupported device model name: ' . $modelName);
        }
    }
}