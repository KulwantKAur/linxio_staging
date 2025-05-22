<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLP1;

use App\Service\Tracker\Command\Topflytech\Model\TemperatureAndHumiditySensorBaseCommand;

class TemperatureAndHumiditySensorCommand extends TemperatureAndHumiditySensorBaseCommand
{
    /**
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return 'BLEIDA,' . $this->getBLESensorIdAsMAC() . ',' . SensorTypes::SENSOR_TYPE_ID_TSTH1_B . '#';
    }

    /**
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return 'BLEIDD,' . $this->getBLESensorIdAsMAC() . '#';
    }

    /**
     * @inheritDoc
     */
    function getListCommand(): ?string
    {
        // TODO: Implement getListCommand() method.
    }
}