<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\EncoderInterface;
use App\Service\Tracker\Parser\Ulbotech\Model\CRC16;
use App\Service\Tracker\Parser\Ulbotech\Model\HeartBeat;

class TcpEncoder implements EncoderInterface
{
    const RESPONSE_TEMPLATE = '*TS$protocol,ACK:$crc_hex#';
    const RESPONSE_FALSE = '00';

    /**
     * @param bool $isAuthenticated
     * @param string|null $payload
     * @return string
     * @throws \Exception
     */
    public function encodeAuthentication(bool $isAuthenticated, ?string $payload = null): string
    {
        $result = self::formatResponse($payload);

        return ($isAuthenticated) ? bin2hex($result) : self::RESPONSE_FALSE;
    }

    /**
     * @param string $payload
     * @return string|string[]
     * @throws \Exception
     */
    private static function formatResponse(string $payload): string
    {
        $authModel = HeartBeat::createFromTextPayload($payload);
        $crcCode = strtoupper(dechex(CRC16::hash($payload)));
        $result = str_replace('$protocol', $authModel->getProtocol(), self::RESPONSE_TEMPLATE);
        $result = str_replace('$crc_hex', $crcCode, $result);

        return $result;
    }

    /**
     * @param $textPayload
     * @param Device|null $device
     * @return string
     * @throws \Exception
     */
    public function encodeData($textPayload, ?Device $device = null): string
    {
        $result = self::formatResponse($textPayload);

        return self::encodePayload($result);
    }

    /**
     * @param string $payload     *
     * @return string
     * @throws \Exception
     */
    public static function encodePayload(string $payload): string
    {
        try {
            return bin2hex($payload);
        } catch (\Exception $exception) {
            throw new \Exception('Unable to convert payload: ' . $exception->getMessage());
        }
    }
}
