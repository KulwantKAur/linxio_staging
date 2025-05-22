<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Route;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\Tracker\Teltonika\TrackerSensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\Tracker\Parser\DataHelper;
use App\Service\Tracker\Parser\Teltonika\Model\GpsData;
use App\Service\Tracker\Parser\Topflytech\Data;
use App\Util\GeoHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * !!! Important !!!
 * When you change smth in this class you need to check the following:
 * - "App\Entity\Tracker\TrackerHistoryTrait"
 * - "App\Entity\Tracker\TrackerHistoryLast"
 * - "App\Entity\Tracker\TrackerHistoryTemp"
 *
 * TrackerHistory
 */
#[ORM\Table(name: 'tracker_history')]
#[ORM\Index(columns: ['device_id', 'created_at'], name: 'tracker_history_device_id_created_at_index')]
#[ORM\Index(columns: ['device_id', 'ts'], name: 'tracker_history_device_id_ts_index')]
#[ORM\Index(columns: ['device_id', 'engine_on_time'], name: 'tracker_history_device_id_engine_on_time_index')]
#[ORM\Index(columns: ['vehicle_id', 'ts', 'odometer'], name: 'tracker_history_vehicle_id_ts_odometer_index')]
#[ORM\Index(columns: ['driver_id', 'ts', 'odometer'], name: 'tracker_history_driver_id_ts_odometer_index')]
#[ORM\Index(columns: ['is_calculated'], name: 'tracker_history_is_calculated_index')]
#[ORM\Index(columns: ['device_id', 'is_calculated', 'ts'], name: 'tracker_history_device_id_is_calculated_ts_index')]
#[ORM\Index(columns: ['device_id', 'speed', 'ts'], name: 'tracker_history_device_id_speed_ts_index')]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistoryRepository')]
class TrackerHistory extends BaseEntity
{
    use TrackerHistoryTrait;

    public const IO_EXTRA_DATA_NAME = 'IOData';
    public const OBD_EXTRA_DATA_NAME = 'OBDData';
    public const ALARM_EXTRA_DATA_NAME = 'AlarmData';
    public const BLE_EXTRA_DATA_NAME = 'BLEData';
    public const BLE_DRIVER_SENSOR_EXTRA_DATA_NAME = 'DriverSensorData';
    public const BLE_TEMP_AND_HUMIDITY_EXTRA_DATA_NAME = 'TempAndHumidityData';
    public const BLE_SOS_DATA_NAME = 'SOSData';
    public const ACCIDENT_EXTRA_DATA_NAME = 'AccidentData';
    public const BLE_DATA_DEFAULT_VALUE = [
        self::BLE_EXTRA_DATA_NAME => [
            self::BLE_DRIVER_SENSOR_EXTRA_DATA_NAME,
            self::BLE_TEMP_AND_HUMIDITY_EXTRA_DATA_NAME,
            self::BLE_SOS_DATA_NAME
        ]
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'deviceId',
        'lastCoordinates',
        'address',
        'speed',
        'angle',
        'movement',
        'ignition',
        'lastDataReceived',
        'temperatureLevel',
        'mileage',
        'mileageFromTracker',
        'engineHours',
        'batteryVoltage',
        'externalVoltage',
        'standsIgnition',
        'iButton',
    ];

    /**
     * @var int
     *
     *
     * @Serializer\SerializedName("id")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    /**
     * @var \DateTime
     *
     *
     *
     * @Serializer\SerializedName("ts")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'ts', type: 'datetime')]
    private $ts;

    /**
     * @var string
     *
     *
     * @Serializer\SerializedName("lng")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'lng', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private $lng;

    /**
     * @var string
     *
     *
     * @Serializer\SerializedName("lat")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'lat', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private $lat;

    /**
     * @var float|null
     *
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("alt")
     */
    #[ORM\Column(name: 'alt', type: 'float', nullable: true)]
    private $alt;

