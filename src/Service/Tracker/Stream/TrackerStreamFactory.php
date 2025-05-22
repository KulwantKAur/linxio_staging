<?php

namespace App\Service\Tracker\Stream;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Service\Streamax\StreamaxService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;

class TrackerStreamFactory
{
    public function __construct(
        private EntityManager $em,
        private StreamaxService $streamaxService,
        private PaginatorInterface $paginator,
    ) {
    }

    /**
     * @param string $vendor
     * @param Device|null $device
     * @return TrackerStreamService|null
     * @throws \Exception
     */
    public function getInstance(string $vendor, ?Device $device = null)
    {
        return match ($vendor) {
            DeviceVendor::VENDOR_STREAMAX => new StreamaxStreamService($this->streamaxService, $this->paginator, $this->em),
            default => new TrackerStreamService($this->paginator, $this->em)
        };
    }
}
