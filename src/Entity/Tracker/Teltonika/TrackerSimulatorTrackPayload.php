<?php

namespace App\Entity\Tracker\Teltonika;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerSimulatorTrackPayload
 */
#[ORM\Table(name: 'tracker_simulator_track_payload')]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\Teltonika\TrackerSimulatorTrackPayloadRepository')]
class TrackerSimulatorTrackPayload
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'payload', type: 'text')]
    private $payload;

    /**
     * @var TrackerSimulatorTrack
     */
    #[ORM\ManyToOne(targetEntity: 'TrackerSimulatorTrack', inversedBy: 'payloads')]
    #[ORM\JoinColumn(name: 'simulator_track_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $simulatorTrack;

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
     * Set payload.
     *
     * @param string $payload
     *
     * @return TrackerSimulatorTrackPayload
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
     * @return TrackerSimulatorTrackPayload
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
     * @return TrackerSimulatorTrack
     */
    public function getSimulatorTrack(): TrackerSimulatorTrack
    {
        return $this->simulatorTrack;
    }

    /**
     * @param TrackerSimulatorTrack $simulatorTrack
     */
    public function setSimulatorTrack(TrackerSimulatorTrack $simulatorTrack): void
    {
        $this->simulatorTrack = $simulatorTrack;
    }
}