    /**
     * @var float|null
     *
     *
     *
     * @Serializer\SerializedName("angle")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'angle', type: 'float', nullable: true)]
    private $angle;

    /**
     * @var float|null
     *
     *
     *
     * @Serializer\SerializedName("speed")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'speed', type: 'float', nullable: true)]
    private $speed; // km/h
    /**
     * @var TrackerPayload|null
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("trackerPayloadId")
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerPayload', inversedBy: 'trackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_payload_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $trackerPayload;

    /**
     * @var ArrayCollection|TrackerSensor[]
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("trackerSensorData")
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\Teltonika\TrackerSensor', mappedBy: 'trackerHistory')]
    private $trackerSensorData;

    /**
     * @var int|null
     *
     *
     * @Serializer\SerializedName("movement")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'movement', type: 'integer', nullable: true)]
    private $movement;

    /**
     * @var int|null
     *
     *
     * @Serializer\SerializedName("ignition")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'ignition', type: 'integer', nullable: true)]
    private $ignition;

    /**
     * @var float|null
     *
     *
     * @Serializer\SerializedName("batteryVoltage")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'battery_voltage', type: 'float', nullable: true)]
    private $batteryVoltage; // milli Volts
    /**
     * @var float|null
     *
     *
     * @Serializer\SerializedName("batteryVoltagePercentage")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'battery_voltage_percentage', type: 'float', nullable: true)]
    private $batteryVoltagePercentage; // percent
    /**
     * @var float|null
     *
     *
     * @Serializer\SerializedName("externalVoltage")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'external_voltage', type: 'float', nullable: true)]
    private $externalVoltage; // milli Volts
    /**
     * @var bool|null
     *
     *
     * @Serializer\Ignore()
     */
    #[ORM\Column(name: 'solar_charging_status', type: 'boolean', nullable: true)]
    private $solarChargingStatus;

    /**
     * @var float|null
     *
     *
     * @Serializer\SerializedName("temperatureLevel")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'temperature_level', type: 'float', nullable: true)]
    private $temperatureLevel; // milli degrees Celsius, @todo: change to Celsius with front-end
    /**
     * @var float|null
     *
     *
     * @Serializer\SerializedName("engineOnTime")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'engine_on_time', type: 'float', nullable: true)]
    private $engineOnTime; // seconds
    /**
     * @var float|null
     *
     *
     * @Serializer\SerializedName("odometer")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'odometer', type: 'float', nullable: true)]
    private $odometer; // meters
    /**
     * @var boolean
     *
     *
     * @Serializer\Ignore()
     */
    #[ORM\Column(name: 'is_odometer_correct', type: 'boolean', options: ['default' => '1'])]
    private $isOdometerCorrect = true;

    /**
     * @var string|null
     *
     *
     * @Serializer\SerializedName("iButton")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'ibutton', type: 'string', nullable: true)]
    private $iButton;

    /**
     * @var \DateTime
     *
     *
     * @Serializer\SerializedName("createdAt")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var Device|null
     *
     *
     * @Serializer\SerializedName("device")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerRecords')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var bool
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("isCalculated")
     */
    #[ORM\Column(name: 'is_calculated', type: 'boolean', options: ['default' => '0'])]
    private $isCalculated = false;

    /**
     * @var bool
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("isCalculatedIdling")
     */
    #[ORM\Column(name: 'is_calculated_idling', type: 'boolean', options: ['default' => '0'])]
    private $isCalculatedIdling = false;

    /**
     * @var bool
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("isCalculatedSpeeding")
     */
    #[ORM\Column(name: 'is_calculated_speeding', type: 'boolean', options: ['default' => '0'])]
    private $isCalculatedSpeeding = false;

    /**
     * @var Vehicle|null
     *
     *
     * @Serializer\SerializedName("vehicle")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle', inversedBy: 'trackerRecords')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var User|null
     *
     *
     * @Serializer\SerializedName("driver")
     * @Serializer\Groups({"th_ntf_event_detail", "th_by_event:read"})
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $driver;

    /**
     * @var array|null
     *
     *
     * @Serializer\Ignore()
     * @Serializer\SerializedName("extraData")
     */
    #[ORM\Column(name: 'extra_data', type: 'json', nullable: true)]
    private $extraData;

