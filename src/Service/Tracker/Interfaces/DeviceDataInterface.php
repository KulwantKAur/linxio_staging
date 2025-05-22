<?php

namespace App\Service\Tracker\Interfaces;

use App\Service\Tracker\Parser\Topflytech\Model\BaseNetwork;
use App\Service\Tracker\Parser\Topflytech\Model\DriverBehaviorBase;
use App\Service\Tracker\Parser\Topflytech\Model\TLD1DADE\DTCAndVIN;
use App\Service\Tracker\Parser\Ulbotech\Model\HarshDriverBehavior;

interface DeviceDataInterface
{
    public function getDateTime(): ?\DateTimeInterface;

    public function getGpsData(): GpsDataInterface;

    public function getDriverIdTag(): ?string;

    /**
     * @return int|bool|null
     */
    public function getIgnition(?bool $isFixWithSpeed = null);

    /**
     * @return int|bool|null
     */
    public function getEngineOnTime();

    /**
     * @return DTCAndVIN|array|null
     */
    public function getDTCVINData();

    /**
     * @return DriverBehaviorBase|HarshDriverBehavior|array|null
     */
    public function getDriverBehaviorData();

    /**
     * @return BaseNetwork|array|null
     */
    public function getNetworkData();

    public function getSatellites(): ?int;

    /**
     * @return int|float|null
     */
    public function getOdometer();

    public function isJammerAlarmStarted(): bool;

    public function isAccidentHappened(): bool;
}