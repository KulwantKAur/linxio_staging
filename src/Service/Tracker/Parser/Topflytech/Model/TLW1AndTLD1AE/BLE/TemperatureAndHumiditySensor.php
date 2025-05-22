<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\BLE\TemperatureAndHumiditySensor as TemperatureAndHumiditySensorBase;

/**
 * @example
 */
class TemperatureAndHumiditySensor extends TemperatureAndHumiditySensorBase
{
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
