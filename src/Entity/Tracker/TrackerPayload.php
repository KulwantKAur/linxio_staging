<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\DrivingBehavior;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerPayload
 */
#[ORM\Table(name: 'tracker_payload')]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerPayloadRepository')]
class TrackerPayload extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var TrackerAuth|null
     */
    #[ORM\ManyToOne(targetEntity: 'TrackerAuth')]
    #[ORM\JoinColumn(name: 'tracker_auth_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $trackerAuth;

    /**
     * @var string
     */
    #[ORM\Column(name: 'payload', type: 'text')]
    private $payload;

    /**
     * @var ArrayCollection|TrackerHistory[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistory', mappedBy: 'trackerPayload')]
    private $trackerHistory;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var Device|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $device;

    /**
     * @var ArrayCollection|TraccarEventHistory[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TraccarEventHistory', mappedBy: 'payload', fetch: 'EXTRA_LAZY')]
    private $traccarEventHistory;

    /**
     * @var ArrayCollection|DrivingBehavior[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DrivingBehavior', mappedBy: 'trackerPayload')]
    private $drivingBehavior;

    /**
     * @var ArrayCollection|TrackerHistorySensor[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistorySensor', mappedBy: 'trackerPayload', fetch: 'EXTRA_LAZY')]
    private $trackerHistorySensor;

    /**
     * @var ArrayCollection|TrackerHistoryDTCVIN[]
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistoryDTCVIN', mappedBy: 'trackerPayload', fetch: 'EXTRA_LAZY')]
    private $trackerHistoryDTCVIN;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
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
     * Set payload.
     *
     * @param string $payload
     *
     * @return TrackerPayload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return TrackerPayload
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
     * @return TrackerAuth|null
     */
    public function getTrackerAuth(): ?TrackerAuth
    {
        return $this->trackerAuth;
    }

    /**
     * @param TrackerAuth|null $trackerAuth
     */
    public function setTrackerAuth(?TrackerAuth $trackerAuth): void
    {
        $this->trackerAuth = $trackerAuth;
    }

    /**
     * @return TrackerHistory[]|ArrayCollection
     */
    public function getTrackerHistory()
    {
        return $this->trackerHistory;
    }

    /**
     * @param TrackerHistory[]|ArrayCollection $trackerHistory
     */
    public function setTrackerHistory($trackerHistory): void
    {
        $this->trackerHistory = $trackerHistory;
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
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'device' => $this->getDevice(),
            'trackerAuth' => $this->getTrackerAuth() ? $this->getTrackerAuth()->getId() : null,
            'createdAt' => $this->formatDate($this->getCreatedAt()),
        ];
    }

    /**
     * @return TraccarEventHistory[]|ArrayCollection
     */
    public function getTraccarEventHistory()
    {
        return $this->traccarEventHistory;
    }

    /**
     * @return DrivingBehavior[]|ArrayCollection
     */
    public function getDrivingBehavior()
    {
        return $this->drivingBehavior;
    }

    /**
     * @return TrackerHistorySensor[]|ArrayCollection
     */
    public function getTrackerHistorySensor()
    {
        return $this->trackerHistorySensor;
    }

    /**
     * @return TrackerHistoryDTCVIN[]|ArrayCollection
     */
    public function getTrackerHistoryDTCVIN()
    {
        return $this->trackerHistoryDTCVIN;
    }

    /**
     * @return string|null
     */
    public function getSocketId(): ?string
    {
        return $this->getTrackerAuth()?->getSocketId();
    }
}
