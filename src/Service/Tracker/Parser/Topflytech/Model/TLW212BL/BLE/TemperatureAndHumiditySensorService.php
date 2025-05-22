<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL\BLE;

use App\Service\Tracker\Parser\Topflytech\Model\BLE\TemperatureAndHumiditySensorService as BaseTemperatureAndHumiditySensorService;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\BLE\TemperatureAndHumiditySensorService as TemperatureAndHumiditySensorServiceTLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;

class TemperatureAndHumiditySensorService extends BaseTemperatureAndHumiditySensorService
{
    public const PACKET_LENGTH = TemperatureAndHumiditySensorServiceTLW1AndTLD1AE::PACKET_LENGTH;

    public const PROTOCOL = TcpDecoder::PROTOCOL_TLW1;
}
