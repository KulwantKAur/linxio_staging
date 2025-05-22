<?php

namespace App\Entity\Tracker\Teltonika;

use App\Entity\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerSimulatorTrack
 */
#[ORM\Table(name: 'tracker_simulator_track')]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\Teltonika\TrackerSimulatorTrackRepository')]
class TrackerSimulatorTrack extends BaseEntity
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'location', type: 'string', length: 255, nullable: true)]
    private $location;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'number', type: 'integer', nullable: true)]
    private $number;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: true)]
    private $type;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'track_duration', type: 'integer', nullable: true)]
    private $trackDuration;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

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
     * @var integer
     */
    #[ORM\Column(name: 'points_count', type: 'integer', nullable: true)]
    private $pointsCount;

    /**
     * @var ArrayCollection|TrackerSimulatorTrackPayload[]
     */
    #[ORM\OneToMany(targetEntity: 'TrackerSimulatorTrackPayload', mappedBy: 'simulatorTrack')]
    private $payloads;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->payloads = new ArrayCollection();
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
     * Set name.
     *
     * @param string|null $name
     *
     * @return TrackerSimulatorTrack
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return TrackerSimulatorTrack
     */
    public function setLocation($location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set number.
     *
     * @param integer|null $number
     *
     * @return TrackerSimulatorTrack
     */
    public function setNumber($number = null)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number.
     *
     * @return integer|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return TrackerSimulatorTrack
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return TrackerSimulatorTrack
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
     * @return TrackerSimulatorTrackPayload[]|ArrayCollection
     */
    public function getPayloads()
    {
        return $this->payloads;
    }

    /**
     * @param TrackerSimulatorTrackPayload[]|ArrayCollection $payloads
     */
    public function setPayloads($payloads): void
    {
        $this->payloads = $payloads;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'user' => $this->getLocation(),
            'createdAt' => $this->getCreatedAt()
        ];
    }

    /**
     * @return int|null
     */
    public function getTrackDuration(): ?int
    {
        return $this->trackDuration;
    }

    /**
     * @param int|null $trackDuration
     */
    public function setTrackDuration(?int $trackDuration): void
    {
        $this->trackDuration = $trackDuration;
    }

    /**
     * @return \DateTime
     */
    public function getStartedAt(): \DateTime
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime $startedAt
     */
    public function setStartedAt(\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @return \DateTime
     */
    public function getFinishedAt(): \DateTime
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTime $finishedAt
     */
    public function setFinishedAt(\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * @return int
     */
    public function getPointsCount(): int
    {
        return $this->pointsCount;
    }

    /**
     * @param int $pointsCount
     */
    public function setPointsCount(int $pointsCount): void
    {
        $this->pointsCount = $pointsCount;
    }
}
