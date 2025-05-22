<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLP1;

use App\Service\Tracker\Command\Topflytech\Model\DriverSensorIdBaseCommand;

class DriverSensorIdCommand extends DriverSensorIdBaseCommand
{
    /**
     * @example BLEIDA,0000,A,B,C#
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return 'BLEIDA,' . $this->getBLEMACIdAsMAC() . ',' . SensorTypes::SENSOR_TYPE_ID_DRIVER_ID . '#';
    }

    /**
     * @example BLEIDD,0000,A,B#
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return 'BLEIDD,' . $this->getBLEMACIdAsMAC() . ',#';
    }

    /**
     * @inheritDoc
     */
    function getListCommand(): ?string
    {
        // TODO: Implement getListCommand() method.
    }
}