<?php

namespace App\Service\Tracker\Command\Topflytech\Model;

use App\Entity\DeviceVendor;
use App\Service\Tracker\Command\Topflytech\TopflytechTrackerCommandService;
use App\Service\Tracker\Command\TrackerCommand;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;

abstract class BaseCommand extends TrackerCommand
{
    public const VENDOR_NAME = DeviceVendor::VENDOR_TOPFLYTECH;

    /**
     * @inheritDoc
     */
    public function getCommand(): ?string
    {
        $command = parent::getCommand();

        return TopflytechTrackerCommandService::formatCommandAsHEX(
            TcpDecoder::getProtocolByModelName($this->getDevice()->getModelName()),
            $this->getDevice()->getImei(),
            $command
        );
    }
}