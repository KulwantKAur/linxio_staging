<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"device", "sensor"},
 *     errorPath="sensor",
 *     message="This Sensor already exists for given device"
 * )
 */
#[ORM\Table(name: 'device_sensor')]
#[ORM\Index(name: 'device_sensor_device_id_updated_at_index', columns: ['device_id', 'updated_at'])]
#[ORM\UniqueConstraint(columns: ['device_id', 'sensor_id'])]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceSensorRepository')]
#[ORM\EntityListeners(['App\EventListener\DeviceSensor\DeviceSensorEntityListener'])]
#[ORM\HasLifecycleCallbacks]
class DeviceSensor extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'deviceId',
        'vehicleId',
        'sensorBLEId',
        'sensor',
        'createdBy',
        'createdAt',
        'updatedBy',
        'updatedAt',
        'lastTrackerHistorySensor',
        'isAutoCreated',
        'team',
        'asset'
    ];

    public const DEFAULT_EXPORT_VALUES = [
        'sensorBLEId',
        'type',
        'createdBy',
        'createdAt',
        'updatedBy',
        'updatedAt',
        'label',
    ];

    public const SENSOR_HISTORY_DISPLAY_VALUES = [
        'deviceId',
        'vehicleId',
        'sensor',
        'lastTrackerHistorySensor',
        'team',
    ];

    public const STATUS_OFFLINE = 0;
    public const STATUS_ONLINE = 1;
    public const STATUS_DELETED = 2;

    public const STATUS_ONLINE_TEXT = 'online';
    public const STATUS_OFFLINE_TEXT = 'offline';
    public const STATUS_DELETED_TEXT = 'deleted';

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Device
     *
     *
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Device', inversedBy: 'trackerSensors')]
    #[ORM\JoinColumn(name: 'device_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $device;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $createdBy;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var TrackerHistory|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistory')]
    #[ORM\JoinColumn(name: 'tracker_history_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $trackerHistory;

    /**
     * @var TrackerHistorySensor|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistorySensor')]
    #[ORM\JoinColumn(name: 'last_tracker_history_sensor_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $lastTrackerHistorySensor;

    /**
     * @var ArrayCollection|TrackerHistorySensor[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Tracker\TrackerHistorySensor', mappedBy: 'deviceSensor', fetch: 'EXTRA_LAZY')]
    private $trackerHistoriesSensor;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'sensors')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: false)]
    private $team;

    /**
     * @var Sensor
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sensor', inversedBy: 'deviceSensors')]
    #[ORM\JoinColumn(name: 'sensor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private $sensor;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'smallint', nullable: false, options: ['default' => 0])]
    private $status = self::STATUS_OFFLINE;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'last_occurred_at', type: 'datetime', nullable: true)]
    private $lastOccurredAt;

    /**
     * @var integer|null
     */
    #[ORM\Column(name: 'rssi', type: 'smallint', nullable: true)]
    private $rssi;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->device = $fields['device'] ?? null;
        $this->sensor = $fields['sensor'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->trackerHistory = $fields['trackerHistory'] ?? null;
        $this->lastTrackerHistorySensor = $fields['lastTrackerHistorySensor'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->trackerHistoriesSensor = new ArrayCollection();
        $this->status = $fields['status'] ?? self::STATUS_OFFLINE;
        $this->rssi = $fields['rssi'] ?? null;
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
            $data['device'] = $this->getDevice()->toArray(Device::SIMPLE_FIELDS);
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle() ? $this->getVehicle()->toArray() : null;
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getSensor()->getType()->toArray();
        }
        if (in_array('sensor', $include, true)) {
            $data['sensor'] = $this->getSensor()->toArray(Sensor::DEVICE_SENSOR_DISPLAY_VALUES);
        }
        if (in_array('sensorId', $include, true)) {
            $data['sensorId'] = $this->getSensor()->getId();
        }
        if (in_array('sensorBLEId', $include, true)) {
            $data['sensorBLEId'] = $this->getSensor()->getSensorId();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy() ? $this->getCreatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
        }
        if (in_array('trackerHistory', $include, true)) {
            $data['trackerHistory'] = $this->getTrackerHistory() ? $this->getTrackerHistory()->toArray() : null;
        }
        if (in_array('trackerHistoryId', $include, true)) {
            $data['trackerHistoryId'] = $this->getTrackerHistory() ? $this->getTrackerHistory()->getId() : null;
        }
        if (in_array('lastTrackerHistorySensor', $include, true)) {
            $data['lastTrackerHistorySensor'] = $this->getLastTrackerHistorySensor()?->toArray();
        }
        if (in_array('isAutoCreated', $include, true)) {
            $data['isAutoCreated'] = $this->isAutoCreated();
        }
        if (in_array('trackerHistoriesSensor', $include, true)) {
            $data['trackerHistoriesSensor'] = $this->getTrackerHistoriesSensor() ?
                array_map(
                    function (TrackerHistorySensor $trackerHistorySensor) {
                        return $trackerHistorySensor->toArray(array_merge(
                            TrackerHistorySensor::DEVICE_SENSOR_HISTORY_DISPLAY_VALUES,
                            ['lastPosition']
                        ));
                    },
                    $this->getTrackerHistoriesSensor()->getValues()
                )
                : null;
        }
        if (in_array('label', $include, true)) {
            $data['label'] = $this->getLabel();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->toArray() : null;
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatusText();
        }
        if (in_array('lastOccurredAt', $include, true)) {
            $data['lastOccurredAt'] = $this->getLastOccurredAt();
        }
        if (in_array('asset', $include, true)) {
            $data['asset'] = $this->getAsset()?->toArray(['name']);
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
        if (in_array('deviceId', $include, true)) {
            $data['deviceId'] = $this->getDeviceId();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice() ? $this->getDevice()->toArray(Device::SIMPLE_FIELDS) : null;
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicleId();
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getSensor()->getTypeLabel();
        }
        if (in_array('sensorBLEId', $include, true)) {
            $data['sensorBLEId'] = $this->getSensor()->getSensorId();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate(
                $this->getCreatedAt(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            );
        }

        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy() ? $this->getCreatedBy()->getFullName() : null;
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate(
                $this->getUpdatedAt(),
                self::EXPORT_DATE_FORMAT,
                $user->getTimezone()
            );
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedBy() ? $this->getUpdatedBy()->getFullName() : null;
        }
        if (in_array('label', $include, true)) {
            $data['label'] = $this->getLabel();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->team ? $this->getTeam()->getClientName() : null;
        }

        return $data;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
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
     * Get createdAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return self
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTimeInterface $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
     * Get updatedAtFormatted
     *
     * @return string
     */
    public function getUpdatedAtFormatted()
    {
        return $this->updatedAt->format(self::EXPORT_DATE_FORMAT);
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return self
     */
    public function setUpdatedBy(User $updatedBy)
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
     * @return Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    /**
     * @return int
     */
    public function getDeviceId(): int
    {
        return $this->getDevice()->getId();
    }

    /**
     * @param Device $device
     */
    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @return int|null
     */
    public function getVehicleId(): ?int
    {
        return $this->getDevice()->getVehicle() ? $this->getDevice()->getVehicle()->getId() : null;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->getDevice()->getVehicle();
    }

    public function getSensorIdField(): string
    {
        return $this->getSensor()->getSensorId();
    }

    /**
     * @return string
     */
    public function getSensorId(): string
    {
        return $this->getSensor()->getId();
    }

    /**
     * @return string
     */
    public function getSensorBLEId(): string
    {
        return $this->getSensor()->getSensorId();
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
     * @return TrackerHistorySensor|null
     */
    public function getLastTrackerHistorySensor(): ?TrackerHistorySensor
    {
        return $this->lastTrackerHistorySensor;
    }

    /**
     * @param TrackerHistorySensor|null $trackerHistorySensor
     */
    public function setLastTrackerHistorySensor(?TrackerHistorySensor $trackerHistorySensor): void
    {
        $this->lastTrackerHistorySensor = $trackerHistorySensor;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getOccurredAt()
    {
        return $this->getLastTrackerHistorySensor() ? $this->getLastTrackerHistorySensor()->getOccurredAt() : null;
    }

    /**
     * @return bool
     */
    public function isAutoCreated(): bool
    {
        return $this->getCreatedBy() ? false : true;
    }

    /**
     * @return TrackerHistorySensor[]|ArrayCollection|null
     */
    public function getTrackerHistoriesSensor()
    {
        return $this->trackerHistoriesSensor;
    }

    /**
     * @param TrackerHistorySensor[]|ArrayCollection|null $trackerHistoriesSensor
     */
    public function setTrackerHistoriesSensor($trackerHistoriesSensor): void
    {
        $this->trackerHistoriesSensor = $trackerHistoriesSensor;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return Collection|null
     */
    public function getTrackerSensorsHistoriesCollectionByRange($startDate, $endDate): ?Collection
    {
        return $this->trackerHistoriesSensor->matching(
            Criteria::create()
                ->where(Criteria::expr()->gte('occurredAt', $startDate))
                ->andWhere(Criteria::expr()->lte('occurredAt', $endDate))
                ->andWhere(Criteria::expr()->eq('isNullableData', false))
                ->orderBy(['occurredAt' => Criteria::ASC])
        ) ?: null;
    }

    /**
     * @param $date
     * @return Collection|null
     */
    public function getLastTrackerSensorsHistoryByDate($date): ?TrackerHistorySensor
    {
        return $this->trackerHistoriesSensor->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('occurredAt', $date))
                ->andWhere(Criteria::expr()->eq('isNullableData', false))
                ->orderBy(['occurredAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array|null
     */
    public function getTrackerSensorsHistoriesDataByRange($startDate, $endDate): ?array
    {
        $sensorsHistoryCollection = $this->getTrackerSensorsHistoriesCollectionByRange($startDate, $endDate);

        return $sensorsHistoryCollection ? array_map(
            function (TrackerHistorySensor $trackerHistorySensor) {
                return $trackerHistorySensor->toArray();
            },
            $sensorsHistoryCollection->toArray()
        ) : [];
    }

    /**
     * @return DeviceSensorType|null
     */
    public function getType(): ?DeviceSensorType
    {
        return $this->getSensor()->getType();
    }

    /**
     * @return string|null
     */
    public function getTypeName(): ?string
    {
        return $this->getType() ? $this->getType()->getName() : null;
    }

    /**
     * @return string|null
     */
    public function getTypeLabel(): ?string
    {
        return $this->getType() ? $this->getType()->getLabel() : null;
    }

    /**
     * @return string|null
     */
    public function getVendorName(): ?string
    {
        return $this->getDevice() ? $this->getDevice()->getVendorName() : null;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->getSensor()->getLabel();
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->getTeam()->getId();
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return string|null
     */
    public function getClient()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient()->getName() : null;
    }

    /**
     * @return Sensor
     */
    public function getSensor(): Sensor
    {
        return $this->sensor;
    }

    /**
     * @param Sensor $sensor
     */
    public function setSensor(Sensor $sensor): void
    {
        $this->sensor = $sensor;
    }


    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return self
     */
    public function setStatus(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusText(): string
    {
        switch ($this->getStatus()) {
            case self::STATUS_ONLINE:
                return self::STATUS_ONLINE_TEXT;
            case self::STATUS_DELETED:
                return self::STATUS_DELETED_TEXT;
            default:
                return self::STATUS_OFFLINE_TEXT;
        }
    }

    public function isStatusOnline(): bool
    {
        $lastTrackerHistory = $this->getDevice()->getLastTrackerRecord();
        $ignition = $lastTrackerHistory ? $lastTrackerHistory->getIgnition() : null;
        $movement = $lastTrackerHistory ? $lastTrackerHistory->getMovement() : null;
        $isOnlineByAccuracyTime = boolval(
            $this->getLastOccurredAt() >= (new Carbon())->subRealSeconds(BaseBLE::RSSI_ACCURACY_TIME)
        );
        $isOnlineByAccuracyStopTime = boolval(
            $this->getLastOccurredAt() >= (new Carbon())->subRealSeconds(BaseBLE::RSSI_ACCURACY_TIME_FOR_STOPPED)
        );

        if ($lastTrackerHistory && !is_null($ignition) && !is_null($movement)) {
            if (!$isOnlineByAccuracyTime && ($ignition || $movement)) {
                return false;
            } elseif (!$isOnlineByAccuracyStopTime && ($ignition == 0 && $movement == 0)) {
                return false;
            } else {
                return true;
            }
        } else {
            return $isOnlineByAccuracyTime;
        }
    }

    /**
     * @return int|null
     */
    public function getRSSI(): ?int
    {
        return $this->rssi;
    }

    /**
     * @param int|null $rssi
     * @return self
     */
    public function setRSSI(?int $rssi): self
    {
        $this->rssi = $rssi;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastOccurredAt(): ?\DateTime
    {
        return $this->lastOccurredAt;
    }

    /**
     * @param \DateTime|null $lastOccurredAt
     * @return self
     */
    public function setLastOccurredAt(?\DateTime $lastOccurredAt): self
    {
        $this->lastOccurredAt = $lastOccurredAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return self::STATUS_DELETED === $this->getStatus();
    }

    /**
     * @return void
     */
    public function setAsDeleted()
    {
        $this->status = self::STATUS_DELETED;
    }

    /**
     * @return void
     */
    public function setAsNotDeleted()
    {
        $this->status = self::STATUS_OFFLINE;
    }

    /**
     * @return Asset|null
     */
    public function getAsset(): ?Asset
    {
        return $this->getSensor()->getAsset();
    }

    /**
     * @return bool
     */
    public function hasAsset(): bool
    {
        return boolval($this->getAsset());
    }
}