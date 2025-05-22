<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Service\BaseService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VehicleOdometer
 */
#[ORM\Table(name: 'vehicle_odometer')]
#[ORM\Index(name: 'vehicle_odometer_device_id_occurred_at_index', columns: ['device_id', 'occurred_at'])]
#[ORM\Index(name: 'vehicle_odometer_vehicle_id_occurred_at_index', columns: ['vehicle_id', 'occurred_at'])]
#[ORM\Entity(repositoryClass: 'App\Repository\VehicleOdometerRepository')]
class VehicleOdometer extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'vehicleId',
        'deviceId',
        'driverId',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'odometer',
        'accuracy',
        'occurredAt',
        'lastTrackerRecordOccurredAt',
        'lastTrackerRecordOdometer',
        'prevOdometer',
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Vehicle
     *
     * @Assert\NotBlank
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'odometerData')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var int|null
     */
    #[ORM\ManyToOne(targetEntity: 'Device', inversedBy: 'odometerData')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $device;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $driver;

    /**
     * @var int
     *
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    #[ORM\Column(name: 'odometer', type: 'bigint', nullable: false)]
    private $odometer;

    /**
     * @var int
     */
    #[ORM\Column(name: 'odometer_from_device', type: 'bigint', nullable: true)]
    private $odometerFromDevice;

    /**
     * @var TrackerHistory|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $lastTrackerHistory;

    /**
     * @var int
     */
    #[ORM\Column(name: 'accuracy', type: 'bigint', nullable: true)]
    private $accuracy;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_synced_with_device', type: 'boolean', options: ['default' => '0'])]
    private $isSyncedWithDevice = false;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @Assert\LessThanOrEqual(value="now")
     */
    #[ORM\Column(name: 'occurred_at', type: 'datetime', nullable: false)]
    private $occurredAt;

    /**
     * @var \DateTime
     */
    private $lastTrackerRecordOccurredAt;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'prev_odometer', type: 'bigint', nullable: true)]
    private $prevOdometer;

    /**
     * VehicleOdometer constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields = [])
    {
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->device = $fields['device'] ?? null;
        $this->odometer = $fields['odometer'] ?? null;
        $this->prevOdometer = $fields['prevOdometer'] ?? null;
        $this->odometerFromDevice = $fields['odometerFromDevice'] ?? null;
        $this->accuracy = $fields['accuracy'] ?? null;
        $this->createdAt = new \DateTime();
        $this->occurredAt = isset($fields['occurredAt'])
            ? BaseService::parseDateToUTC($fields['occurredAt'])
            : new \DateTime();
    }

    /**
     * @param array $include
     * @return array
     * @throws \Exception
     */
    public function toArray($include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle() ? $this->getVehicle()->getId() : null;
        }
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDevice() ? $this->getDevice()->getId() : null;
        }
        if (in_array('driverId', $include, true)) {
            $data['driverId'] = $this->getDriver() ? intval($this->getDriver()->getId()) : null;
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()
                ? $this->getVehicle()->toArray(Vehicle::DISPLAYED_VALUES)
                : null;
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice() ? $this->getDevice()->toArray(Device::SIMPLE_FIELDS) : null;
        }
        if (in_array('driver', $include, true)) {
            $data['driver'] = $this->getDriver() ? $this->getDriver()->toArray() : null;
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('odometer', $include, true)) {
            $data['odometer'] = $this->getOdometer();
        }
        if (in_array('accuracy', $include, true)) {
            $data['accuracy'] = $this->getAccuracy();
        }
        if (in_array('isSyncedWithDevice', $include, true)) {
            $data['isSyncedWithDevice'] = $this->isSyncedWithDevice();
        }
        if (in_array('createdById', $include, true)) {
            $data['createdById'] = $this->getCreatedById();
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedByFullName();
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('occurredAt', $include, true)) {
            $data['occurredAt'] = $this->formatDate($this->getOccurredAt());
        }
        if (in_array('updatedById', $include, true)) {
            $data['updatedById'] = $this->getUpdatedById();
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByFullName();
        }
        if (in_array('lastTrackerRecordOccurredAt', $include, true)) {
            $data['lastTrackerRecordOccurredAt'] = $this->formatDate($this->getLastTrackerRecordOccurredAt());
        }
        if (in_array('lastTrackerRecordOdometer', $include, true)) {
            $data['lastTrackerRecordOdometer'] = $this->getLastTrackerHistory()
                ? $this->getLastTrackerHistory()->getOdometer()
                : null;
        }
        if (in_array('odometerFromDevice', $include, true)) {
            $data['odometerFromDevice'] = $this->getOdometerFromDevice();
        }
        if (in_array('prevOdometer', $include, true)) {
            $data['prevOdometer'] = $this->getPrevOdometer();
        }

        return $data;
    }

    /**
     * @param Vehicle $vehicle
     * @return $this
     */
    public function fromVehicle(Vehicle $vehicle): self
    {
        $device = $vehicle->getDevice();
        $this->setVehicle($vehicle);
        $this->setDevice($device);
        $this->setDriver($vehicle->getDriver());

        return $this;
    }

    /**
     * Need to be set odometer before use this method `setOdometer()`
     *
     * @param TrackerHistory $trackerHistory
     * @return $this
     */
    public function fromTrackerRecord(?TrackerHistory $trackerHistory): self
    {
        if ($trackerHistory) {
            $this->setLastTrackerHistory($trackerHistory);
            $this->setLastTrackerRecordOccurredAt($trackerHistory->getTs());
            $this->setOdometerFromDevice($trackerHistory->getOdometer());

            if ($this->getOdometerFromDevice() > 0) {
                $this->setAccuracy($this->getOdometer() - $this->getOdometerFromDevice());
            }
        }

        return $this;
    }

    /**
     * @param TrackerHistoryLast $trackerHistoryLast
     * @return $this
     */
    public function fromLastTrackerRecord(?TrackerHistoryLast $trackerHistoryLast): self
    {
        if ($trackerHistoryLast) {
            $this->setLastTrackerHistory($trackerHistoryLast->getTrackerHistory());
            $this->setLastTrackerRecordOccurredAt($trackerHistoryLast->getTs());
            $this->setOdometerFromDevice($trackerHistoryLast->getOdometer());

            if ($this->getOdometerFromDevice() > 0) {
                $this->setAccuracy($this->getOdometer() - $this->getOdometerFromDevice());
            }
        }

        return $this;
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
     * Set device.
     *
     * @param Device|null $device
     *
     * @return self
     */
    public function setDevice(?Device $device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device.
     *
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * Set vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return self
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Set odometer.
     *
     * @param int|null $odometer
     *
     * @return self
     */
    public function setOdometer(?int $odometer)
    {
        $this->odometer = $odometer;

        return $this;
    }

    /**
     * Get odometer.
     *
     * @return int | null
     */
    public function getOdometer(): ?int
    {
        return $this->odometer;
    }

    /**
     * @return bool
     */
    public function isSyncedWithDevice(): bool
    {
        return $this->isSyncedWithDevice;
    }

    /**
     * @param bool $isSyncedWithDevice
     */
    public function setIsSyncedWithDevice(bool $isSyncedWithDevice): void
    {
        $this->isSyncedWithDevice = $isSyncedWithDevice;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int|null
     */
    public function getOdometerFromDevice(): ?int
    {
        return $this->odometerFromDevice;
    }

    /**
     * @param int|null $odometerFromDevice
     */
    public function setOdometerFromDevice(?int $odometerFromDevice): void
    {
        $this->odometerFromDevice = $odometerFromDevice;
    }

    /**
     * @return TrackerHistory|null
     */
    public function getLastTrackerHistory(): ?TrackerHistory
    {
        return $this->lastTrackerHistory;
    }

    /**
     * @param TrackerHistory|null $lastTrackerHistory
     */
    public function setLastTrackerHistory(?TrackerHistory $lastTrackerHistory): void
    {
        $this->lastTrackerHistory = $lastTrackerHistory;
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
    public function getAccuracy(): ?int
    {
        return $this->accuracy;
    }

    /**
     * @param int $accuracy
     */
    public function setAccuracy(?int $accuracy): void
    {
        $this->accuracy = $accuracy;
    }

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @return int|null
     */
    public function getCreatedById(): ?int
    {
        return $this->getCreatedBy() ? $this->getCreatedBy()->getId() : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getCreatedByData(): ?array
    {
        return $this->getCreatedBy() ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getCreatedByFullName(): ?array
    {
        return $this->getCreatedBy() ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @param User $createdBy
     * @return self
     */
    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set updatedBy
     *
     * @param User|null $updatedBy
     *
     * @return self
     */
    public function setUpdatedBy(?User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return User|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getUpdatedByData()
    {
        return $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @return int|null
     */
    public function getUpdatedById(): ?int
    {
        return $this->getUpdatedBy() ? $this->getUpdatedBy()->getId() : null;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getUpdatedByFullName(): ?array
    {
        return $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    /**
     * @return \DateTime|null
     */
    public function getOccurredAt(): ?\DateTime
    {
        return $this->occurredAt;
    }

    /**
     * @param \DateTime|null $occurredAt
     */
    public function setOccurredAt(?\DateTime $occurredAt): void
    {
        $this->occurredAt = $occurredAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastTrackerRecordOccurredAt(): ?\DateTime
    {
        return $this->lastTrackerRecordOccurredAt;
    }

    /**
     * @param \DateTime $lastTrackerRecordOccurredAt
     */
    public function setLastTrackerRecordOccurredAt(\DateTime $lastTrackerRecordOccurredAt): void
    {
        $this->lastTrackerRecordOccurredAt = $lastTrackerRecordOccurredAt;
    }

    /**
     * @return int|null
     */
    public function getPrevOdometer(): ?int
    {
        return $this->prevOdometer;
    }

    /**
     * @param int|null $prevOdometer
     */
    public function setPrevOdometer(?int $prevOdometer): void
    {
        $this->prevOdometer = $prevOdometer;
    }
}
