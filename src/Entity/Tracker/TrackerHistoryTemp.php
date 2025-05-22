<?php

namespace App\Entity\Tracker;

use App\Entity\Device;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tracker_history_temp_part')]
#[ORM\Index(name: 'thtp_created_at_index', columns: ['created_at'])]
#[ORM\Index(name: 'thtp_device_id_created_at_index', columns: ['device_id', 'created_at'])]
#[ORM\Index(name: 'thtp_device_id_ts_index', columns: ['device_id', 'ts'])]
#[ORM\Index(name: 'thtp_is_calculated_index', columns: ['is_calculated'])]
#[ORM\Index(name: 'thtp_is_calculated_idling_index', columns: ['is_calculated_idling'])]
#[ORM\Index(name: 'thtp_is_calculated_speeding_index', columns: ['is_calculated_speeding'])]
#[ORM\Index(name: 'thtp_device_id_is_calculated_ts_index', columns: ['device_id', 'is_calculated', 'ts'])]
#[ORM\Index(name: 'thtp_device_id_is_calculated_speeding_ts_index', columns: ['device_id', 'is_calculated_speeding', 'ts'])]
#[ORM\Index(name: 'thtp_device_id_is_calculated_idling_ts_index', columns: ['device_id', 'is_calculated_idling', 'ts'])]
#[ORM\Index(name: 'thtp_device_id_speed_ts_index', columns: ['device_id', 'speed', 'ts'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistoryTempRepository')]
class TrackerHistoryTemp
{
    public const RECORDS_DAYS_TTL = 3;

    use TrackerHistoryTrait;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'ts', type: 'datetime')]
    private $ts;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'movement', type: 'boolean', nullable: true)]
    private $movement;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'ignition', type: 'boolean', nullable: true)]
    private $ignition;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'speed', type: 'float', nullable: true)]
    private $speed; // km/h
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
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_calculated', type: 'boolean', options: ['default' => '0'])]
    private $isCalculated = false;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_calculated_idling', type: 'boolean', options: ['default' => '0'])]
    private $isCalculatedIdling = false;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_calculated_speeding', type: 'boolean', options: ['default' => '0'])]
    private $isCalculatedSpeeding = false;

    /**
     * @var Vehicle|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle')]
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
     * @var TrackerHistory
     *
     *
     */
    #[ORM\OneToOne(targetEntity: 'TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $trackerHistory;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'traccar_position_id', type: 'bigint', nullable: true)]
    private ?int $traccarPositionId = null;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private ?Team $team = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function __toString()
    {
        return strval($this->getId());
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
     * @param \DateTimeInterface $ts
     *
     * @return self
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
    public function getMovement(): ?bool
    {
        return $this->movement;
    }

    /**
     * @param bool|null $movement
     */
    public function setMovement(?bool $movement): void
    {
        $this->movement = $movement;
    }

    /**
     * @return bool|null
     */
    public function getIgnition(): ?bool
    {
        return $this->ignition;
    }

    /**
     * @param bool|null $ignition
     */
    public function setIgnition(?bool $ignition): void
    {
        $this->ignition = $ignition;
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
     * @return TrackerHistory
     */
    public function getTrackerHistory(): TrackerHistory
    {
        return $this->trackerHistory;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return TrackerHistoryTemp
     */
    public function setTrackerHistory(TrackerHistory $trackerHistory): self
    {
        $this->trackerHistory = $trackerHistory;

        return $this;
    }

    /**
     * @param float|null $speed
     *
     * @return self
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
     * @param TrackerHistory $trackerHistory
     * @return self
     */
    public function fromTrackerHistory(TrackerHistory $trackerHistory): self
    {
        $this->setTrackerHistory($trackerHistory);
        $this->setDevice($trackerHistory->getDevice());
        $this->setTeam($trackerHistory->getTeam());
        $this->setTs($trackerHistory->getTs());
        $this->setMovement(boolval($trackerHistory->getMovement()));
        $this->setIgnition(boolval($trackerHistory->getIgnition()));
        $this->setSpeed($trackerHistory->getSpeed());
        $this->setCreatedAt($trackerHistory->getCreatedAt());
        $this->setVehicle($trackerHistory->getVehicle());
        $this->setDriver($trackerHistory->getDriver());
        $this->setTraccarPositionId($trackerHistory->getTraccarPositionId());

        return $this;
    }
}
