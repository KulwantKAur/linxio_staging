<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Teltonika;

use App\Entity\Device;
use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\Teltonika\Exception\ParserException;
use App\Service\Tracker\Interfaces\DecoderInterface;
use App\Service\Tracker\Parser\Teltonika\Model\Data;
use App\Service\Tracker\Parser\Teltonika\Model\Imei;

class TcpDecoder implements DecoderInterface
{
    /**
     * @param $payload
     * @return bool|string
     * @throws ParserException
     */
    private function getAvlDataWithChecks(string $payload): string
    {
        $avlDataWithChecks = substr($payload, 16, -8);

        // Validating number of data;
        if (substr($avlDataWithChecks, 2, 2) !== substr($avlDataWithChecks, strlen($avlDataWithChecks) - 2, 2)) {
            throw new ParserException(
                sprintf('The first count check differs from the last count check. Initial data is "%s".', $payload)
            );
        }

        return $avlDataWithChecks;
    }

    /**
     * @param string $payload
     * @return bool
     */
    public function isAuthentication(string $payload): bool
    {
        $firstByte = substr($payload, 0, 8);

        return hexdec($firstByte) !== 0;
    }

    /**
     * @param string $payload
     * @return bool
     */
    public function isData(string $payload): bool
    {
        return !$this->isAuthentication($payload);
    }

    /**
     * @param string $payload
     * @return Imei
     */
    public function decodeAuthentication(string $payload): ImeiInterface
    {
        $hexImei = substr($payload, 4);

        return Imei::createFromHex($hexImei);
    }

    /**
     * @param string $payload
     * @param Device|null $device
     * @return array
     * @throws ParserException
     */
    public function decodeData(string $payload, ?Device $device = null): array
    {
        $avlDataWithChecks = $this->getAvlDataWithChecks($payload);
        $numberOfElements = hexdec(substr($avlDataWithChecks, 2, 2));
        $avlData = substr($avlDataWithChecks, 4, -2);

        $position = 0;
        $resultData = [];

        for ($i = 0; $i < $numberOfElements; $i++) {
            $resultData[] = Data::createFromHex($avlData, $position);
        }

        return $resultData;
    }

    /**
     * @param array $data
     * @return array
     */
    public function orderByDateTime(array $data): array
    {
        usort($data, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a->getDateTime() < $b->getDateTime()) ? -1 : 1;
        });

        return $data;
    }

    /**
     * @param string $payload
     * @param string $modelName
     * @param \DateTimeInterface $createdAt
     * @return array
     * @throws \Exception
     */
    public function encodePayloadWithNewDateTime(
        string $payload,
        string $modelName,
        \DateTimeInterface $createdAt
    ): array {
        $avlDataWithChecks = $this->getAvlDataWithChecks($payload);
        $numberOfElements = hexdec(substr($avlDataWithChecks, 2, 2));
        $avlData = substr($avlDataWithChecks, 4, -2);
        $position = 0;
        $resultData = [];

        for ($i = 0; $i < $numberOfElements; $i++) {
            $dtPayload = Data::getTimestampPayload($avlData, $position);
            $dt = Data::getDateTimeValue($dtPayload);
            $dtNeeded = (new \DateTime())->getTimestamp() - ($createdAt->getTimestamp() - $dt->getTimestamp());
            $dt->setTimestamp($dtNeeded);
            $dtString = Data::encodeDateTime($dt);
            $avlData = Data::getPayloadWithNewDateTime($avlData, $position, $dtString);
            $resultData[] = Data::createFromHex($avlData, $position);
        }

        $payload = substr_replace($payload, $avlData, 20, -10);

        return [
            'payload' => $payload,
            'createdAt' => $dt ?? null,
        ];
    }

    /**
     * @param string $hex
     * @return string
     * @throws \Exception
     * @example '0000017BCF9BCDC0'
     */
    public static function convertHexToDateTime(string $hex): string
    {
        $timestamp = hexdec($hex);
        $dateTime = (new \DateTime());
        $dateTime->setTimestamp(intval($timestamp / 1000));

        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @param string $dateTime
     * @return string
     * @throws \Exception
     * @example '2021-09-14 10:00:01'
     */
    public static function convertDateTimeToHex(string $dateTime): string
    {
        $dateTime = (new \DateTime($dateTime));

        return dechex($dateTime->getTimestamp() * 1000);
    }
}
