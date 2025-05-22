<?php

namespace App\Tests\Behat\Context;

use App\Entity\DeviceVendor;

/**
 * Defines application features from the specific context.
 */
class TrackerSimulatorTeltonikaContext extends TrackerTeltonikaContext
{
    /**
     * @When Create devices for simulator
     */
    public function createDevicesForSimulator()
    {
        $simulatorTrackerFactory = $this->getContainer()->get('tracker.simulator_tracker_factory');
        $simulatorTrackerService = $simulatorTrackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $simulatorTrackerService->generateDevices();
    }

    /**
     * @When I want to send tcp data from simulator to api with socket :socket
     */
    public function sendTcpDataFromSimulatorToApi($socket)
    {
        $trackerFactory = $this->getContainer()->get('tracker.tracker_factory');
        $trackerService = $trackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $baseImei = $trackerService->getBaseImei();
        $this->fillData['payload'] = $trackerService->convertImeiToPayload($baseImei);

        $this->sendTcpDataToApi($socket);
    }
}
