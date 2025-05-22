<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Teltonika;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\EncoderInterface;

class TcpEncoder implements EncoderInterface
{
    const RESPONSE_TRUE = '01';
    const RESPONSE_FALSE = '00';

    /**
     * @param bool $isAuthenticated
     * @param string|null $payload
     * @return string
     */
    public function encodeAuthentication(bool $isAuthenticated, ?string $payload = null): string
    {
        return ($isAuthenticated) ? self::RESPONSE_TRUE : self::RESPONSE_FALSE;
    }

    /**
     * @inheritDoc
     */
    public function encodeData($data, ?Device $device = null)
    {
        return count($data);
    }

    /**
     * @param string $imei
     * @return string
     */
    public function convertImeiToPayload(string $imei): string
    {
        return '000F' . bin2hex($imei);
    }
}
