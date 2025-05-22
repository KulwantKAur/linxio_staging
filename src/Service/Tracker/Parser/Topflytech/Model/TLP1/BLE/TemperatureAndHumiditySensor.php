<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\BLE\TemperatureAndHumiditySensor as TemperatureAndHumiditySensorBase;

/**
 * @example 272710002700570888888888888888201026124251010004ca110625cb8aff6453E016a800007027270200490346086011204706290800002010261242548136019a03471e1013f600f113f600f181080180008915ff3900000288e3020400780000
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
