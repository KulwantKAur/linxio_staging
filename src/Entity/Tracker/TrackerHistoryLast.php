<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\Route;
use App\Entity\Team;
use App\Entity\Tracker\Teltonika\TrackerSensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\Tracker\Parser\DataHelper;
use App\Util\AttributesTrait;
use App\Util\GeoHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerHistoryLast
 */
#[ORM\Table(name: 'tracker_history_last')]
#[ORM\UniqueConstraint(columns: ['vehicle_id', 'device_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistoryLastRepository')]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners(['App\EventListener\Tracker\TrackerHistoryLastEntityListener'])]
class TrackerHistoryLast extends BaseEntity
{
    use AttributesTrait;
    use TrackerHistoryTrait;

    public const DEFAULT_DISPLAY_VALUES = [
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
        'iButton'
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'ts', type: 'datetime')]
    private $ts;

    /**
     * @var string
     */
    #[ORM\Column(name: 'lng', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private $lng;

    /**
     * @var string
     */
    #[ORM\Column(name: 'lat', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private $lat;

    /**
     * @var float
     */
    #[ORM\Column(name: 'alt', type: 'float', nullable: true)]
    private $alt;

    /**
     * @var float
     */
    #[ORM\Column(name: 'angle', type: 'float', nullable: true)]
    private $angle;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'speed', type: 'float', nullable: true)]
    private $speed;

    /**
     * @var ArrayCollection|TrackerSensor[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\Teltonika\TrackerSensor', mappedBy: 'trackerHistory')]
    private $trackerSensorData;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'movement', type: 'integer', nullable: true)]
    private $movement;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'ignition', type: 'integer', nullable: true)]
    private $ignition;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'battery_voltage', type: 'float', nullable: true)]
    private $batteryVoltage;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'battery_voltage_percentage', type: 'float', nullable: true)]
    private $batteryVoltagePercentage;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'external_voltage', type: 'float', nullable: true)]
    private $externalVoltage;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'solar_charging_status', type: 'boolean', nullable: true)]
    private $solarChargingStatus;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'temperature_level', type: 'float', nullable: true)]
    private $temperatureLevel;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'engine_on_time', type: 'float', nullable: true)]
    private $engineOnTime;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'odometer', type: 'float', nullable: true)]
    private $odometer;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'ibutton', type: 'string', nullable: true)]
    private $iButton;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var Device|null
     *
     *
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\Device', inversedBy: 'lastTrackerRecord')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var Vehicle|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle', inversedBy: 'trackerHistoriesLast')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var User|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $driver;

    /**
     * @var TrackerHistory|null
     *
     *
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $trackerHistory;

    /**
     * @var string|null
     */
    private $address;

    /**
     * @var array|null
     *
     *
     */
    #[ORM\Column(name: 'extra_data', type: 'json', nullable: true)]
    private $extraData;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'traccar_position_id', type: 'bigint', nullable: true)]
    private ?int $traccarPositionId = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'satellites', type: 'smallint', nullable: true)]
    private ?int $satellites = null;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private ?Team $team = null;

    private EntityManager $em;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return $this
     */
    public function fromTrackerHistory(TrackerHistory $trackerHistory): self
    {
        $this->setTrackerHistory($trackerHistory);
        $this->setDevice($trackerHistory->getDevice());
        $this->setTeam($trackerHistory->getTeam());
        $this->setTs($trackerHistory->getTs());
        $this->setCreatedAt($trackerHistory->getCreatedAt());
        $this->setTraccarPositionId($trackerHistory->getTraccarPositionId());
        $this->setExtraData($trackerHistory->getExtraData());
        $this->setIButton($trackerHistory->getIButton());
        $this->setAlt($trackerHistory->getAlt() ?? $this->getAlt());
        $this->setAngle($trackerHistory->getAngle() ?? $this->getAngle());
        $this->setSpeed($trackerHistory->getSpeed() ?? $this->getSpeed());
        $this->setMovement($trackerHistory->getMovement() ?? $this->getMovement());
        $this->setIgnition($trackerHistory->getIgnition() ?? $this->getIgnition());
        $this->setSatellites($trackerHistory->getSatellites() ?? $this->getSatellites());
        $OBDData = $this->getOBDExtraData();

        if (!$this->getOBDExtraData() && $OBDData) {
            $this->setOBDExtraData($OBDData);
        }
        if ($trackerHistory->getDevice() && $trackerHistory->getDevice()->getVehicle()) {
            $this->setVehicle($trackerHistory->getDevice()->getVehicle());
            $this->setDriver($trackerHistory->getDevice()->getVehicle()->getDriver());
        }
        if (!is_null($trackerHistory->getOdometer())) {
            $this->setOdometer($trackerHistory->getOdometer());
        }
        if (!is_null($trackerHistory->getExternalVoltage())) {
            $this->setExternalVoltage($trackerHistory->getExternalVoltage());
        }
        if (!is_null($trackerHistory->getEngineOnTime())) {
            $this->setEngineOnTime($trackerHistory->getEngineOnTime());
        }
        if (!is_null($trackerHistory->getTemperatureLevel())) {
            $this->setTemperatureLevel($trackerHistory->getTemperatureLevel());
        }
        if (!is_null($trackerHistory->getBatteryVoltage())) {
            $this->setBatteryVoltage($trackerHistory->getBatteryVoltage());
        }
        if (!is_null($trackerHistory->getBatteryVoltagePercentage())) {
            $this->setBatteryVoltagePercentage($trackerHistory->getBatteryVoltagePercentage());
        }
        if (!is_null($trackerHistory->getSolarChargingStatus())) {
            $this->setSolarChargingStatus($trackerHistory->getSolarChargingStatus());
        }
        if (GeoHelper::hasCoordinatesWithCorrectValue($trackerHistory->getLat(), $trackerHistory->getLng())) {
            $this->setLat($trackerHistory->getLat());
            $this->setLng($trackerHistory->getLng());
        }

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
     * Set ts.
     *
     * @param \DateTime|\DateTimeImmutable $ts
     *
     * @return TrackerHistoryLast
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return \DateTime
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
     * @return TrackerHistoryLast
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

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
     * @return TrackerHistoryLast
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

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
     * @return TrackerHistoryLast
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
     * @return TrackerHistoryLast
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
     * @return TrackerHistoryLast
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
     * @return TrackerHistoryLast
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

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('lastCoordinates', $include, true)) {
            $data['lastCoordinates'] = [
                'lat' => $this->getLat(),
                'lng' => $this->getLng(),
                'ts' => $this->formatDate($this->getTs()),
            ];
        }
        if (in_array('address', $include, true)) {
            $data['address'] = $this->getAddress();
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
            $data['mileage'] = $this->getDevice()?->getLastCorrectedOdometerValue(
                $this->getOdometer(), $this->getVehicle()
            );
        }
        if (in_array('mileageFromTracker', $include, true)) {
            $data['mileageFromTracker'] = $this->getOdometer();
        }
        if (in_array('engineHours', $include, true)) {
            $data['engineHours'] = $this->getEngineOnTime(); // @todo update after front-end changes it to vehicle's data
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
        return $this->odometer;
    }

    /**
     * @param float|null $odometer
     */
    public function setOdometer(?float $odometer): void
    {
        $this->odometer = $odometer;
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
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        return $this->device;
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
        $this->iButton = $iButton;
    }

    /**
     * @return TrackerHistory|null
     */
    public function getTrackerHistory(): ?TrackerHistory
    {
        return $this->trackerHistory;
    }

    /**
     * @param TrackerHistory|null $trackerHistory
     */
    public function setTrackerHistory(?TrackerHistory $trackerHistory): void
    {
        $this->trackerHistory = $trackerHistory;
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
    public function getAddress(): ?string
    {
        if ($this->address) {
            return $this->address;
        }

        if ($this->getTrackerHistory()) {
            $routeAddress = $this->em->getRepository(Route::class)
                ->getClosestAddressByDate($this->getTrackerHistory());
            $this->setAddress($routeAddress);
        }

        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
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

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }
}
