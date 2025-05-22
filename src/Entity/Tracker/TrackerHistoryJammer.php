<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerHistoryJammer
 */
#[ORM\Table(name: 'tracker_history_jammer')]
#[ORM\Index(name: 'tracker_history_jammer_device_id_occurred_at_on_index', columns: ['device_id', 'occurred_at_on'])]
#[ORM\Index(name: 'tracker_history_jammer_vehicle_id_occurred_at_on_index', columns: ['vehicle_id', 'occurred_at_on'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistoryJammerRepository')]
class TrackerHistoryJammer extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'deviceId',
        'vehicleId',
        'driverId',
        'occurredAtOn',
        'occurredAtOff',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Device
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerJammerRecords')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $device;

    /**
     * @var Vehicle|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $vehicle;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'occurred_at_on', type: 'datetime', nullable: false)]
    private $occurredAtOn;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'occurred_at_off', type: 'datetime', nullable: true)]
    private $occurredAtOff;

    /**
     * @var TrackerHistory
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_on_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $trackerHistoryOn;

    /**
     * @var TrackerHistory|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_off_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $trackerHistoryOff;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $driver;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

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
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return int|null
     */
    public function getDeviceId()
    {
        return $this->getDevice()?->getId();
    }

    /**
     * @return string|null
     */
    public function getDeviceModelName()
    {
        return $this->getDevice()?->getModelName();
    }

    /**
     * @return string|null
     */
    public function getDeviceVendorName()
    {
        return $this->getDevice()?->getVendorName();
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    /**
     * @return int|null
     */
    public function getVehicleId()
    {
        return $this->getVehicle()?->getId();
    }

    /**
     * @return User|null
     */
    public function getDriver(): ?User
    {
        return $this->driver;
    }

    /**
     * @return int|null
     */
    public function getDriverId(): ?int
    {
        return $this->getDriver()?->getId();
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
    public function getTrackerHistoryOn(): TrackerHistory
    {
        return $this->trackerHistoryOn;
    }

    /**
     * @param TrackerHistory $trackerHistoryOn
     * @return self
     */
    public function setTrackerHistoryOn(TrackerHistory $trackerHistoryOn): self
    {
        $this->trackerHistoryOn = $trackerHistoryOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOccurredAtOn(): \DateTime
    {
        return $this->occurredAtOn;
    }

    /**
     * @param \DateTime $occurredAtOn
     * @return TrackerHistoryJammer
     */
    public function setOccurredAtOn(\DateTime $occurredAtOn): self
    {
        $this->occurredAtOn = $occurredAtOn;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getOccurredAtOff(): ?\DateTime
    {
        return $this->occurredAtOff;
    }

    /**
     * @param \DateTime|null $occurredAtOff
     */
    public function setOccurredAtOff(?\DateTime $occurredAtOff): self
    {
        $this->occurredAtOff = $occurredAtOff;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastOccurredAt(): \DateTime
    {
        return $this->getOccurredAtOff() ?: $this->getOccurredAtOn();
    }

    /**
     * @return TrackerHistory|null
     */
    public function getTrackerHistoryOff(): ?TrackerHistory
    {
        return $this->trackerHistoryOff;
    }

    /**
     * @param TrackerHistory|null $trackerHistoryOff
     */
    public function setTrackerHistoryOff(?TrackerHistory $trackerHistoryOff): self
    {
        $this->trackerHistoryOff = $trackerHistoryOff;

        return $this;
    }

    /**
     * @param Device $device
     * @return self
     */
    public function setDevice(Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    /**
     * @param Vehicle|null $vehicle
     * @return self
     */
    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * @param User|null $driver
     * @return self
     */
    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return $this
     */
    public function fromTrackerHistory(TrackerHistory $trackerHistory): self
    {
        $this->setDevice($trackerHistory->getDevice());
        $this->setTeam($trackerHistory->getTeam());
        $this->setVehicle($trackerHistory->getVehicle());
        $this->setDriver($trackerHistory->getDriver());

        return $this;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()?->toArray();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }
        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->getDriverId();
        }
        if (in_array('occurredAtOn', $include, true)) {
            $data['occurredAtOn'] = $this->getOccurredAtOn();
        }
        if (in_array('occurredAtOff', $include, true)) {
            $data['occurredAtOff'] = $this->getOccurredAtOff();
        }

        return $data;
    }

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team;
    }

    /**
     * @param Team|null $team
     */
    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }
}
