<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Parser\Ulbotech\Data;

/**
 * Class GpsData
 * @package App\Service\Tracker\Parser\Ulbotech\Model
 *
 * @example GPS:2;N22.995947;E113.107829;0;0;2.41 | GPS:2;N22.996582;E113.107671;7;353;2.11 | GPS:3;S27.560376;E152.956688;52;346;1.98
 */
class GpsData implements GpsDataInterface
{
    public const STATUS = 0;
    public const STATUS_CODES = [
        1 => 'no_signal',
        2 => '2D_signal',
        3 => '3D_signal',
    ];
    public const LAT = 1;
    public const LNG = 2;
    public const SPEED = 3;
    public const ANGLE = 4;
    public const HDOP = 5;
    public const HDOP_UNKNOWN_VALUE = 99.99;

    public $status;
    public $latitude;
    public $longitude;
    public $speed; // km/h
    public $angle;
    public $hdop;

    /**
     * DeviceStatus constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->status = $data['status'] ?? null;
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
        $this->speed = $data['speed'] ?? null;
        $this->angle = $data['angle'] ?? null;
        $this->hdop = $data['hdop'] ?? null;
    }

    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $set = explode(Data::DATA_PART_SEPARATOR, substr($textPayload, 4));

        return new self([
            'status' => isset($set[self::STATUS]) ? self::getStatusByValue($set[self::STATUS]) : null,
            'latitude' => isset($set[self::LAT]) ? self::formatLat($set[self::LAT]) : null,
            'longitude' => isset($set[self::LNG]) ? self::formatLng($set[self::LNG]) : null,
            'speed' => isset($set[self::SPEED]) ? floatval($set[self::SPEED]) : null,
            'angle' => isset($set[self::ANGLE]) ? floatval($set[self::ANGLE]) : null,
            'hdop' => (isset($set[self::HDOP]) && $set[self::HDOP] != self::HDOP_UNKNOWN_VALUE)
                ? floatval($set[self::HDOP])
                : null,
        ]);
    }

    /**
     * @param string $textPayload
     * @return array
     */
    private static function getStatusByValue(string $textPayload): array
    {
        return [
            'key' => floatval($textPayload),
            'value' => self::STATUS_CODES[$textPayload] ?? null
        ];
    }

    /**
     * @param string $textPayload
     * @return float
     */
    private static function formatLat(string $textPayload)
    {
        $direction = substr($textPayload, 0, 1);
        $value = substr($textPayload, 1);
        $symbol = ($direction == 'S') ? '-' : '';

        return floatval($symbol . $value);
    }

    /**
     * @param string $textPayload
     * @return float
     */
    private static function formatLng(string $textPayload)
    {
        $direction = substr($textPayload, 0, 1);
        $value = substr($textPayload, 1);
        $symbol = ($direction == 'W') ? '-' : '';

        return floatval($symbol . $value);
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
     * @return null
     */
    public function getAltitude()
    {
        return null;
    }
}
