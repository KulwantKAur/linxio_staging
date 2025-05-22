<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLD1DADE;

use App\Service\Tracker\Command\Topflytech\Model\DriverSensorIdBaseCommand;

class DriverSensorIdCommand extends DriverSensorIdBaseCommand
{
    /**
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return 'BTMACA,' . $this->getBLEMACIdAsMAC() . '#';
    }

    /**
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return 'BTMACD,' . $this->getBLEMACIdAsMAC() . '#';
    }

    /**
     * @inheritDoc
     */
    function getListCommand(): ?string
    {
        // TODO: Implement getListCommand() method.
    }
}