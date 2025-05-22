<?php

namespace App\Service\Tracker\Factory;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Service\Tracker\Command\Pivotel\PivotelTrackerCommandService;
use App\Service\Tracker\Command\Teltonika\TeltonikaTrackerCommandService;
use App\Service\Tracker\Command\Topflytech\TopflytechTrackerCommandService;
use App\Service\Tracker\Command\Traccar\TraccarCommandService;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Command\Ulbotech\UlbotechTrackerCommandService;
use Doctrine\ORM\EntityManager;

class TrackerCommandFactory
{
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    /**
     * @param string $vendor
     * @param Device|null $device
     * @return TrackerCommandService|null
     * @throws \Exception
     */
    public function getInstance(string $vendor, ?Device $device = null)
    {
        return match ($vendor) {
            DeviceVendor::VENDOR_TOPFLYTECH =>
                new TopflytechTrackerCommandService(
                    $this->em
                ),
            default => new TrackerCommandService()
        };
    }
}
