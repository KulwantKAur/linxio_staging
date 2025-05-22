<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackerCommand
 */
#[ORM\Table(name: 'tracker_command')]
#[ORM\Index(name: 'tracker_command_device_id_created_at_index', columns: ['device_id', 'created_at'])]
#[ORM\Index(name: 'tracker_command_vehicle_id_created_at_index', columns: ['vehicle_id', 'created_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerCommandRepository')]
class TrackerCommand extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'deviceId',
        'vehicleId',
        'command',
        'response',
        'createdAt',
        'respondedAt',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'command_request', type: 'string', nullable: false)]
    private $commandRequest;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'type', type: 'integer', nullable: true)]
    private $type;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'tracker_response', type: 'text', nullable: true)]
    private $trackerResponse;

    /**
     * @var Device|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerCommands')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
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
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private $createdAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'responded_at', type: 'datetime', nullable: true)]
    private $respondedAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'sent_at', type: 'datetime', nullable: true)]
    private $sentAt;

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
        return [
            'id' => $this->getId(),
            'deviceId' => $this->getDeviceId(),
            'vehicleId' => $this->getVehicleId(),
            'command' => $this->getCommandRequest(),
            'response' => $this->getTrackerResponse(),
            'createdAt' => $this->formatDate($this->getCreatedAt()),
            'respondedAt' => $this->formatDate($this->getRespondedAt()),
        ];
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
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->getVehicle() ? $this->getVehicle()->getId() : null;
    }

    /**
     * @param Vehicle|null $vehicle
     */
    public function setVehicle(?Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }

    /**
     * @return string|null
     */
    public function getTimeZoneName()
    {
        return $this->getVehicle() ? $this->getVehicle()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @return \DateTime|null
     */
    public function getRespondedAt(): ?\DateTime
    {
        return $this->respondedAt;
    }

    /**
     * @param \DateTime $respondedAt
     */
    public function setRespondedAt(\DateTime $respondedAt): void
    {
        $this->respondedAt = $respondedAt;
    }

    /**
     * @return bool
     */
    public function isResponded(): bool
    {
        return boolval($this->getRespondedAt());
    }

    /**
     * @return string
     */
    public function getCommandRequest(): string
    {
        return $this->commandRequest;
    }

    /**
     * @param string $commandRequest
     */
    public function setCommandRequest(string $commandRequest): void
    {
        $this->commandRequest = $commandRequest;
    }

    /**
     * @return string|null
     */
    public function getTrackerResponse(): ?string
    {
        return $this->trackerResponse;
    }

    /**
     * @param string $trackerResponse
     */
    public function setTrackerResponse(string $trackerResponse): void
    {
        $this->trackerResponse = $trackerResponse;
    }

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User|null $createdBy
     */
    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return \DateTime|null
     */
    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime|null $sentAt
     */
    public function setSentAt(?\DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    /**
     * @return bool
     */
    public function isSent(): bool
    {
        return boolval($this->getSentAt());
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function setAsSentAndGetCommandRequest(): string
    {
        $this->setSentAt(new \DateTime());

        return $this->getCommandRequest();
    }
}
