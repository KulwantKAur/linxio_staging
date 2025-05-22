<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Util\AttributesTrait;
use App\Util\GeoHelper;
use App\Util\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route
 */
#[ORM\Table(name: 'route')]
#[ORM\Index(name: 'route_device_id_started_at_idx', columns: ['device_id', 'started_at'])]
#[ORM\Index(name: 'route_driver_id_started_at_finished_at_idx', columns: ['driver_id', 'started_at', 'finished_at'])]
#[ORM\Index(name: 'route_vehicle_id_started_at_finished_at_idx', columns: ['vehicle_id', 'started_at', 'finished_at'])]
#[ORM\Index(name: 'route_start_coordinates_index', columns: ['start_coordinates'])]
#[ORM\Index(name: 'route_finish_coordinates_index', columns: ['finish_coordinates'])]
#[ORM\Index(name: 'route_vehicle_id_device_id_started_at_finished_at_driver_id_idx', columns: [
    'vehicle_id',
    'device_id',
    'started_at',
    'finished_at',
    'driver_id'
])]
#[ORM\Entity(repositoryClass: 'App\Repository\RouteRepository')]
#[ORM\EntityListeners(['App\EventListener\Route\RouteEntityListener'])]
class Route extends BaseEntity
{
    use AttributesTrait;

    public const TYPE_STOP = 'stopped';
    public const TYPE_DRIVING = 'driving';

    public const SCOPE_PRIVATE = 'private';
    public const SCOPE_WORK = 'work';
    public const SCOPE_UNCATEGORISED = 'uncategorised';

