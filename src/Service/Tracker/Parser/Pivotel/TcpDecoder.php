<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Pivotel;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\Pivotel\Model\Imei;
use App\Service\Tracker\Parser\Teltonika\Exception\ParserException;
use App\Service\Tracker\Interfaces\DecoderInterface;

class TcpDecoder implements DecoderInterface, ImeiInterface
{
    private $payload;
    private $decodedPayload;

    public function __construct($payload)
    {
        $this->decodedPayload = new \SimpleXMLElement($payload);
        $this->payload = $payload;
    }

    public function getDeviceType()
    {
        return (string)$this->decodedPayload->deviceType;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return (string)$this->decodedPayload->deviceID;
    }

    /**
     * @param string $payload
     * @return bool
     */
    public function isAuthentication(string $payload): bool
    {
    }

    /**
     * @inheritDoc
     */
    public function decodeAuthentication(string $payload): ImeiInterface
    {
        $decoder = new TcpDecoder($payload);

        return new Imei($decoder->getImei());
    }

    /**
     * @param string $payload
     * @param Device|null $device
     * @return array
     * @throws ParserException
     */
    public function decodeData(string $payload, ?Device $device = null): array
    {
        return [(new Data())->createFromPayload($this->decodedPayload)];
    }

    /**
     * @param array $data
     * @return array
     */
    public function orderByDateTime(array $data): array
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function encodePayloadWithNewDateTime(string $payload, string $modelName, \DateTimeInterface $createdAt): array
    {
        return [
            'payload' => null,
            'createdAt' => null,
        ];
    }
}
