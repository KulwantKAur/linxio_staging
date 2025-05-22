<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\Traccar\Model\TraccarEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * TraccarEventHistory
 */
#[ORM\Table(name: 'traccar_event_history')]
#[ORM\Index(name: 'traccar_event_history_device_id_occurred_at_index', columns: ['device_id', 'occurred_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TraccarEventHistoryRepository')]
class TraccarEventHistory extends BaseEntity
{
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
    #[ORM\Column(name: 'occurred_at', type: 'datetime')]
    private $occurredAt;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: true)]
    private $type;

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
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'traccarEventHistories')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var Vehicle|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle', inversedBy: 'trackerRecords')]
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
     * @var int|null
     */
    #[ORM\Column(name: 'traccar_event_id', type: 'bigint', nullable: true)]
    private $traccarEventId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'traccar_position_id', type: 'bigint', nullable: true)]
    private $traccarPositionId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'traccar_geofence_id', type: 'bigint', nullable: true)]
    private $traccarGeofenceId;

    /**
     * @var TrackerPayload|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerPayload', inversedBy: 'traccarEventHistory')]
    #[ORM\JoinColumn(name: 'payload_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $payload;

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
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [];
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
    public function getTraccarEventId(): ?int
    {
        return $this->traccarEventId;
    }

    /**
     * @param int|null $traccarEventId
     * @return TraccarEventHistory
     */
    public function setTraccarEventId(?int $traccarEventId): TraccarEventHistory
    {
        $this->traccarEventId = $traccarEventId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTraccarPositionId(): ?int
    {
        return $this->traccarPositionId;
    }

    /**
     * @param int|null $traccarPositionId
     * @return TraccarEventHistory
     */
    public function setTraccarPositionId(?int $traccarPositionId): TraccarEventHistory
    {
        $this->traccarPositionId = $traccarPositionId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTraccarGeofenceId(): ?int
    {
        return $this->traccarGeofenceId;
    }

    /**
     * @param int|null $traccarGeofenceId
     * @return TraccarEventHistory
     */
    public function setTraccarGeofenceId(?int $traccarGeofenceId): TraccarEventHistory
    {
        $this->traccarGeofenceId = $traccarGeofenceId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return TraccarEventHistory
     */
    public function setType(?string $type): TraccarEventHistory
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOccurredAt(): \DateTime
    {
        return $this->occurredAt;
    }

    /**
     * @param \DateTime $occurredAt
     * @return TraccarEventHistory
     */
    public function setOccurredAt(\DateTime $occurredAt): TraccarEventHistory
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    /**
     * @param TraccarEvent $traccarEvent
     */
    public function fromTraccarEvent(TraccarEvent $traccarEvent)
    {
        $this->setTraccarPositionId($traccarEvent->getPositionId());
        $this->setTraccarEventId($traccarEvent->getId());
        $this->setTraccarGeofenceId($traccarEvent->getGeofenceId());
        $this->setOccurredAt($traccarEvent->getEventTime());
        $this->setType($traccarEvent->getType());
    }

    /**
     * @return TrackerPayload|null
     */
    public function getPayload(): ?TrackerPayload
    {
        return $this->payload;
    }

    /**
     * @param TrackerPayload|null $payload
     * @return TraccarEventHistory
     */
    public function setPayload(?TrackerPayload $payload): TraccarEventHistory
    {
        $this->payload = $payload;

        return $this;
    }
}
