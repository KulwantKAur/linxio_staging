<?php

namespace App\Events\Route;

use Symfony\Contracts\EventDispatcher\Event;

class RouteCalculateEvent extends Event
{
    const NAME = 'app.event.route.calculate';
    protected $deviceId;
    protected $lastDT;

    /**
     * RouteCalculateEvent constructor.
     * @param int $deviceId
     * @param \DateTimeImmutable $lastDT
     */
    public function __construct(int $deviceId, \DateTimeImmutable $lastDT)
    {
        $this->deviceId = $deviceId;
        $this->lastDT = $lastDT;
    }

    public function getDeviceId(): int
    {
        return $this->deviceId;
    }

    public function getLastDT(): \DateTimeImmutable
    {
        return $this->lastDT;
    }
}