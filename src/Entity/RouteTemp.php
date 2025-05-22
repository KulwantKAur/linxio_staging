<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Util\AttributesTrait;
use App\Util\StringHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RouteTemp
 */
#[ORM\Table(name: 'route_temp')]
#[ORM\Index(name: 'route_temp_device_id_started_at_idx', columns: ['device_id', 'started_at'])]
#[ORM\Index(name: 'route_temp_type_idx', columns: ['type'])]
#[ORM\Index(name: 'route_temp_idling_report_idx', columns: ['vehicle_id', 'device_id', 'started_at', 'finished_at', 'driver_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\RouteTempRepository')]
class RouteTemp extends BaseEntity
{
    use AttributesTrait;

    public const TYPE_STOP = Route::TYPE_STOP;
    public const TYPE_DRIVING = Route::TYPE_DRIVING;
    public const TYPE_IDLING = Device::STATUS_IDLE;

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
    #[ORM\ManyToOne(targetEntity: 'Device')]
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
     * @var int|null
     */
    #[ORM\Column(name: 'duration', type: 'integer', nullable: true)]
    private $duration;

    /**
     * @var Vehicle|null
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $driver;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function __toString()
    {
        return (string) $this->getId();
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
        return (!is_null($this->distance) && $this->distance >= 0) ? intval($this->distance) : null;
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
    public function getDevice(): Device
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
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        return [];
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return ($this->getPointStart() && $this->getPointFinish())
            ? $this->getPointFinish()->getTs()->getTimestamp() - $this->getPointStart()->getTs()->getTimestamp()
            : null;
    }

    /**
     * @param int|null $totalStopDuration
     */
    public function setDuration(?int $totalStopDuration): void
    {
        if (!is_null($totalStopDuration) && $totalStopDuration >= 0) {
            $this->duration = $totalStopDuration;
        }
    }

    /**
     * @return TrackerHistory
     */
    public function getLastPoint(): TrackerHistory
    {
        return $this->pointFinish ?: $this->pointStart;
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
     * @return int|null
     */
    public function getTeamId()
    {
        return $this->vehicle ? $this->vehicle->getTeam()->getId() : null;
    }

    /**
     * @return bool
     */
    public function isIdling()
    {
        return $this->getType() === self::TYPE_IDLING;
    }
}
