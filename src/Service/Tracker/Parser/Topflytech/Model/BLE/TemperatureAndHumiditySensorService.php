<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\BLE;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;

/**
 * @todo batch packet
 *
 * @example 272710002700570888888888888888201026124251010004ca110625cb8aff6407cb16a800007027270200490346086011204706290800002010261242548136019a03471e1013f600f113f600f181080180008915ff3900000288e3020400780000
 * Each part should be extended with 272710002700570888888888888888201026124251010004 in start of string
 * 1 part: ca110625cb8aff6407cb16a8000070
 * 2 part: 272702004903460860112047062908
 * 3 part: 00002010261242548136019a03471e
 * 4 part: 1013f600f113f600f1810801800089
 * 5 part: 15ff3900000288e3020400780000 (need to add 2 zeros to the end of string)
 */
abstract class TemperatureAndHumiditySensorService extends BaseDataCode
{
    public const PACKET_LENGTH = 30;
    public const PROTOCOL = null;

    public $data;

    /**
     * @param string $payloadPart
     * @return bool
     */
    protected static function isPartOfNewPacket(string $payloadPart): bool
    {
        $protocolMatch = substr($payloadPart, 0, 4) == static::PROTOCOL;
        $typeMatch = substr($payloadPart, 4, 1) == '0' || '1' || '8';

        return $protocolMatch && $typeMatch;
    }

    /**
     * @param TemperatureAndHumiditySensor[]|array $data
     * @return array
     * @throws \Exception
     */
    protected static function filterSensorsByRSSI(array $data): array
    {
        $maxRSSI = null;

        foreach ($data as $key => $sensor) {
            $sensorRSSI = $sensor->getRSSI();
            $maxRSSI = $maxRSSI && $sensorRSSI < $maxRSSI ? $maxRSSI : $sensorRSSI;
        }

        foreach ($data as $key => $sensor) {
            $sensorRSSI = $sensor->getRSSI();

            if ($sensorRSSI < $maxRSSI - BaseBLE::RSSI_ACCURACY) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $data = [];
        $payloadParts = self::formatPayloadParts($textPayload);

        foreach ($payloadParts as $payloadPart) {
            if (self::isPartOfNewPacket($payloadPart)) {
                break;
            }

            $data[] = TemperatureAndHumiditySensor::createFromTextPayload($payloadPart);
        }

        return new static($data);
    }

    /**
     * @param string $textPayload
     * @return array
     * @throws \Exception
     */
    public static function formatPayloadParts(string $textPayload): array
    {
        $payloadParts = str_split($textPayload, static::PACKET_LENGTH);

        return array_map(function ($part) {
            return self::formatPacketLength($part);
        }, $payloadParts);
    }

    /**
     * @param string $textPayload
     * @return self
     * @throws \Exception
     */
    public static function formatPacketLength(string $textPayload): string
    {
        return strlen($textPayload) < static::PACKET_LENGTH
            ? DataHelper::addZerosToEndOfString($textPayload, static::PACKET_LENGTH)
            : $textPayload;
    }

    /**
     * @inheritDoc
     */
    public function getDataArray(): ?array
    {
        return $this->getTempAndHumidityData();
    }

    /**
     * @return array|null
     */
    public function getTempAndHumidityData()
    {
        return $this->data;
    }
}
