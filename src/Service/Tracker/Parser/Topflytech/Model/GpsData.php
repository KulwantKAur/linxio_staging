<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Parser\DataHelper;

/**
 * Class GpsData
 * @package App\Service\Tracker\Parser\Topflytech\Model
 */
class GpsData implements GpsDataInterface
{
    public $altitude;
    public $latitude;
    public $longitude;
    public $speed;
    public $angle;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->altitude = $data['altitude'] ?? null;
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
        $this->speed = $data['speed'] ?? null;
        $this->angle = $data['direction'] ?? null;
    }

    /**
     * @param float $value
     * @return float|int
     */
    private static function formatDirection(float $value)
    {
        return $value <= 360 ? $value : $value % 360;
    }

    /**
     * @param string $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        return new self([
            'altitude' => round(DataHelper::hexToFloatDCBA(substr($textPayload, 0, 8)), 5),
            'longitude' => DataHelper::hexToFloatDCBA(substr($textPayload, 8, 8)),
            'latitude' => DataHelper::hexToFloatDCBA(substr($textPayload, 16, 8)),
            'speed' => DataHelper::formatIntegerAndFraction(substr($textPayload, 24, 4), 3, 1),
            'direction' => self::formatDirection(hexdec(substr($textPayload, 28, 4))),
        ]);
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

    /**
     * @return float|null
     */
    public function getAngle(): ?float
    {
        return $this->angle;
    }

    /**
     * @param float|null $angle
     */
    public function setAngle(?float $angle): void
    {
        $this->angle = $angle;
    }

    /**
     * @return float|null
     */
    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    /**
     * @param float|null $speed
     */
    public function setSpeed(?float $speed): void
    {
        $this->speed = $speed;
    }

    /**
     * @return mixed|null
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * @param mixed|null $altitude
     */
    public function setAltitude($altitude): void
    {
        $this->altitude = $altitude;
    }
}
