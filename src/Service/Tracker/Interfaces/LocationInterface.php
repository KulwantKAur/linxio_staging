<?php

namespace App\Service\Tracker\Interfaces;

interface LocationInterface
{
    /**
     * @return int|null
     */
    public function getMobileCountryCode(): ?int;

    /**
     * @return int|null
     */
    public function getMobileNetworkCode(): ?int;

    /**
     * @return int|null
     */
    public function getLocationAreaCode(): ?int;

    /**
     * @return int|null
     */
    public function getStationId(): ?int;
}