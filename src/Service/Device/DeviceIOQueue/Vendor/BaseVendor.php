<?php

namespace App\Service\Device\DeviceIOQueue\Vendor;

use App\Entity\DeviceVendor;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManager;

class BaseVendor
{
    public $em;
    public $notificationDispatcher;

    /**
     * @param int $deviceId
     * @param array $trackerDataSet
     * @param Vehicle $vehicle
     * @return Vehicle
     */
    public function calc(int $deviceId, array $trackerDataSet, Vehicle $vehicle): Vehicle
    {
        return $vehicle;
    }

    /**
     * @param string $vendorName
     * @param EntityManager $em
     * @param $notificationDispatcher
     * @return static
     * @throws \Exception
     */
    public static function resolve(string $vendorName, $em, $notificationDispatcher): self
    {
        return match ($vendorName) {
            DeviceVendor::VENDOR_TOPFLYTECH => new Topflytech($em, $notificationDispatcher),
            default => new self(),
        };
    }
}
