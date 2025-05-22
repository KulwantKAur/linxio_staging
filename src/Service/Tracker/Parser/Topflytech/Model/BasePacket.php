<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

class BasePacket
{
    public const IMEI_LENGTH = 15;
    public const PACKET_LENGTH = 30;

    public $header;
    public $messageType;
    public $packetLength;
    public $serialNumber;
    public $imei;

    /**
     * @param string $header
     * @param string $messageType
     * @param string $packetLength
     * @param mixed $serialNumber
     * @param mixed $imei
     * @throws \Exception
     */
    public function __construct(
        string $header,
        string $messageType,
        string $packetLength,
        $serialNumber,
        $imei
    ) {
        if (strlen($imei) !== self::IMEI_LENGTH) {
            throw new \Exception('IMEI number is not valid.');
        }

        $this->header = $header;
        $this->messageType = $messageType;
        $this->packetLength = $packetLength;
        $this->serialNumber = $serialNumber;
        $this->imei = $imei;
    }

    public function __toString(): string
    {
        return strval($this->getImei());
    }

    /**
     * @return string
     */
    public function getImei(): string
    {
        return $this->imei;
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): self
    {
        if (strlen($payload) < self::PACKET_LENGTH) {
            throw new \Exception('Tracker packet is wrong, skipped.');
        }

        $header = substr($payload, 0, 4);
        $messageType = substr($payload, 4, 2);
        $packetLength = substr($payload, 6, 4);
        $serialNumber = substr($payload, 10, 4);
        $imei = substr($payload, 15, 15);

        return new self($header, $messageType, $packetLength, $serialNumber, $imei);
    }

    /**
     * @return string
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }

    /**
     * @return string
     */
    public function getPacketLength(): string
    {
        return $this->packetLength;
    }

    /**
     * @return mixed
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->header;
    }
}
