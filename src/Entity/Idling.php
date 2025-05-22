<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Idling
 */
#[ORM\Table(name: 'idling')]
#[ORM\Index(name: 'idling_vehicle_id_duration_started_at_finished_at_index', columns: ['vehicle_id', 'duration', 'started_at', 'finished_at'])]
#[ORM\Index(name: 'idling_driver_id_started_at_finished_at_index', columns: ['driver_id', 'started_at', 'finished_at'])]
#[ORM\Index(name: 'idling_device_id_started_at_index', columns: ['device_id', 'started_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\IdlingRepository')]
#[ORM\EntityListeners(['App\EventListener\Idling\IdlingEntityListener'])]
class Idling extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'type',
        'pointStart',
        'pointFinish',
        'duration',
        'address',
        'driverId',
        'vehicleId'
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

    /**
     * @var string
     */
    #[ORM\Column(name: 'address', type: 'text', nullable: true)]
    private $address;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice() ? $this->getDevice()->toArray() : null;
        }

        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDevice() ? $this->getDevice()->getId() : null;
        }

        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle() ? $this->getVehicle()->toArray() : null;
        }

        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle() ? $this->getVehicle()->getId() : null;
        }

        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver() ? $this->getDriver()->toArray() : null;
        }

        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->getDriver() ? $this->getDriver()->getId() : null;
        }

        if (in_array('pointStart', $include, true)) {
            $data['pointStart'] = $this->getPointStart() ? $this->getPointStart()->toArray() : null;
        }

        if (in_array('pointFinish', $include, true)) {
            $data['pointFinish'] = $this->getPointFinish() ? $this->getPointFinish()->toArray() : null;
        }

        if (in_array('coordinates', $include, true)) {
            $data['coordinates'] = $this->getCoordinates();
        }

        if (in_array('duration', $include, true)) {
            $data['duration'] = $this->getDuration();
        }

        if (in_array('address', $include, true)) {
            $data['address'] = $this->getAddress();
        }

        return $data;
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
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return ($this->getPointStart() && $this->getPointFinish())
            ? $this->getPointFinish()->getTs()->getTimestamp() - $this->getPointStart()->getTs()->getTimestamp()
            : null;
    }

    /**
     * @param int|null $duration
     */
    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @param int|null $duration
     */
    public function increaseDuration(?int $duration): void
    {
        $this->duration += $duration;
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
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getVehicle() ? $this->getVehicle()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }
}
