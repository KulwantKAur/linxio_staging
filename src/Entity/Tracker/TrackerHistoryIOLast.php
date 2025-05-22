<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\Team;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TrackerHistoryIOLast
 *
 * @UniqueEntity(
 *     fields={"device", "type"},
 *     errorPath="type",
 *     message="This type already exists for given device"
 * )
 */
#[ORM\Table(name: 'tracker_history_io_last')]
#[ORM\UniqueConstraint(columns: ['device_id', 'type_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistoryIOLastRepository')]
class TrackerHistoryIOLast extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'deviceId',
        'sensorIOTypeId',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Device
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $device;

    /**
     * @var TrackerHistoryIO
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistoryIO')]
    #[ORM\JoinColumn(name: 'tracker_history_io_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $trackerHistoryIO;

    /**
     * @var TrackerIOType
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerIOType')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $type;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'occurred_at', type: 'datetime', nullable: false)]
    private $occurredAt;

    /**
     * @var Team|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    public function __construct()
    {}

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
        return $this->getDevice() ? $this->getDevice()->getId() : null;
    }

    /**
     * @return TrackerHistoryIO
     */
    public function getTrackerHistoryIO(): TrackerHistoryIO
    {
        return $this->trackerHistoryIO;
    }

    /**
     * @param TrackerHistoryIO $trackerHistoryIO
     * @return self
     */
    public function setTrackerHistoryIO(TrackerHistoryIO $trackerHistoryIO): self
    {
        $this->trackerHistoryIO = $trackerHistoryIO;

        return $this;
    }

    /**
     * @return TrackerIOType
     */
    public function getType(): TrackerIOType
    {
        return $this->type;
    }

    /**
     * @param TrackerIOType $type
     * @return self
     */
    public function setType(TrackerIOType $type): self
    {
        $this->type = $type;

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
     * @param TrackerHistoryIO $trackerHistoryIO
     * @return self
     */
    public function fromTrackerHistoryIO(TrackerHistoryIO $trackerHistoryIO): self
    {
        $this->setDevice($trackerHistoryIO->getDevice());
        $this->setTeam($trackerHistoryIO->getTeam());
        $this->setTrackerHistoryIO($trackerHistoryIO);
        $this->setType($trackerHistoryIO->getType());
        $this->setOccurredAt($trackerHistoryIO->getLastOccurredAt());

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
     */
    public function setOccurredAt(\DateTime $occurredAt): void
    {
        $this->occurredAt = $occurredAt;
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
            $data['device'] = $this->getDevice() ? $this->getDevice()->toArray() : null;
        }
        if (in_array('sensorIOTypeId', $include, true)) {
            $data['sensorIOTypeId'] = $this->getType() ? $this->getType()->getId() : null;
        }
        if (in_array('occurredAt', $include, true)) {
            $data['occurredAt'] = $this->getOccurredAt();
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
