<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLD2;

use App\Service\Tracker\Command\Topflytech\Model\TemperatureAndHumiditySensorBaseCommand;

class TemperatureAndHumiditySensorCommand extends TemperatureAndHumiditySensorBaseCommand
{
    /**
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return 'BLEIDA,' . $this->getBLESensorId() . ',' . SensorTypes::SENSOR_TYPE_ID_TSTH1_B . '#';
    }

    /**
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return 'BLEIDD,' . $this->getBLESensorId() . '#';
    }

    /**
     * @inheritDoc
     */
    function getListCommand(): ?string
    {
        // TODO: Implement getListCommand() method.
    }
}