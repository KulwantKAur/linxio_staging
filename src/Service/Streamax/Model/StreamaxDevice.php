<?php

namespace App\Service\Streamax\Model;

use App\Service\Tracker\Interfaces\ImeiInterface;

/**
 * @example {"channels":"","deviceType":"AD_PLUS_V2/AD_LITE","dormantState":"UNKNOWN","fleetId":"809656229809915118","lanWan":"PUBLIC_NETWORK","onlineState":"OFFLINE","uniqueId":"00D2000C9B1"}
 */
class StreamaxDevice extends StreamaxModel implements ImeiInterface
{
    public ?string $uniqueId;
    public ?string $channels;
    public ?string $deviceType;
    public ?string $onlineState;
    public ?string $fleetId;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->uniqueId = $fields['uniqueId'] ?? null;
        $this->channels = $fields['channels'] ?? null;
        $this->deviceType = $fields['deviceType'] ?? null;
        $this->onlineState = $fields['onlineState'] ?? null;
        $this->fleetId = $fields['fleetId'] ?? null;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @inheritDoc
     */
    public function getImei(): string
    {
        return $this->getUniqueId();
    }

    /**
     * @return string|null
     */
    public function getDeviceType(): ?string
    {
        return $this->deviceType;
    }

    /**
     * @param string|null $deviceType
     */
    public function setDeviceType(?string $deviceType): void
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return string|null
     */
    public function getChannels(): ?string
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'uniqueId' => $this->getUniqueId(),
            'deviceType' => $this->getDeviceType(),
            'channels' => $this->getChannels(),
        ];
    }
}

