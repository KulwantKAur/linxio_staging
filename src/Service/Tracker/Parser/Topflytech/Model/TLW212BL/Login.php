<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLW212BL;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseLogin;
use App\Service\Tracker\Parser\Topflytech\Model\BasePacket;

class Login extends BaseLogin
{
    private $basePacket;
    private $MCUVersion;
    private $modemVersion;
    private $modemAppVersion;
    private $hardwareVersion;

    /**
     * @param BasePacket $basePacket
     * @param $data
     */
    public function __construct(BasePacket $basePacket, array $data)
    {
        $this->basePacket = $basePacket;
        $this->MCUVersion = $data['MCUVersion'] ?? null;
        $this->modemVersion = $data['modemVersion'] ?? null;
        $this->modemAppVersion = $data['modemAppVersion'] ?? null;
        $this->hardwareVersion = $data['hardwareVersion'] ?? null;
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
     * @return self
     * @throws \Exception
     */
    public static function createFromTextPayload(string $payload): BaseLogin
    {
        $basePacket = BasePacket::createFromTextPayload($payload);

        return new self($basePacket, [
            'MCUVersion' => substr($payload, 30, 4), // @todo
            'modemVersion' => substr($payload, 34, 6), // @todo
            'modemAppVersion' => DataHelper::implodeStringWithDot(substr($payload, 40, 4)),
            'hardwareVersion' => DataHelper::implodeStringWithDot(substr($payload, 44, 2)),
        ]);
    }
}
