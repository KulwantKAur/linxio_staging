<?php

namespace App\Service\Tracker\Command\Topflytech\Model;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Service\Tracker\Command\Topflytech\Model\TLD1DADE\OdometerCommand as OdometerCommandTLD1DADE;
use App\Service\Tracker\Command\Topflytech\Model\TLD2\OdometerCommand as OdometerCommandTLD2;
use App\Service\Tracker\Command\Topflytech\Model\TLP1\OdometerCommand as OdometerCommandTLP1;
use App\Service\Tracker\Command\Topflytech\Model\TLW1AndTLD1AE\OdometerCommand as OdometerCommandTLW1AndTLD1AE;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;

abstract class OdometerBaseCommand extends BaseCommand
{
    private ?int $value;

    public function __construct(
        Device $device,
        string $type,
        ?int $value = null
    ) {
        parent::__construct($device, $type);
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?int
    {
        return TrackerCommandService::ODOMETER_COMMAND_TYPE;
    }

    public static function resolve(Device $device, string $type, ?int $value = null): ?TrackerCommandInterface
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
                return new OdometerCommandTLW1AndTLD1AE($device, $type, $value);
            case DeviceModel::TOPFLYTECH_TLD1_DA_DE:
                return new OdometerCommandTLD1DADE($device, $type, $value);
            case DeviceModel::TOPFLYTECH_TLW2_12BL:
            case DeviceModel::TOPFLYTECH_TLW2_2BL:
            case DeviceModel::TOPFLYTECH_TLW2_12B:
            case DeviceModel::TOPFLYTECH_PIONEERX_100:
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                return new OdometerCommandTLD2($device, $type, $value);
            case DeviceModel::TOPFLYTECH_TLP1_SF:
            case DeviceModel::TOPFLYTECH_TLP1_LF:
            case DeviceModel::TOPFLYTECH_TLP1_LM:
            case DeviceModel::TOPFLYTECH_TLP1_P:
            case DeviceModel::TOPFLYTECH_TLP1_SM:
            case DeviceModel::TOPFLYTECH_TLP2_SFB:
                return new OdometerCommandTLP1($device, $type, $value);
            case DeviceModel::TOPFLYTECH_TLD2_DA_DE:
                return null;
            default:
                throw new \Exception('Unsupported device model name: ' . $modelName);
        }
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}