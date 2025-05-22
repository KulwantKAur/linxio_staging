<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;

class HeartBeat implements ImeiInterface
{
    const IMEI_LENGTH = 15;

    /**
     * @var string
     */
    private $imei;
    private $protocol;

    /**
     * @param string $imei
     * @param string $protocol
     * @throws \Exception
     */
    public function __construct(string $imei, string $protocol)
    {
        if (strlen($imei) !== self::IMEI_LENGTH) {
            throw new \Exception('IMEI number is not valid.');
        }

        $this->imei = $imei;
        $this->protocol = $protocol;
    }

    public function __toString(): string
    {
        return $this->getImei();
    }

    /**
     * @return string
     */
    public function getImei(): string
    {
        return $this->imei;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @param string $payload
     * @return HeartBeat
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): self
    {
        $protocol = substr($payload, 3, 2);
        $imei = substr($payload, (strpos($payload, ',') + 1), 15);

        return new self($imei, $protocol);
    }
}
