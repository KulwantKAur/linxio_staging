<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Pivotel\Model;

use App\Service\Tracker\Interfaces\GpsDataInterface;

/**
 * Class GpsData
 * @package App\Service\Tracker\Parser\Pivotel\Model
 */

class GpsData implements GpsDataInterface
{
    public $latitude;
    public $longitude;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
    }

    public function getAltitude()
    {
    }

    public function getAngle()
    {
    }

    public function getSpeed()
    {
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     */
    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     */
    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