    public const HOME_PRIVATE = '1 To / From home / Miscellaneous private';
    public const TO_MEETING = '2 To meeting';
    public const FROM_MEETING = '3 Return Meeting';
    public const DEL_PICKUP = '4 Del / Pickup';
    public const ADMIN_NEPT = '5 Admin/ NEPT';
    public const REPAIRS = '6 Repairs';

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'type',
        'pointStart',
        'pointFinish',
        'duration',
        'distance',
        'avgSpeed',
        'maxSpeed',
        'comment',
        'scope',
        'address',
        'driverId',
        'vehicleId',
        'deviceId',
        'tripCode'
    ];

    public const REPORT_DISPLAY_VALUES = [
        'defaultlabel',
        'regno',
        'model',
        'driver_name',
        'groups',
        'depot_name',
        'started_at',
        'address_from',
        'start_areas_name',
        'finished_at',
        'address_to',
        'finish_areas_name',
        'distance',
        'start_odometer',
        'finish_odometer',
        'driving_time',
        'idling_time',
        'avg_speed',
        'max_speed'
    ];

    public const STOPS_DISPLAY_VALUES = [
        'defaultlabel',
        'regno',
        'model',
        'driver_name',
        'groups',
        'depot_name',
        'started_at',
        'finished_at',
        'address',
        'areas_name',
        'finish_odometer',
        'parking_time',
        'idling_time'
    ];

    public const OPT_ROUTE_DISPLAY_VALUES = [
        'id',
        'type',
        'pointStart',
        'pointFinish',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Device
     */
    #[ORM\ManyToOne(targetEntity: 'Device', inversedBy: 'routes')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var TrackerHistory
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'point_start_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $pointStart;

    /**
     * @var TrackerHistory|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'point_finish_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $pointFinish;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'started_at', type: 'datetime', nullable: true)]
    private $startedAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'finished_at', type: 'datetime', nullable: true)]
    private $finishedAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 7, nullable: true)]
    private $type;

    /**
     * @var int
     */
    #[ORM\Column(name: 'distance', type: 'bigint', nullable: true)]
    private $distance;

    /**
     * @var int
     */
    #[ORM\Column(name: 'max_speed', type: 'integer', nullable: true)]
    private $maxSpeed;

    /**
     * @var int
     */
    #[ORM\Column(name: 'avg_speed', type: 'integer', nullable: true)]
    private $avgSpeed;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var array|null
     */
    private $coordinates;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'total_stop_duration', type: 'integer', nullable: true)]
    private $totalStopDuration;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'total_movement_duration', type: 'integer', nullable: true)]
    private $totalMovementDuration;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'total_idle_duration', type: 'integer', nullable: true)]
    private $totalIdleDuration;

    /**
     * @var Vehicle|null
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'routes')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $driver;

    #[ORM\OneToMany(mappedBy: 'route', targetEntity: 'App\Entity\RouteStartArea', fetch: 'EXTRA_LAZY')]
    private $startAreas;

    #[ORM\OneToMany(mappedBy: 'route', targetEntity: 'App\Entity\RouteFinishArea', fetch: 'EXTRA_LAZY')]
    private $finishAreas;

    /**
     * @var string|null
     *
     *
     * @Assert\Choice(callback={"App\Entity\Route", "getScopes"}, groups={"update"})
     */
    #[ORM\Column(name: 'scope', type: 'string', length: 255, nullable: true)]
    private $scope;

    /**
     * @var string|null
     *
     *
     * @Assert\Length(max=255, groups={"update"})
     */
    #[ORM\Column(name: 'comment', type: 'string', length: 255, nullable: true)]
    private $comment;

    /**
     * @var string
     */
    #[ORM\Column(name: 'address', type: 'text', nullable: true)]
    private $address;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'start_odometer', type: 'float', nullable: true)]
    private $startOdometer;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'finish_odometer', type: 'float', nullable: true)]
    private $finishOdometer;

    #[ORM\Column(name: 'start_coordinates', type: 'geometry', nullable: true, options: ['geometry_type' => 'POINT'])]
    private $startCoordinates;

    private $startAddress;
    private $finishAddress;

    #[ORM\Column(name: 'finish_coordinates', type: 'geometry', nullable: true, options: ['geometry_type' => 'POINT'])]
    private $finishCoordinates;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'is_location_checked', type: 'boolean', nullable: false, options: ['default' => false])]
    private $isLocationChecked = false;

    /** @var EntityManager */
    private $em;

    /**
     * @var string|null
     *
     *
     */
    #[ORM\Column(name: 'trip_code', type: 'string', length: 255, nullable: true)]
    private $tripCode;

    #[ORM\Column(name: 'driver_comment', type: 'text', nullable: true)]
    private $driverComment;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->startAreas = new ArrayCollection();
        $this->finishAreas = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->getId();
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
     * Set startedAt.
     *
     * @param \DateTime $startedAt
     *
     * @return self
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt.
     *
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set finishedAt.
     *
     * @param \DateTime $finishedAt
     *
     * @return self
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * Get finishedAt.
     *
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set distance.
     *
     * @param int $distance
     *
     * @return self
     */
    public function setDistance($distance)
    {
        if (!is_null($distance) && $distance >= 0) {
            $this->distance = StringHelper::isDecimal($distance) ? round($distance) : $distance;
        }

        return $this;
    }

    /**
     * Get distance.
     *
     * @return int
     */
    public function getDistance()
    {
        return $this->distance >= 0 ? $this->distance : null;
    }

    /**
     * Set avgSpeed.
     *
     * @param int $avgSpeed
     *
     * @return self
     */
    public function setAvgSpeed($avgSpeed)
    {
        $this->avgSpeed = StringHelper::isDecimal($avgSpeed) ? ceil($avgSpeed) : $avgSpeed;

        return $this;
    }

    /**
     * Get avgSpeed.
     *
     * @return int
     */
    public function getAvgSpeed()
    {
        return $this->avgSpeed ?: 0;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return self
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
     * @return int|null
     */
    public function getMaxSpeed(): ?int
    {
        return $this->maxSpeed;
    }

    /**
     * @param int|float|null $maxSpeed
     */
    public function setMaxSpeed($maxSpeed): void
    {
        $this->maxSpeed = StringHelper::isDecimal($maxSpeed) ? ceil($maxSpeed) : $maxSpeed;
    }

    /**
     * @return Device
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * @param Device $device
     */
    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return TrackerHistory
     */
    public function getPointStart(): TrackerHistory
    {
        return $this->pointStart;
    }

    /**
     * @param TrackerHistory $pointStart
     */
    public function setPointStart(TrackerHistory $pointStart): void
    {
        $this->pointStart = $pointStart;
    }

    /**
     * @return TrackerHistory|null
     */
    public function getPointFinish(): ?TrackerHistory
    {
        return $this->pointFinish;
    }

    /**
     * @param TrackerHistory $pointFinish
     */
    public function setPointFinish(TrackerHistory $pointFinish): void
    {
        $this->pointFinish = $pointFinish;
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_DRIVING,
            self::TYPE_STOP,
        ];
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()?->toArray();
        }

        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDevice()?->getId();
        }

        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray();
        }

        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle()?->getId();
        }

        if (in_array('regNo', $include, true)) {
            $data['regNo'] = $this->getVehicle()?->getRegNo();
        }

        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver()?->toArray();
        }

        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->getDriver()?->getId();
        }

        if (in_array('fullName', $include, true)) {
            $data['fullName'] = $this->getDriver()?->getFullName();
        }

        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType();
        }

        if (in_array('pointStart', $include, true)) {
            $data['pointStart'] = $this->getStartPointData();
        }

        if (in_array('pointFinish', $include, true)) {
            $data['pointFinish'] = $this->getFinishPointData();
        }

        if (in_array('coordinates', $include, true)) {
            $data['coordinates'] = $this->getCoordinates();
        }

        if (in_array('duration', $include, true)) {
            $data['duration'] = $this->getDuration();
        }

        if (in_array('distance', $include, true)) {
            $data['distance'] = $this->getDistance();
        }

        if (in_array('comment', $include, true)) {
            $data['comment'] = $this->getComment();
        }

        if (in_array('scope', $include, true)) {
            $data['scope'] = $this->getScope();
        }

        if (in_array('avgSpeed', $include, true)) {
            $data['avgSpeed'] = $this->getAvgSpeed();
        }

        if (in_array('maxSpeed', $include, true)) {
            $data['maxSpeed'] = $this->getMaxSpeed();
        }

        if (in_array('address', $include, true)) {
            $data['address'] = $this->getAddress();
        }

        if (in_array('startOdometer', $include, true)) {
            $data['startOdometer'] = $this->getStartOdometer();
        }
        if (in_array('finishOdometer', $include, true)) {
            $data['finishOdometer'] = $this->getFinishOdometer();
        }
        if (in_array('coordinates', $include, true)) {
            $data['coordinates'] = $this->getCoordinates();
        }
        if (in_array('startArea', $include, true)) {
            $data['startArea'] = $this->getStartAreaArray();
        }
        if (in_array('finishArea', $include, true)) {
            $data['finishArea'] = $this->getFinishAreaArray();
        }
        if (in_array('tripCode', $include, true)) {
            $data['tripCode'] = $this->getTripCode();
        }
        if (in_array('driverComment', $include, true)) {
            $data['driverComment'] = $this->getDriverComment();
        }

        return $data;
    }

    /**
     * @return array|null
     */
    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    /**
     * @param array|null $coordinates
     */
    public function setCoordinates(?array $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return void
     * @throws \Exception
     */
    public function setCoordinatesFromTrackerHistory(TrackerHistory $trackerHistory): void
    {
        $coordinates = $trackerHistory->toArrayCoordinates();
        $coordinates = GeoHelper::convertCoordinatesForResponse($coordinates);
        $this->setCoordinates($coordinates);
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return ($this->getPointStart() && $this->getPointFinish())
            ? $this->getPointFinish()->getTs()->getTimestamp()
            - $this->getPointStart()->getTs()->getTimestamp()
            : null;
    }

    /**
     * @return TrackerHistory
     */
    public function getLastPoint(): TrackerHistory
    {
        return $this->pointFinish ?: $this->pointStart;
    }

    /**
     * @return int|null
     */
    public function getTotalStopDuration(): ?int
    {
        return ($this->totalStopDuration && $this->totalStopDuration >= 0) ? $this->totalStopDuration : 0;
    }

    /**
     * @param int|null $totalStopDuration
     */
    public function setTotalStopDuration(?int $totalStopDuration): void
    {
        // @todo remove hotfix with integer
        if (!is_null($totalStopDuration) && $totalStopDuration >= 0 && $totalStopDuration < 2147483647) {
            $this->totalStopDuration = $totalStopDuration;
        }
    }

    /**
     * @return int|null
     */
    public function getTotalMovementDuration(): ?int
    {
        return ($this->totalMovementDuration && $this->totalMovementDuration >= 0) ? $this->totalMovementDuration : 0;
    }

    /**
     * @param int|null $totalMovementDuration
     */
    public function setTotalMovementDuration(?int $totalMovementDuration): void
    {
        // @todo remove hotfix with integer
        if (!is_null($totalMovementDuration) && $totalMovementDuration >= 0 && $totalMovementDuration < 2147483647) {
            $this->totalMovementDuration = $totalMovementDuration;
        }
    }

    /**
     * @return int|null
     */
    public function getTotalIdleDuration(): ?int
    {
        return ($this->totalIdleDuration && $this->totalIdleDuration >= 0) ? $this->totalIdleDuration : 0;
    }

    /**
     * @param int|null $totalIdleDuration
     */
    public function setTotalIdleDuration(?int $totalIdleDuration): void
    {
        // @todo remove hotfix with integer
        if (!is_null($totalIdleDuration) && $totalIdleDuration >= 0 && $totalIdleDuration < 2147483647) {
            $this->totalIdleDuration = $totalIdleDuration;
        }
    }

    /**
     * @param RouteTemp $routeTemp
     */
    public function setTotalValuesFromRouteTemp(RouteTemp $routeTemp): void
    {
        switch ($routeTemp->getType()) {
            case RouteTemp::TYPE_DRIVING:
                $this->setTotalMovementDuration($this->getTotalMovementDuration() + $routeTemp->getDuration());
                break;
            case RouteTemp::TYPE_IDLING:
                $this->setTotalIdleDuration($this->getTotalIdleDuration() + $routeTemp->getDuration());
                break;
            case RouteTemp::TYPE_STOP:
                $this->setTotalStopDuration($this->getTotalStopDuration() + $routeTemp->getDuration());
                break;
        }
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
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return array
     */
    public static function getScopes(): array
    {
        return [
            self::SCOPE_PRIVATE,
            self::SCOPE_WORK,
        ];
    }

    /**
     * @return array
     */
    public static function getSettingScopes(): array
    {
        return array_merge(self::getScopes(), [self::SCOPE_UNCATEGORISED]);
    }

    public static function getUserScopes(): array
    {
        return array_merge(self::getScopes(), ['']);
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function settingScopeExists(?string $value): bool
    {
        return in_array($value, self::getSettingScopes());
    }

    /**
     * @param string|null $scope
     */
    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @param $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return int|null
     */
    public function getTeamId()
    {
        return $this->vehicle ? $this->vehicle->getTeam()->getId() : null;
    }

    /**
     * @return float|null
     */
    public function getStartOdometer(): ?float
    {
        return $this->startOdometer;
    }

    /**
     * @param float|null $odometer
     */
    public function setStartOdometer(?float $odometer): void
    {
        $this->startOdometer = $odometer;
    }

    /**
     * @return float|null
     */
    public function getFinishOdometer(): ?float
    {
        return $this->finishOdometer;
    }

    /**
     * @param float|null $odometer
     */
    public function setFinishOdometer(?float $odometer): void
    {
        $this->finishOdometer = $odometer;
    }

    /**
     * @return mixed
     */
    public function getStartCoordinates()
    {
        return $this->startCoordinates;
    }

    /**
     * @param array|null $startCoordinates
     * @return Route
     */
    public function setStartCoordinates(?array $startCoordinates): self
    {
        $this->startCoordinates = $this->convertCoordinatesToPoint($startCoordinates);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFinishCoordinates()
    {
        return $this->finishCoordinates;
    }

    /**
     * @param array|null $finishCoordinates
     * @return Route
     */
    public function setFinishCoordinates(?array $finishCoordinates): self
    {
        $this->finishCoordinates = $this->convertCoordinatesToPoint($finishCoordinates);

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getStartPointData()
    {
        return
            [
                'lastCoordinates' => array_merge(
                    [
                        'ts' => $this->formatDate($this->getStartedAt())
                    ],
                    $this->convertPointToCoordinates($this->getStartCoordinates())
                ),
                'address' => $this->startAddress
            ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFinishPointData()
    {
        return
            [
                'lastCoordinates' => array_merge(
                    [
                        'ts' => $this->formatDate($this->getFinishedAt())
                    ],
                    $this->convertPointToCoordinates($this->getFinishCoordinates())
                ),
                'address' => $this->finishAddress
            ];
    }

    /**
     * @param string|null $address
     */
    public function setStartAddress(?string $address)
    {
        $this->startAddress = $address;
    }

    /**
     * @param string|null $address
     */
    public function setFinishAddress(?string $address)
    {
        $this->finishAddress = $address;
    }


    /**
     * @param string $routeTempType
     * @return string
     */
    public static function getRouteTypeByRouteTemp(string $routeTempType): string
    {
        return ($routeTempType == RouteTemp::TYPE_STOP)
            ? Route::TYPE_STOP
            : Route::TYPE_DRIVING;
    }

    /**
     * @param Device $device
     * @param RouteTemp $routeTemp
     * @param string $routeType
     * @return Route
     */
    public function fromRouteTemp(
        Device $device,
        RouteTemp $routeTemp,
        string $routeType
    ): Route {
        $this->setDevice($device);
        $this->setPointStart($routeTemp->getPointStart());
        $this->setStartedAt($routeTemp->getPointStart()->getTs());
        $this->setType($routeType);
        $this->setDriver($device->getVehicle() ? $device->getVehicle()->getDriver() : null);
        $this->setVehicle($device->getVehicle());
        $this->setMaxSpeed($routeTemp->getMaxSpeed());
        $this->setAvgSpeed($routeTemp->getAvgSpeed());
        $this->setStartOdometer($routeTemp->getPointStart()->getOdometer());

        if ($routeTemp->getPointFinish()) {
            $this->setPointFinish($routeTemp->getPointFinish());
            $this->setFinishedAt($routeTemp->getPointFinish()->getTs());
            $this->setFinishOdometer($routeTemp->getPointFinish()->getOdometer() ?: $this->getStartOdometer());
            $this->setDistance($routeTemp->getDistance());

            if ($this->getFinishOdometer() && !$this->getStartOdometer()) {
                $this->setStartOdometer($this->getFinishOdometer());
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getVehicle() ? $this->getVehicle()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    public function getIsLocationChecked(): bool
    {
        return $this->isLocationChecked;
    }

    public function setIsLocationChecked(bool $value): self
    {
        $this->isLocationChecked = $value;

        return $this;
    }

    public function isStopType(): bool
    {
        return $this->getType() === self::TYPE_STOP;
    }

    public function isDrivingType(): bool
    {
        return $this->getType() === self::TYPE_DRIVING;
    }

    /**
     * @return bool
     */
    public function isRouteWithoutOdometer(): bool
    {
        return (!$this->getStartOdometer() && !$this->getPointFinish())
            || (!$this->getStartOdometer() && $this->getPointFinish() && !$this->getFinishOdometer());
    }

    /**
     * @return float|null
     */
    public function getFinalOdometer(): ?float
    {
        return $this->getFinishOdometer() ?: $this->getStartOdometer();
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getStartArea(): array
    {
        if ($this->getPointStart() && $this->getPointStart()->getLat() && $this->getPointStart()->getLng()) {
            $lat = $this->getPointStart()->getLat();
            $lng = $this->getPointStart()->getLng();

            return $this->em->getRepository(Area::class)->findByPoint($lng . ' ' . $lat, $this->getDevice()->getTeam());
        } else {
            return [];
        }
    }

    public function getStartAreaArray(): array
    {
        return array_map(fn($area) => $area->toArray(['name']), $this->getStartArea());
    }

    public function getFinishAreaArray(): array
    {
        return array_map(fn($area) => $area->toArray(['name']), $this->getFinishArea());
    }

    public function getFinishArea(): array
    {
        if ($this->getPointFinish() && $this->getPointFinish()->getLat() && $this->getPointFinish()->getLng()) {
            $lat = $this->getPointFinish()->getLat();
            $lng = $this->getPointFinish()->getLng();

            return $this->em->getRepository(Area::class)->findByPoint($lng . ' ' . $lat, $this->getDevice()->getTeam());
        } else {
            return [];
        }
    }

    public function getTripCode(): ?string
    {
        return $this->tripCode;
    }

    public function setTripCode(?string $tripCode): self
    {
        $this->tripCode = $tripCode;

        return $this;
    }

    public function getDriverComment(): ?string
    {
        return $this->driverComment;
    }

    public function setDriverComment(?string $comment): self
    {
        $this->driverComment = $comment;

        return $this;
    }

    public function checkStartArea(Area $area): bool
    {
        return !$this->startAreas->isEmpty() && $this->startAreas->filter(
            fn(RouteStartArea $startArea) => $startArea->getArea()->getId() === $area->getId())->count();
    }

    public function checkFinishArea(Area $area): bool
    {
        return !$this->finishAreas->isEmpty() && $this->finishAreas->filter(
            fn(RouteFinishArea $finishArea) => $finishArea->getArea()->getId() === $area->getId())->count();
    }

    public function removeStartAreas()
    {
        foreach ($this->startAreas as $area) {
            $this->em->remove($area);
        }
    }

    public function removeFinishAreas()
    {
        foreach ($this->finishAreas as $area) {
            $this->em->remove($area);
        }
    }
}
