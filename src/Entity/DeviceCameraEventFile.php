<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Service\Device\DeviceStreamService;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"remoteId", "eventId"},
 *     errorPath="remoteId",
 *     message="This remote ID already exists for given event"
 * )
 */
#[ORM\Table(name: 'device_camera_event_file')]
#[ORM\UniqueConstraint(columns: ['remote_id', 'event_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceCameraEventFileRepository')]
#[ORM\HasLifecycleCallbacks]
class DeviceCameraEventFile extends BaseEntity
{
    use AttributesTrait;

    public const CAMERA_TYPE_OUTWARD_ID = 1;
    public const CAMERA_TYPE_DMS_ID = 2;
    public const CAMERA_TYPE_3_ID = 3;
    public const CAMERA_TYPE_4_ID = 4;
    public const CAMERA_TYPE_5_ID = 5;
    public const CAMERA_TYPE_6_ID = 6;
    public const FILE_TYPE_VIDEO = 1;
    public const FILE_TYPE_IMAGE = 2;

    public const DEFAULT_DISPLAY_VALUES = [
        'eventId',
        'url',
        'startedAt',
        'finishedAt',
        'cameraType',
        'fileType',
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

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\DeviceCameraEvent', inversedBy: 'files')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private DeviceCameraEvent $event;

    #[ORM\Column(name: 'url', type: 'text', nullable: true)]
    private ?string $url;

    #[ORM\Column(name: 'camera_type', type: 'smallint', nullable: true)]
    private ?int $cameraType;

    #[ORM\Column(name: 'file_type', type: 'smallint', nullable: true)]
    private ?int $fileType;

    #[ORM\Column(name: 'extra_data', type: 'json', nullable: true)]
    private ?array $extraData;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?TrackerHistory $trackerHistory;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
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
        if (in_array('event', $include, true)) {
            $data['event'] = $this->getEvent()->toArray();
        }
        if (in_array('eventId', $include, true)) {
            $data['eventId'] = $this->getEvent()->getId();
        }
        if (in_array('url', $include, true)) {
            $data['url'] = $this->getUrl();
        }
        if (in_array('startedAt', $include, true)) {
            $data['startedAt'] = $this->formatDate($this->getStartedAt());
        }
        if (in_array('finishedAt', $include, true)) {
            $data['finishedAt'] = $this->formatDate($this->getFinishedAt());
        }
        if (in_array('fileType', $include, true)) {
            $data['fileType'] = $this->getFileType();
        }
        if (in_array('cameraType', $include, true)) {
            $data['cameraType'] = $this->getCameraTypeText();
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getEvent()->getVehicle()?->toArray();
        }
        if (in_array('trackerHistory', $include, true)) {
            $data['trackerHistory'] = $this->getTrackerHistory()?->toArray(['id', 'lastCoordinates']);
        }
        if (in_array('eventType', $include, true)) {
            $data['eventType'] = $this->getEvent()?->getType()?->toArray();
        }

        return $data;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
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
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return int|null
     */
    public function getCameraType(): ?int
    {
        return $this->cameraType;
    }

    /**
     * @param int|null $cameraType
     */
    public function setCameraType(?int $cameraType): void
    {
        $this->cameraType = $cameraType;
    }

    /**
     * @return string|null
     */
    public function getCameraTypeText(): ?string
    {
        return match ($this->getCameraType()) {
            self::CAMERA_TYPE_OUTWARD_ID => DeviceStreamService::TYPE_OUTWARD,
            self::CAMERA_TYPE_DMS_ID => DeviceStreamService::TYPE_DMS,
            self::CAMERA_TYPE_3_ID => DeviceStreamService::TYPE_3,
            self::CAMERA_TYPE_4_ID => DeviceStreamService::TYPE_4,
            self::CAMERA_TYPE_5_ID => DeviceStreamService::TYPE_5,
            self::CAMERA_TYPE_6_ID => DeviceStreamService::TYPE_6,
            default => null
        };
    }

    /**
     * @return DeviceCameraEvent
     */
    public function getEvent(): DeviceCameraEvent
    {
        return $this->event;
    }

    /**
     * @param DeviceCameraEvent $event
     */
    public function setEvent(DeviceCameraEvent $event): void
    {
        $this->event = $event;
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
     * @return int|null
     */
    public function getFileType(): ?int
    {
        return $this->fileType;
    }

    /**
     * @param int|null $fileType
     */
    public function setFileType(?int $fileType): void
    {
        $this->fileType = $fileType;
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
}
