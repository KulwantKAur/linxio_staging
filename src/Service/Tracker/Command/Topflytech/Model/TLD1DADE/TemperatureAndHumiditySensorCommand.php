<?php

namespace App\Service\Tracker\Command\Topflytech\Model\TLD1DADE;

use App\Service\Tracker\Command\Topflytech\Model\TemperatureAndHumiditySensorBaseCommand;

class TemperatureAndHumiditySensorCommand extends TemperatureAndHumiditySensorBaseCommand
{
    /**
     * @return string|null
     */
    public function getAddCommand(): ?string
    {
        return 'BTIDA,' . $this->getBLESensorIdAsMAC() . ',' . SensorTypes::TEMPERATURE_AND_HUMIDITY_SENSOR_TYPE_ID . '#';
    }

    /**
     * @return string|null
     */
    public function getDeleteCommand(): ?string
    {
        return 'BTIDD,' . $this->getBLESensorIdAsMAC() . '#';
    }

    /**
     * @inheritDoc
     * @example BTIDL,0000,A,B#
     * List all existing BLE sensor:
     *  1. A, the start BLE sensor serial number. B, the BLE sensor quantity count from A;
     *  2. A's range is 1-40;
     *  3. Make sure B ≤20;
     */
    function getListCommand(): ?string
    {
        return 'BTIDL,1,20#';
    }
}