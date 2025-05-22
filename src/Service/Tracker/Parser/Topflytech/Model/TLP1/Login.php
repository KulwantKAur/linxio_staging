<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLP1;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseLogin;
use App\Service\Tracker\Parser\Topflytech\Model\BasePacket;

/**
 * @example 2727010017000108806168988888881016010207110111
 */
class Login extends BaseLogin
{
    private $basePacket;
    private ?string $firmwareVersion;
    private ?string $hardwareVersion;

    /**
     * @param string $payload
     * @return array
     */
    private static function parse(string $payload)
    {
        return [
            'MCUModel' => substr($payload, 0, 1),
            'firmwareVersion' => DataHelper::implodeStringWithDot(substr($payload, 1, 3)),
            'modemVersion' => substr($payload, 4, 6),
            'modemAppVersion' => DataHelper::implodeStringWithDot(substr($payload, 10, 4)),
            'hardwareVersion' => DataHelper::implodeStringWithDot(substr($payload, 14, 2)),
        ];
    }

    /**
     * @param BasePacket $basePacket
     * @param $versionData
     */
    public function __construct(BasePacket $basePacket, array $versionData)
    {
        $this->basePacket = $basePacket;
        $this->firmwareVersion = $versionData['firmwareVersion'] ?? null;
        $this->hardwareVersion = $versionData['hardwareVersion'] ?? null;
        $this->setFWVersion($this->firmwareVersion);
        $this->setHWVersion($this->hardwareVersion);
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
        $versionData = self::parse(substr($payload, 30));

        return new self($basePacket, $versionData);
    }
}
