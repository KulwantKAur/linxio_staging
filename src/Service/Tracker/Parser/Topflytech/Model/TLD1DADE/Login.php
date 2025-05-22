<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE;

use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Topflytech\Model\BaseLogin;
use App\Service\Tracker\Parser\Topflytech\Model\BasePacket;

/**
 * @example 26260100190001088061689888888811101162501301010611
 */
class Login extends BaseLogin
{
    private $basePacket;
    private $basicVersion;
    private $firmwareVersion;
    private $platform;
    private $hardwareVersion;
    private $obdData;

    /**
     * @param BasePacket $basePacket
     * @param $versionData
     */
    public function __construct(BasePacket $basePacket, array $versionData)
    {
        $this->basePacket = $basePacket;
        $this->basicVersion = $versionData['basicVersion'] ?? null;
        $this->firmwareVersion = $versionData['firmwareVersion'] ?? null;
        $this->platform = $versionData['platform'] ?? null;
        $this->hardwareVersion = $versionData['hardwareVersion'] ?? null;
        $this->obdData = $versionData['obdData'] ?? null;
        $this->setFWVersion($this->basicVersion . '-' . $this->firmwareVersion);
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

    /**
     * @param string $payload
     * @return array
     */
    private static function parse(string $payload)
    {
        return [
            'basicVersion' => DataHelper::implodeStringWithDot(substr($payload, 0, 3)),
            'firmwareVersion' => DataHelper::implodeStringWithDot(substr($payload, 3, 3)),
            'platform' => substr($payload, 6, 4),
            'hardwareVersion' => DataHelper::implodeStringWithDot(substr($payload, 10, 2)),
            'obdData' => self::formatObdData(substr($payload, 12)),
        ];
    }

    /**
     * @param string $payload
     * @return string
     */
    private static function formatObdData(string $payload)
    {
        // @todo for future
        return $payload;
    }
}
