<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;

class HeartBeat implements ImeiInterface
{
    private $basePacket;

    /**
     * @param BasePacket $basePacket
     */
    public function __construct(BasePacket $basePacket)
    {
        $this->basePacket = $basePacket;
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
        return $this->basePacket->getImei();
    }

    /**
     * @param string $payload
     * @return HeartBeat
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): self
    {
        $basePacket = BasePacket::createFromTextPayload($payload);

        return new self($basePacket);
    }
}
