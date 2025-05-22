<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;
use App\Service\Tracker\Parser\Topflytech\Model\TLW1AndTLD1AE\Login AS LoginTLW1AndTLD1AE;
use App\Service\Tracker\Parser\Topflytech\Model\TLP1\Login AS LoginTLP1;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\Login AS LoginTLD1DADE;
use App\Service\Tracker\Parser\Topflytech\TcpDecoder;

abstract class BaseLogin implements ImeiInterface
{
    public ?string $HWVersion = null;
    public ?string $FWVersion = null;

    /**
     * @return BasePacket
     */
    public function getBasePacket(): BasePacket
    {
        return $this->basePacket;
    }

    /**
     * @return string
     */
    public function getImei(): string
    {
        return $this->getBasePacket()->getImei();
    }

    /**
     * @param string $payload
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): self
    {
        $protocol = (new TcpDecoder())->getProtocol($payload);

        switch ($protocol) {
            case TcpDecoder::PROTOCOL_TLW1:
                return LoginTLW1AndTLD1AE::createFromTextPayload($payload);
            case TcpDecoder::PROTOCOL_TLD1DADE:
                return LoginTLD1DADE::createFromTextPayload($payload);
            case TcpDecoder::PROTOCOL_TLP1:
                return LoginTLP1::createFromTextPayload($payload);
            default:
                throw new \Exception('Unsupported device protocol: ' . $protocol);
        }
    }

    /**
     * @return string|null
     */
    public function getHWVersion(): ?string
    {
        return $this->HWVersion;
    }

    /**
     * @return string|null
     */
    public function getFWVersion(): ?string
    {
        return $this->FWVersion;
    }

    /**
     * @param string|null $HWVersion
     * @return BaseLogin
     */
    public function setHWVersion(?string $HWVersion): BaseLogin
    {
        $this->HWVersion = ($HWVersion && strlen($HWVersion) < 255) ? $HWVersion : null;

        return $this;
    }

    /**
     * @param string|null $FWVersion
     * @return BaseLogin
     */
    public function setFWVersion(?string $FWVersion): BaseLogin
    {
        $this->FWVersion = ($FWVersion && strlen($FWVersion) < 255) ? $FWVersion : null;

        return $this;
    }
}
