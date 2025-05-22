<?php

namespace App\Service\Traccar\Model;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;

class TraccarApiDecoder implements DecoderInterface
{
    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getImei()
    {
        return (string) $this->payload->deviceID;
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
     * @param string $payload
     * @param Device|null $device
     * @return array
     * @throws \Exception
     */
    public function decodeData(string $payload, ?Device $device = null): array
    {
        $data = TraccarModel::convertObjectToArray(json_decode($payload));

        return [
            (new TraccarData())->createFromDataArray($data, $device)
        ];
    }

    /**
     * @param string $payload
     * @return bool
     */
    public function isAuthentication(string $payload): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function encodePayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array {
        return [
            'payload' => null,
            'createdAt' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function decodeAuthentication(string $payload): ImeiInterface
    {
        // TODO: Implement decodeAuthentication() method.
    }
}
