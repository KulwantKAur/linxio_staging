<?php

namespace App\Entity\Tracker;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tracker_history_dtc_vin')]
#[ORM\Index(name: 'tracker_history_dtc_vin_device_id_created_at_index', columns: ['device_id', 'created_at'])]
#[ORM\Index(name: 'tracker_history_dtc_vin_device_id_occurred_at_index', columns: ['device_id', 'occurred_at'])]
#[ORM\Index(name: 'tracker_history_dtc_vin_vehicle_id_created_at_index', columns: ['vehicle_id', 'created_at'])]
#[ORM\Index(name: 'tracker_history_dtc_vin_vehicle_id_occurred_at_index', columns: ['vehicle_id', 'occurred_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\Tracker\TrackerHistoryDTCVINRepository')]
class TrackerHistoryDTCVIN extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'id',
        'deviceId',
        'vehicleId',
        'code',
        'data',
        'occurredAt',
    ];

    public const DEFAULT_EXPORT_VALUES = [
        'id',
        'deviceId',
        'vehicleId',
        'code',
        'data',
        'occurredAt',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Device|null
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerDTCVINRecords')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $device;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'occurred_at', type: 'datetime')]
    private $occurredAt;

    /**
     * @var TrackerPayload|null
     */
    #[ORM\ManyToOne(targetEntity: 'TrackerPayload', inversedBy: 'trackerHistoryDTCVIN')]
    #[ORM\JoinColumn(name: 'tracker_payload_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $trackerPayload;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var string
     */
    #[ORM\Column(name: 'code', type: 'text', nullable: false)]
    private $code;

    /**
     * @var array|null
     */
    #[ORM\Column(name: 'data', type: 'json', nullable: true)]
    private $data;

    /**
     * @var Vehicle|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Vehicle', inversedBy: 'trackerSensorHistories')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_nullable_data', type: 'boolean', options: ['default' => '0'])]
    private $isNullableData = false;

    /**
     * TrackerHistoryDTCVIN constructor.
     * @param array|null $fields
     * @throws \Exception
     */
    public function __construct(?array $fields = null)
    {
        $this->createdAt = new \DateTime();
        $this->code = $fields['code'] ?? null;
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->device = $fields['device'] ?? null;
        $this->data = $fields['data'] ?? null;
        $this->trackerPayload = $fields['trackerPayload'] ?? null;
        $this->occurredAt = $fields['occurredAt'] ?? null;
        $this->isNullableData = $fields['isNullableData'] ?? false;
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
     * @return \DateTimeInterface|\DateTime
     */
    public function getOccurredAt()
    {
        return $this->occurredAt;
    }

    /**
     * @param \DateTimeInterface $occurredAt
     */
    public function setOccurredAt($occurredAt): void
    {
        $this->occurredAt = $occurredAt;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
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
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->getVehicle() ? $this->getVehicle()->getId() : null;
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }
        if (in_array('data', $include, true)) {
            $data['data'] = $this->getData();
        }
        if (in_array('code', $include, true)) {
            $data['code'] = $this->getCode();
        }
        if (in_array('occurredAt', $include, true)) {
            $data['occurredAt'] = $this->formatDate($this->getOccurredAt());
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }

        return $data;
    }

    /**
     * @param User $user
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toExport(array $include = [], ?User $user = null): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DEFAULT_EXPORT_VALUES;
        }
        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }
        if (in_array('occurredAt', $include, true)) {
            $data['occurredAt'] = $this->formatDate(
                $this->getOccurredAt(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            );
        }
        if (in_array('data', $include, true)) {
            $data['data'] = $this->getData();
        }
        if (in_array('code', $include, true)) {
            $data['code'] = $this->getCode();
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function isNullableData(): bool
    {
        return $this->isNullableData;
    }

    /**
     * @param bool $isNullableData
     */
    public function setIsNullableData(bool $isNullableData): void
    {
        $this->isNullableData = $isNullableData;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
