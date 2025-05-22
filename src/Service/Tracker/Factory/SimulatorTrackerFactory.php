<?php

namespace App\Service\Tracker\Factory;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Service\Tracker\Interfaces\SimulatorTrackerInterface;
use App\Service\Tracker\SimulatorTrackerService;
use App\Service\Tracker\TeltonikaSimulatorTrackerService;
use App\Service\Tracker\TopflytechSimulatorTrackerService;
use App\Service\Tracker\UlbotechSimulatorTrackerService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SimulatorTrackerFactory
{
    private $em;
    private $logger;
    private $translator;
    private $simulatorBaseImei;
    private $simulatorDevicesCount;
    private $devicesOffsetOnTrackTs;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param int $simulatorBaseImei
     * @param int $simulatorDevicesCount
     * @param int $devicesOffsetOnTrackTs
     */
    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        int $simulatorBaseImei,
        int $simulatorDevicesCount,
        int $devicesOffsetOnTrackTs
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->simulatorBaseImei = $simulatorBaseImei;
        $this->simulatorDevicesCount = $simulatorDevicesCount;
        $this->devicesOffsetOnTrackTs = $devicesOffsetOnTrackTs;
    }

    /**
     * @param string $vendor
     * @param Device|null $device
     * @return SimulatorTrackerInterface|SimulatorTrackerService
     *
     * @throws \Exception
     */
    public function getInstance(string $vendor, ?Device $device = null): SimulatorTrackerService
    {
        switch ($vendor) {
            case DeviceVendor::VENDOR_TELTONIKA:
                return new TeltonikaSimulatorTrackerService(
                    $this->em,
                    $this->logger,
                    $this->translator,
                    $this->simulatorBaseImei,
                    $this->simulatorDevicesCount,
                    $this->devicesOffsetOnTrackTs
                );
            case DeviceVendor::VENDOR_TOPFLYTECH:
                return new TopflytechSimulatorTrackerService(
                    $this->em,
                    $this->logger,
                    $this->translator
                );
            case DeviceVendor::VENDOR_ULBOTECH:
                return new UlbotechSimulatorTrackerService(
                    $this->em,
                    $this->logger,
                    $this->translator
                );
            default:
                $message = 'Unsupported vendor: ' . $vendor;
                $message .= $device ? ' for device imei: ' . $device->getImei() : '';
                throw new \Exception($message);
        }
    }

    /**
     * @param Device $device
     *
     * @return SimulatorTrackerService
     * @throws \Exception
     */
    public function getInstanceByDevice(Device $device): SimulatorTrackerService
    {
        return $this->getInstance($device->getVendorName(), $device);
    }

    /**
     * @param string $vendorName
     * @return SimulatorTrackerService
     * @throws \Exception
     */
    public function getInstanceByVendorName(string $vendorName): SimulatorTrackerService
    {
        return $this->getInstance($vendorName);
    }
}
