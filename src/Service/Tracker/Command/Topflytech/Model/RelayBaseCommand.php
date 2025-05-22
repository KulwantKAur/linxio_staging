<?php

namespace App\Service\Tracker\Command\Topflytech\Model;

use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;

class RelayBaseCommand extends BaseCommand
{
    public function __construct(
        Device         $device,
        string         $actionType = TrackerCommandService::ADD_ACTION_TYPE
    ) {
        parent::__construct($device, $actionType);
    }

    public function getType(): ?int
    {
        return TrackerCommandService::RELAY_COMMAND_TYPE;
    }

    public function getSetCommand(): ?string
    {
        return 'RELAY,0000,1';
    }

    public static function resolve(Device $device, string $type): ?TrackerCommandInterface
    {
        $modelName = $device->getModelName();

        switch ($modelName) {
            case DeviceModel::TOPFLYTECH_PIONEERX_101:
                return new self($device, $type);
            default:
                return null;
        }
    }
}