<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\BLE\TemperatureAndHumiditySensorService as BaseTemperatureAndHumiditySensorService;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;

class TemperatureAndHumiditySensorService extends BaseTemperatureAndHumiditySensorService
{
    public const PACKET_LENGTH = 30;

    public const PROTOCOL = TcpDecoder::PROTOCOL_TLP1;
}
