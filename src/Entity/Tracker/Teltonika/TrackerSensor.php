<?php

namespace App\Entity\Tracker\Teltonika;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerPayload;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerSensor
 */
#[ORM\Table(name: 'tracker_sensor')]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\Teltonika\TrackerSensorRepository')]
class TrackerSensor
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var TrackerPayload|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerPayload')]
    #[ORM\JoinColumn(name: 'tracker_payload_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $trackerPayload;

    /**
     * @var TrackerHistory
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory', inversedBy: 'trackerSensorData')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $trackerHistory;

    /**
     * @var TrackerSensorEvent|null
     */
    #[ORM\ManyToOne(targetEntity: 'TrackerSensorEvent')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: true)]
    private $event;

    /**
     * @var string
     */
    #[ORM\Column(name: 'event_value', type: 'string', length: 255)]
    private $eventValue;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

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
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return TrackerSensor
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
     * @return TrackerHistory
     */
    public function getTrackerHistory(): TrackerHistory
    {
        return $this->trackerHistory;
    }

    /**
     * @param TrackerHistory $trackerHistory
     */
    public function setTrackerHistory(TrackerHistory $trackerHistory): void
    {
        $this->trackerHistory = $trackerHistory;
    }

    /**
     * @return string
     */
    public function getEventValue(): string
    {
        return $this->eventValue;
    }

    /**
     * @param string $eventValue
     */
    public function setEventValue(string $eventValue): void
    {
        $this->eventValue = $eventValue;
    }

    /**
     * @return TrackerSensorEvent|null
     */
    public function getEvent(): ?TrackerSensorEvent
    {
        return $this->event;
    }

    /**
     * @param TrackerSensorEvent|null $event
     */
    public function setEvent(?TrackerSensorEvent $event): void
    {
        $this->event = $event;
    }
}
