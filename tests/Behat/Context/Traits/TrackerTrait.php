<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\AreaHistory;
use App\Entity\Tracker\Teltonika\TrackerSimulatorTrack;
use App\Entity\Tracker\TrackerAuth;

trait TrackerTrait
{
    use TrackerTeltonikaTrait;
    use TrackerUlbotechTrait;
    use TrackerTopflytechTrait;

    /**
     * @When I want check tracker auth by socket :socket
     */
    public function checkTrackerAuth($socket)
    {
        $trackerAuth = $this->getEntityManager()
            ->getRepository(TrackerAuth::class)
            ->findOneBySocketId($socket);

        if (!$trackerAuth) {
            throw new \Exception('Auth tracker is not found');
        }

        $this->fillData['tracker_auth_id'] = $trackerAuth->getId();
    }

    /**
     * @When I want to get tracker data log
     */
    public function getTrackerDataLog()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/tracker/logs?' . $params);
    }

    /**
     * @When I want to generate track for simulator
     */
    public function generateTrackForSimulator()
    {
        $this->post('/api/tracker/simulator/generate-track', $this->fillData);
    }

    /**
     * @When I want to check if :number tracks have been generated
     */
    public function checkGeneratedTrack($number = 1)
    {
        $tracks = $this->getEntityManager()->getRepository(TrackerSimulatorTrack::class)->findAll();
        $actualNumber = count($tracks);

        if ($actualNumber != $number) {
            throw new \Exception("There have been generated $actualNumber track(s) instead of $number.");
        }
    }

    /**
     * @When I want check area history for current vehicle, area and driver arrived
     */
    public function IWantCheckAreaHistoryForCurrentVehicleAreaDriverArrived()
    {
        $this->getEntityManager()->clear();
        $areaHistory = $this->getEntityManager()->getRepository(AreaHistory::class)->findAll();

        if (!$areaHistory) {
            throw new \Exception('Area history is empty.');
        }

        if (
            !($areaHistory[0]->getArea()->getId() === $this->areaData->id &&
            $areaHistory[0]->getVehicle()->getId() === $this->vehicleData->id &&
            $areaHistory[0]->getDriverArrived()->getId() === $this->driverId)
        ) {
            throw new \Exception('There is no such driver who has arrived in the current vehicle to current area.');
        }
    }

    /**
     * @When I want check area history for current vehicle, area and driver departed
     */
    public function IWantCheckAreaHistoryForCurrentVehicleAreaDriverDeparted()
    {
        $this->getEntityManager()->clear();
        $areaHistory = $this->getEntityManager()->getRepository(AreaHistory::class)->findAll();

        if (!$areaHistory) {
            throw new \Exception('Area history is empty');
        }

        if (
            !($areaHistory[0]->getArea()->getId() === $this->areaData->id &&
            $areaHistory[0]->getVehicle()->getId() === $this->vehicleData->id &&
            $areaHistory[0]->getDriverDeparted()->getId() === $this->driverId)
        ) {
            throw new \Exception('There is no such driver who has arrived in the current vehicle to current area.');
        }
    }

    /**
     * @When I want check unknown devices auth
     */
    public function checkUnknownDevicesAuth()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/tracker/unknown-devices?' . $params);
    }

    /**
     * @When I want click mobile panic button
     */
    public function checkMobilePanicButton()
    {
        $this->post('/api/tracker/' . $this->authorizedUser->getId() . '/mobile-panic-button');
    }
}
