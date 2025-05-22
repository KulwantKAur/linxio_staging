<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Ulbotech\Model;

use App\Service\Tracker\Interfaces\GpsDataInterface;
use App\Service\Tracker\Interfaces\LocationInterface;
use App\Service\Tracker\Parser\OpenCellIdHelper;
use App\Service\Tracker\Parser\Ulbotech\Data;

/**
 * Class Location
 * @package App\Service\Tracker\Parser\Ulbotech\Model
 *
 * @example LBS:053638161112;460;0;2731;40F4;82;2731;BB41;97;2731;40F3;98;2503;962C;98;2731;366D;102;2731;B5E7;103;2503; BFDE;105 | LBS:65535;65535;FFFF;FFFF;120 | LBS:505;1;20C9;7EF5821;73
 */
class Location implements GpsDataInterface, LocationInterface
{
    private const MOBILE_COUNTRY_CODE = 0;
    private const MOBILE_NETWORK_CODE = 1;
    private const LOCATION_AREA_CODE = 2;
    private const STATION_ID = 3;
    private const SIGNAL_POWER = 4; // dbm
    private const NO_LOCATION_CODE = 65535;

    private $signalPower;
    private static $isDataCorrect = true;
    public $mobileCountryCode;
    public $mobileNetworkCode;
    public $locationAreaCode;
    public $stationId;
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
        $this->locationAreaCode = $data['locationAreaCode'] ?? null;
        $this->stationId = $data['stationId'] ?? null;
        $this->signalPower = $data['signalPower'] ?? null;
    }
    
    /**
     * @param $textPayload
     * @return self
     */
    public static function createFromTextPayload(string $textPayload): self
    {
        $set = explode(Data::DATA_PART_SEPARATOR, substr($textPayload, 4));
        $location = self::createFromSet($set);

//        if (self::$isDataCorrect) {
//            $coordinates = OpenCellIdHelper::createFromLocation($location)->fetchCoordinates();
//            $location->fromCoordinates($coordinates);
//        }

        return $location;
    }

    /**
     * @param \stdClass|null $data
     */
    public function fromCoordinates(?\stdClass $data)
    {
        if ($data) {
            $this->latitude = floatval($data->lat);
            $this->longitude = floatval($data->lon);
            $this->range = intval($data->range);
        }
    }

    /**
     * @param array $set
     * @return self
     */
    public static function createFromSet(array $set): self
    {
        return new self([
            'mobileCountryCode' => self::getIntValueFromSetByKeyWithFailCode($set, self::MOBILE_COUNTRY_CODE),
            'mobileNetworkCode' => self::getIntValueFromSetByKeyWithFailCode($set, self::MOBILE_NETWORK_CODE),
            'locationAreaCode' => self::checkCorrectValue(self::formatLocationAreaCode($set)),
            'stationId' => self::checkCorrectValue(self::formatStationId($set)),
            'signalPower' => Data::getIntValueFromSetByKey($set, self::SIGNAL_POWER),
        ]);
    }

    /**
     * @param $set
     * @return float|int|null
     */
    private static function formatLocationAreaCode($set)
    {
        $value = $set[self::LOCATION_AREA_CODE] ?? null;

        return $value ? hexdec($value) : null;
    }

    /**
     * @param $set
     * @return float|int|null
     */
    private static function formatStationId($set)
    {
        $value = $set[self::STATION_ID] ?? null;

        return $value ? hexdec($value) : null;
    }

    /**
     * @param array $set
     * @param $key
     * @return int|null
     */
    private static function getIntValueFromSetByKeyWithFailCode(array $set, $key)
    {
        $value = Data::getIntValueFromSetByKey($set, $key);

        return self::checkCorrectValue($value);
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
        return $this->locationAreaCode;
    }

    /**
     * @return int|null
     */
    public function getStationId(): ?int
    {
        return $this->stationId;
    }

    /**
     * @return null
     */
    public function getAltitude()
    {
        return null;
    }
}
