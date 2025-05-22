<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\LocationInterface;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\OpenCellIdHelper;

/**
 * Class Location
 * @package App\Service\Tracker\Parser\Topflytech\Model
 *
 * @example (2g) 252502004400010880616898888888000A00FF20010000200096009899101010100555015500001010050500050510100505101005051010050510100505101005051010
 * @example (4g) 252502004400010880616898888888000A00FF20010000200096009899101010100555015500001010050500050510100505901005051010050510101010050510101010
 */
class Location implements GpsDataInterface, LocationInterface
{
    private const STATE_BIT_4G = 15;
    private const NO_LOCATION_CODE = 65535;

    private static $isDataCorrect = true;
    public $mobileCountryCode;
    public $mobileNetworkCode;
    public $locationAreaCode1;
    public $locationAreaCode2;
    public $locationAreaCode3;
    public $stationId1;
    public $stationId2;
    public $stationId3;
    public $latitude;
    public $longitude;
    public $range;

    /**
     * Location constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->mobileCountryCode = $data['mobileCountryCode'] ?? null;
        $this->mobileNetworkCode = $data['mobileNetworkCode'] ?? null;
        $this->locationAreaCode1 = $data['locationAreaCode1'] ?? null;
        $this->stationId1 = $data['stationId1'] ?? null;
    }
    
    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $binaryValues = DataHelper::getBinaryFromHex(substr($textPayload, 0, 4));
        $binaryValues = DataHelper::addZerosToStartOfString($binaryValues, 16);
        $setValues = str_split($binaryValues);
        // @todo verify check below (it's not working via vendor's web-parser) for 2G & 4G
        $location = ($setValues[self::STATE_BIT_4G] == 1)
            ? self::createFrom4G($textPayload)
            : self::createFrom2G($textPayload);

//        if (self::$isDataCorrect) {
//            $coordinates = OpenCellIdHelper::createFromLocation($location)->fetchCoordinates();
//            $location->fromCoordinates($coordinates);
//        }

        return $location;
    }

    /**
     * @param string $textPayload
     * @return static
     */
    private static function createFrom4G(string $textPayload): self
    {
        return new self([
            'mobileCountryCode' => self::checkCorrectValue(hexdec(substr($textPayload, 0, 4))),
            'mobileNetworkCode' => self::checkCorrectValue(hexdec(substr($textPayload, 4, 4))),
            'locationAreaCode1' => self::checkCorrectValue(hexdec(substr($textPayload, 8, 8))),
            'stationId1' => self::checkCorrectValue(hexdec(substr($textPayload, 16, 4))),
        ]);
    }

    /**
     * @param string $textPayload
     * @return static
     */
    private static function createFrom2G(string $textPayload): self
    {
        return new self([
            'mobileCountryCode' => self::checkCorrectValue(hexdec(substr($textPayload, 0, 4))),
            'mobileNetworkCode' => self::checkCorrectValue(hexdec(substr($textPayload, 4, 4))),
            'locationAreaCode1' => self::checkCorrectValue(hexdec(substr($textPayload, 8, 4))),
            'stationId1' => self::checkCorrectValue(hexdec(substr($textPayload, 12, 4))),
        ]);
    }

    /**
     * @param \stdClass|null $data
     */
    public function fromCoordinates(?\stdClass $data)
    {
        if ($data
            && property_exists($data, 'lat')
            && property_exists($data, 'lon')
            && property_exists($data, 'range')
        ) {
            $this->latitude = floatval($data->lat);
            $this->longitude = floatval($data->lon);
            $this->range = intval($data->range);
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function checkCorrectValue($value)
    {
        if ($value == self::NO_LOCATION_CODE) {
            self::$isDataCorrect = false;
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return null
     */
    public function getAngle()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getSpeed()
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getMobileCountryCode(): ?int
    {
        return $this->mobileCountryCode;
    }

    /**
     * @return int|null
     */
    public function getMobileNetworkCode(): ?int
    {
        return $this->mobileNetworkCode;
    }

    /**
     * @return int|null
     */
    public function getLocationAreaCode(): ?int
    {
        return $this->locationAreaCode1;
    }

    /**
     * @return int|null
     */
    public function getStationId(): ?int
    {
        return $this->stationId1;
    }

    /**
     * @return null
     */
    public function getAltitude()
    {
        return null;
    }
}
