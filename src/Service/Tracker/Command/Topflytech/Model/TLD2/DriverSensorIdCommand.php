<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLD2;

use App\Service\Tracker\Command\Topflytech\Model\DriverSensorIdBaseCommand;

class DriverSensorIdCommand extends DriverSensorIdBaseCommand
{
    /**
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return 'BLEIDA,' . $this->getBLEMACId() . ',' . SensorTypes::SENSOR_TYPE_ID_DRIVER_ID . '#';
    }

    /**
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return 'BLEIDD,' . $this->getBLEMACId() . ',#';
    }

    /**
     * @inheritDoc
     */
    function getListCommand(): ?string
    {
        // TODO: Implement getListCommand() method.
    }
}