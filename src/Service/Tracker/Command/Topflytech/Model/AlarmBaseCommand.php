<?php
namespace App\Service\Tracker\Command\Topflytech\Model;
use App\Entity\Device;
use App\Entity\DeviceModel;
use App\Entity\Tracker\TrackerCommandType;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Interfaces\TrackerCommandInterface;

class AlarmBaseCommand extends BaseCommand
{
    public function __construct(
        Device         $device,
        string         $actionType = TrackerCommandService::SET_ACTION_TYPE
    ) {
        parent::__construct($device, $actionType);
    }

    public function getType(): ?int
    {
        return TrackerCommandService::ALARM_COMMAND_TYPE;
    }

    public function getSetCommand(): ?string
    {
        // return 'DOUT,0000,0,1';
        return 'TIMER,0000,60:3599:20:100';
    }

    public static function resolve(Device $device, string $type): ?TrackerCommandInterface
    {
        return new self($device, $type);
//        $modelName = $device->getModelName();
//        switch ($modelName) {
//            case DeviceModel::TOPFLYTECH_PIONEERX_101:
//                return new self($device, $type);
//            default:
//                return null;
//        }
    }
}