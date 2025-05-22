<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Util\AttributesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"remoteId", "deviceVendor"},
 *     errorPath="remoteId",
 *     message="This remote ID already exists for given device vendor"
 * )
 */
#[ORM\Table(name: 'device_camera_event')]
#[ORM\Index(name: 'dce_vehicle_id_started_at_finished_at_idx', columns: ['vehicle_id', 'started_at', 'finished_at'])]
#[ORM\UniqueConstraint(columns: ['remote_id', 'device_vendor_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceCameraEventRepository')]
#[ORM\HasLifecycleCallbacks]
class DeviceCameraEvent extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'deviceId',
        'vehicle',
        'driver',
        'remoteId',
        'type',
        'remoteType',
        'startedAt',
        'finishedAt',
        'files',
        'trackerHistory',
    ];

    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'remote_id', type: 'string', length: 255, nullable: true)]
    private ?string $remoteId;

    #[ORM\Column(name: 'started_at', type: 'datetime', nullable: false)]
    private \DateTime $startedAt;

    #[ORM\Column(name: 'finished_at', type: 'datetime', nullable: true)]
    private ?\DateTime $finishedAt;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'lastTrackerRecord')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Device $device;

    #[ORM\ManyToOne(targetEntity: 'DeviceVendor')]
    #[ORM\JoinColumn(name: 'device_vendor_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceVendor $deviceVendor;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle', inversedBy: 'trackerHistoriesLast')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Vehicle $vehicle;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?User $driver;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?TrackerHistory $trackerHistory;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private ?Team $team = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\DeviceCameraEventType')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceCameraEventType $type;

    #[ORM\Column(name: 'remote_type', type: 'string', length: 255, nullable: true)]
    private ?string $remoteType;

    #[ORM\Column(name: 'extra_data', type: 'json', nullable: true)]
    private ?array $extraData;

    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceCameraEventFile', mappedBy: 'event', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private ?Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->setCreatedAt(new \DateTime());
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDevice()?->getId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()?->toArray();
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle()?->getId();
        }
        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver()?->toArray();
        }
        if (in_array('startedAt', $include, true)) {
            $data['startedAt'] = $this->formatDate($this->getStartedAt());
        }
        if (in_array('finishedAt', $include, true)) {
            $data['finishedAt'] = $this->formatDate($this->getFinishedAt());
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType()?->toArray();
        }
        if (in_array('files', $include, true)) {
            $data['files'] = $this->getFilesArray();
        }
        if (in_array('trackerHistory', $include, true)) {
            $data['trackerHistory'] = $this->getTrackerHistory()?->toArray(['id', 'lastCoordinates']);
        }

        return $data;
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
     * @return TrackerHistory|null
     */
    public function getTrackerHistory(): ?TrackerHistory
    {
        return $this->trackerHistory;
    }

    /**
     * @param TrackerHistory|null $trackerHistory
     */
    public function setTrackerHistory(?TrackerHistory $trackerHistory): void
    {
        $this->trackerHistory = $trackerHistory;
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
    public function getDeviceVendorName()
    {
        return $this->getDevice()?->getVendorName();
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
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
     * @return \DateTime|null
     */
    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTime|null $finishedAt
     */
    public function setFinishedAt(?\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * @return string|null
     */
    public function getRemoteId(): ?string
    {
        return $this->remoteId;
    }

    /**
     * @param string|null $remoteId
     */
    public function setRemoteId(?string $remoteId): void
    {
        $this->remoteId = $remoteId;
    }

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(?\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @return DeviceVendor|null
     */
    public function getDeviceVendor(): ?DeviceVendor
    {
        return $this->deviceVendor;
    }

    /**
     * @param DeviceVendor|null $deviceVendor
     */
    public function setDeviceVendor(?DeviceVendor $deviceVendor): void
    {
        $this->deviceVendor = $deviceVendor;
    }

    /**
     * @return DeviceCameraEventType|null
     */
    public function getType(): ?DeviceCameraEventType
    {
        return $this->type;
    }

    /**
     * @param DeviceCameraEventType|null $type
     */
    public function setType(?DeviceCameraEventType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getRemoteType(): ?string
    {
        return $this->remoteType;
    }

    /**
     * @param string|null $remoteType
     */
    public function setRemoteType(?string $remoteType): void
    {
        $this->remoteType = $remoteType;
    }

    /**
     * @return DeviceCameraEventFile[]|ArrayCollection
     */
    public function getFiles(): ?Collection
    {
        return $this->files;
    }

    /**
     * @return array|null
     */
    public function getFilesArray(): ?array
    {
        return array_map(
            function (DeviceCameraEventFile $file) {
                return $file->toArray();
            },
            $this->getFiles()->getValues()
        );
    }

    /**
     * @param DeviceCameraEventFile[]|ArrayCollection $files
     */
    public function setFiles(?Collection $files): void
    {
        $this->files = $files;
    }
}
