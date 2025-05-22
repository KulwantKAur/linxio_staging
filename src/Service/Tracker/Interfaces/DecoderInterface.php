<?php

namespace App\Service\Tracker\Interfaces;

use App\Entity\Device;
use App\Entity\DeviceModel;

interface DecoderInterface
{
    /**
     * Checks if it's payload with imei authentication
     *
     * @param string $payload
     *
     * @return bool
     */
    public function isAuthentication(string $payload): bool;

    /**
     * @param string $payload
     * @return ImeiInterface
     */
    public function decodeAuthentication(string $payload): ImeiInterface;

    /**
     * @param string $payload
     * @param Device|null $device
     */
    public function decodeData(string $payload, ?Device $device = null);

    /**
     * @param array $data
     * @return array
     */
    public function orderByDateTime(array $data): array;

    /**
     * @param string $payload
     * @param string $modelName
     * @param \DateTimeInterface $createdAt
     * @return array
     */
    public function encodePayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array;
}