    /**
     * @var int|null
     *
     *
     * @Serializer\Ignore()
     */
    #[ORM\Column(name: 'traccar_position_id', type: 'bigint', nullable: true)]
    private ?int $traccarPositionId = null;

    /**
     * @var int|null
     *
     *
     * @Serializer\Ignore()
     */
    #[ORM\Column(name: 'satellites', type: 'smallint', nullable: true)]
    private ?int $satellites = null;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private ?Team $team;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function __toString()
    {
        return strval($this->getId());
    }

    /**
     * todo support multiple vendors
     * @param GpsData $gpsData
     * @return $this
     */
    public function fromGpsData(GpsData $gpsData): self
    {
        $this->setLat($gpsData->getLatitude());
        $this->setLng($gpsData->getLongitude());
        $this->setAlt($gpsData->getAltitude());
        $this->setAngle($gpsData->getAngle());
        $this->setSpeed($gpsData->getSpeed());

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return TrackerHistory
     */
    public function setId(int $id)
    {
        return $this->id = $id;

        return $this;
    }

    /**
     * Set ts.
     *
     * @param \DateTimeInterface $ts
     *
     * @return TrackerHistory
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return \DateTime|\DateTimeImmutable
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set lng.
     *
     * @param string $lng
     *
     * @return TrackerHistory
     */
    public function setLng($lng)
    {
        $this->lng = isset($lng) && self::isCoordinateValid('longitude', $lng) ? $lng : null;

        return $this;
    }

    /**
     * Get lng.
     *
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set lat.
     *
     * @param string $lat
     *
     * @return TrackerHistory
     */
    public function setLat($lat)
    {
        $this->lat = isset($lat) && self::isCoordinateValid('latitude', $lat) ? $lat : null;

        return $this;
    }

    /**
     * Get lat.
     *
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set alt.
     *
     * @param float $alt
     *
     * @return TrackerHistory
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt.
     *
     * @return float
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set angle.
     *
     * @param float $angle
     *
     * @return TrackerHistory
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;

        return $this;
    }

    /**
     * Get angle.
     *
     * @return float
     */
    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * Set speed.
     *
     * @param float|null $speed
     *
     * @return TrackerHistory
     */
    public function setSpeed($speed = null)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed.
     *
     * @return float|null
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return TrackerHistory
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return TrackerPayload|null
     */
    public function getTrackerPayload(): ?TrackerPayload
    {
        return $this->trackerPayload;
    }

    /**
     * @param TrackerPayload|null $trackerPayload
     */
    public function setTrackerPayload(?TrackerPayload $trackerPayload): void
    {
        $this->trackerPayload = $trackerPayload;
    }

    /**
     * @return TrackerSensor[]|ArrayCollection
     */
    public function getTrackerSensorData()
    {
        return $this->trackerSensorData;
    }

    /**
     * @param TrackerSensor[]|ArrayCollection $trackerSensorData
     */
    public function setTrackerSensorData($trackerSensorData): void
    {
        $this->trackerSensorData = $trackerSensorData;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->getId()
        ];

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice();
        }
        if (in_array('lastCoordinates', $include, true)) {
            $data['lastCoordinates'] = [
                'lat' => $this->getLat(),
                'lng' => $this->getLng(),
                'ts' => $this->formatDate($this->getTs()),
            ];
        }
        if (in_array('lat', $include, true)) {
            $data['lat'] = $this->getLat();
        }
        if (in_array('lng', $include, true)) {
            $data['lng'] = $this->getLng();
        }
        if (in_array('ts', $include, true)) {
            $data['ts'] = $this->getTs();
        }
        if (in_array('tsISO8601', $include, true)) {
            $data['tsISO8601'] = $this->formatDate($this->getTs());
        }
        if (in_array('address', $include, true)) {
            $data['address'] = null;
        }
        if (in_array('speed', $include, true)) {
            $data['speed'] = $this->getSpeed();
        }
        if (in_array('angle', $include, true)) {
            $data['angle'] = $this->getAngle();
        }
        if (in_array('movement', $include, true)) {
            $data['movement'] = $this->getMovement();
        }
        if (in_array('ignition', $include, true)) {
            $data['ignition'] = $this->getIgnition();
        }
        if (in_array('lastDataReceived', $include, true)) {
            $data['lastDataReceived'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('temperatureLevel', $include, true)) {
            $data['temperatureLevel'] = $this->getTemperatureLevelDegrees();
        }
        if (in_array('mileage', $include, true)) {
            $data['mileage'] =
                $this->getDevice()?->getLastCorrectedOdometerValue($this->getOdometer(), $this->getVehicle());
        }
        if (in_array('mileageFromTracker', $include, true)) {
            $data['mileageFromTracker'] = $this->getOdometer();
        }
        if (in_array('engineHours', $include, true)) {
            $data['engineHours'] = $this->getEngineOnTime(); // keep it for front team
        }
        if (in_array('engineOnTime', $include, true)) {
            $data['engineOnTime'] = $this->getEngineOnTime();
        }
        if (in_array('batteryVoltage', $include, true)) {
            $data['batteryVoltage'] = $this->getBatteryVoltageMilli();
        }
        if (in_array('externalVoltage', $include, true)) {
            $data['externalVoltage'] = $this->getExternalVoltage();
        }
        if (in_array('standsIgnition', $include, true)) {
            $data['standsIgnition'] = $this->getStandsIgnitionByDevice();
        }
        if (in_array('iButton', $include, true)) {
            $data['iButton'] = $this->getIButton();
        }
        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->getDriver()?->getId();
        }
        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('satellites', $include, true)) {
            $data['satellites'] = $this->getSatellites();
        }

        $data = $this->addExtraFieldsByDeviceVendor($this->getDeviceVendorName(), $data);
        $data = $this->addExtraFieldsByDeviceModel($this->getDeviceModelName(), $data);

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArrayCoordinates(): array
    {
        return $this->toArray()['lastCoordinates'];
    }

    /**
     * @return int|null
     */
    public function getMovement(): ?int
    {
        return $this->movement;
    }

    /**
     * @param int|null $movement
     */
    public function setMovement(?int $movement): void
    {
        $this->movement = $movement;
    }

    /**
     * @return int|null
     */
    public function getIgnition(): ?int
    {
        return $this->ignition;
    }

    /**
     * @param int|null $ignition
     */
    public function setIgnition(?int $ignition): void
    {
        $this->ignition = $ignition;
    }

    /**
     * @return float|null
     */
    public function getBatteryVoltage(): ?float
    {
        return $this->batteryVoltage;
    }

    /**
     * @return float|null
     */
    public function getBatteryVoltageMilli(): ?float
    {
        return DataHelper::formatMilliValue($this->getBatteryVoltage());
    }

    /**
     * @param float|null $batteryVoltage
     */
    public function setBatteryVoltage(?float $batteryVoltage): void
    {
        $this->batteryVoltage = $batteryVoltage;
    }

    /**
     * @return float|null
     */
    public function getTemperatureLevel(): ?float
    {
        return $this->temperatureLevel;
    }

    /**
     * @return float|null
     */
    public function getTemperatureLevelMilli(): ?float
    {
        return DataHelper::formatMilliValue($this->getTemperatureLevel());
    }

    /**
     * @return float|null
     */
    public function getTemperatureLevelDegrees(): ?float
    {
        return $this->getTemperatureLevel() ? $this->getTemperatureLevel() / 1000 : null;
    }

    /**
     * @param float|null $temperatureLevel
     */
    public function setTemperatureLevel(?float $temperatureLevel): void
    {
        $this->temperatureLevel = $temperatureLevel;
    }

    /**
     * @return float|null
     */
    public function getEngineOnTime(): ?float
    {
        return $this->engineOnTime;
    }

    /**
     * @param float|null $engineOnTime
     */
    public function setEngineOnTime(?float $engineOnTime): void
    {
        $this->engineOnTime = $engineOnTime;
    }

    /**
     * @return float|null
     */
    public function getOdometer(): ?float
    {
        if ($this->getDevice()) {
            $vendorName = $this->getDevice()->getVendorName();

            if ($vendorName) {
                switch ($vendorName) {
                    case DeviceVendor::VENDOR_TOPFLYTECH:
                        return ($this->odometer == Data::ODOMETER_VALUE_WITH_ERROR) ? null : $this->odometer;
                    default:
                        return $this->odometer;
                }
            }
        }

        return $this->odometer;
    }

    /**
     * @param float|null $odometer
     * @param TrackerHistoryLast|null $lastTH
     * @param string|null $vendorName
     */
    public function setOdometer(?float $odometer, ?TrackerHistoryLast $lastTH = null, ?string $vendorName = null): void
    {
        $this->odometer = $odometer;

        if (!$odometer) {
            $this->setIsOdometerCorrect(false);
            $this->odometer = ($lastTH && $this->getTs() >= $lastTH->getTs()) ? $lastTH->getOdometer() : null;
        }

        switch ($vendorName) {
            case DeviceVendor::VENDOR_TOPFLYTECH:
                if ($odometer > Data::ODOMETER_LIMIT_MAX) {
                    $this->setIsOdometerCorrect(false);

                    if ($odometer == Data::ODOMETER_VALUE_WITH_ERROR) {
                        $this->odometer = ($lastTH && $this->getTs() >= $lastTH->getTs())
                            ? $lastTH->getOdometer()
                            : null;
                    }
                }

                break;
            default:
                break;
        }
    }

    /**
     * @return bool|null
     */
    public function getStandsIgnitionByDevice(): ?bool
    {
        $ignition = $this->getIgnition();
        $movement = $this->getMovement();

        if (!is_null($ignition) && !is_null($movement)) {
            return $ignition == 1 && $movement == 0;
        }

        return null;
    }

    /**
     * @param null $ignition
     * @param null $movement
     * @return string
     */
    public static function getDeviceStatusByIgnitionAndMovement($ignition = null, $movement = null): string
    {
        if (!is_null($movement) && !is_null($ignition)) {
            if ($movement == 1 && $ignition == 1) {
                $status = Device::STATUS_DRIVING;
            } else {
                $status = ($ignition == 1) ? Device::STATUS_IDLE : Device::STATUS_STOPPED;
            }
        }

        return $status ?? Device::STATUS_STOPPED;
    }

    /**
     * @param null $ignition
     * @param null $movement
     * @return string
     */
    static public function getRouteTypeByIgnitionAndMovement($ignition = null, $movement = null): string
    {
        $deviceStatus = self::getDeviceStatusByIgnitionAndMovement($ignition, $movement);

        return ($deviceStatus && $deviceStatus == Device::STATUS_STOPPED)
            ? Route::TYPE_STOP
            : Route::TYPE_DRIVING;
    }

    public static function getRouteTempTypeByIgnitionAndMovement($ignition = null, $movement = null): string
    {
        return self::getDeviceStatusByIgnitionAndMovement($ignition, $movement);
    }

    /**
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * @return int|null
     */
    public function getDeviceId()
    {
        return $this->getDevice() ? $this->getDevice()->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getDeviceModelName()
    {
        return $this->getDevice() ? $this->getDevice()->getModelName() : null;
    }

    /**
     * @return string|null
     */
    public function getDeviceVendorName()
    {
        return $this->getDevice() ? $this->getDevice()->getVendorName() : null;
    }

    /**
     * @param Device|null $device
     */
    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return float|null
     */
    public function getExternalVoltage(): ?float
    {
        return $this->externalVoltage;
    }

    /**
     * @return float|null
     */
    public function getExternalVoltageVolts(): ?float
    {
        return $this->getExternalVoltage() ? $this->getExternalVoltage() / 1000 : $this->getExternalVoltage();
    }

    /**
     * @param float|null $externalVoltage
     */
    public function setExternalVoltage(?float $externalVoltage): void
    {
        $this->externalVoltage = $externalVoltage;
    }

    /**
     * @return string|null
     */
    public function getIButton(): ?string
    {
        return $this->iButton;
    }

    /**
     * @param string|null $iButton
     */
    public function setIButton(?string $iButton): void
    {
        $this->iButton = (is_string($iButton) && !empty($iButton)) ? $iButton : null;
    }

    /**
     * @return bool
     */
    public function isCalculated(): bool
    {
        return $this->isCalculated;
    }

    /**
     * @param bool $isCalculated
     */
    public function setIsCalculated(bool $isCalculated): void
    {
        $this->isCalculated = $isCalculated;
    }

    /**
     * @return bool
     */
    public function isCalculatedIdling(): bool
    {
        return $this->isCalculatedIdling;
    }

    /**
     * @param bool $isCalculatedIdling
     */
    public function setIsCalculatedIdling(bool $isCalculatedIdling): void
    {
        $this->isCalculatedIdling = $isCalculatedIdling;
    }

    /**
     * @return bool
     */
    public function isCalculatedSpeeding(): bool
    {
        return $this->isCalculatedSpeeding;
    }

    /**
     * @param bool $isCalculatedSpeeding
     */
    public function setIsCalculatedSpeeding(bool $isCalculatedSpeeding): void
    {
        $this->isCalculatedSpeeding = $isCalculatedSpeeding;
    }

    /**
     * @return void
     */
    public function setIsAllCalculated(): void
    {
        $this->isCalculated = true;
        $this->isCalculatedSpeeding = true;
        $this->isCalculatedIdling = true;
    }

    /**
     * @return void
     */
    public function setIsAllNotCalculated(): void
    {
        $this->isCalculated = false;
        $this->isCalculatedSpeeding = false;
        $this->isCalculatedIdling = false;
    }

    /**
     * @return bool
     */
    public function isAllCalculated(): bool
    {
        return $this->isCalculated() && $this->isCalculatedIdling() && $this->isCalculatedSpeeding();
    }

    /**
     * @return bool
     */
    public function isOdometerCorrect(): bool
    {
        return $this->isOdometerCorrect;
    }

    /**
     * @param bool $isOdometerCorrect
     */
    public function setIsOdometerCorrect(bool $isOdometerCorrect): void
    {
        $this->isOdometerCorrect = $isOdometerCorrect;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @param Vehicle|null $vehicle
     */
    public function setVehicle(?Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }

    /**
     * @return User|null
     */
    public function getDriver(): ?User
    {
        return $this->driver;
    }

    /**
     * @param User|null $driver
     */
    public function setDriver(?User $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getVehicle() ? $this->getVehicle()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @return float|null
     */
    public function getBatteryVoltagePercentage(): ?float
    {
        return $this->batteryVoltagePercentage;
    }

    /**
     * @param float|null $batteryVoltagePercentage
     */
    public function setBatteryVoltagePercentage(?float $batteryVoltagePercentage): void
    {
        $this->batteryVoltagePercentage = $batteryVoltagePercentage;
    }

    /**
     * @return bool|null
     */
    public function getSolarChargingStatus(): ?bool
    {
        return $this->solarChargingStatus;
    }

    /**
     * @param bool|null $solarChargingStatus
     */
    public function setSolarChargingStatus(?bool $solarChargingStatus): void
    {
        $this->solarChargingStatus = $solarChargingStatus;
    }

    /**
     * It should be {1,8} instead of {1,20}, we added this accuracy to allow wrong tracker's data
     * @param string $type
     * @param float $value
     * @return bool
     */
    public static function isCoordinateValid(string $type, float $value): bool
    {
        $pattern = ($type == 'latitude')
            ? '/^(\+|-)?(?:90(?:(?:\.0{1,20})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,20})?))$/'
            : '/^(\+|-)?(?:180(?:(?:\.0{1,20})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,20})?))$/';

        return boolval(preg_match($pattern, strval($value)));
    }
}
