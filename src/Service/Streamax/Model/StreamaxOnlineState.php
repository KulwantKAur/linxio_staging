<?php

namespace App\Service\Streamax\Model;

use App\Service\Tracker\Interfaces\DateTimePartPayloadInterface;
use App\Service\Tracker\Interfaces\ImeiInterface;

class StreamaxOnlineState extends StreamaxModel implements ImeiInterface, DateTimePartPayloadInterface
{
    private const STATE_ONLINE = 'ONLINE';
    private const STATE_SLEEP_ONLINE = 'SLEEP_ONLINE';
    private const STATE_OFFLINE = 'OFFLINE';

    public string $time; // RFC3339
    public ?string $uniqueId; // Unique identifier of a device
    public ?string $vehicleId;
    public ?string $state;
    public ?string $networkState;

    /**
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->state = $fields['state'] ?? null;
        $this->networkState = $fields['networkState'] ?? null;
        $this->time = $fields['time'];
        $this->uniqueId = $fields['uniqueId'] ?? null;
        $this->vehicleId = $fields['vehicleId'] ?? null;
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
     * @return array
     */
    public function toAPIArray(): array
    {
        return [
            'uniqueId' => $this->getUniqueId(),
            'time' => $this->getTime(),
            'state' => $this->getState(),
        ];
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->getState() === self::STATE_ONLINE || $this->getState() === self::STATE_SLEEP_ONLINE;
    }

    /**
     * @return bool
     */
    public function isOffline(): bool
    {
        return $this->getState() === self::STATE_OFFLINE;
    }

    /**
     * @inheritDoc
     */
    public function getPayloadWithNewDateTime(string $payload, string $dtString): string
    {
        // TODO: Implement getPayloadWithNewDateTime() method.
    }

    /**
     * @inheritDoc
     */
    public function getDateTimePayload(string $payload): string
    {
        // TODO: Implement getDateTimePayload() method.
    }
}

