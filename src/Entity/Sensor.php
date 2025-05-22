<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistorySensor;
use App\Service\Tracker\Parser\Topflytech\Model\BaseBLE;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"sensorId"},
 *     repositoryMethod="findByLowerSensorId",
 *     message="Sensor ID already exists"
 * )
 */
#[ORM\Table(name: 'sensor')]
#[ORM\Index(name: 'sensor_label_index', columns: ['label'])]
#[ORM\Entity(repositoryClass: 'App\Repository\SensorRepository')]
#[ORM\HasLifecycleCallbacks]
class Sensor extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'sensorId',
        'type',
        'createdBy',
        'createdAt',
        'updatedBy',
        'updatedAt',
        'isAutoCreated',
        'label',
        'team',
        'systemStatus'
    ];

    public const DEFAULT_EXPORT_VALUES = [
        'sensorId',
        'type',
        'createdBy',
        'createdAt',
        'updatedBy',
        'updatedAt',
        'label',
        'systemStatus'
    ];

    public const DEVICE_SENSOR_DISPLAY_VALUES = [
        'sensorId',
        'type',
        'isAutoCreated',
        'label',
        'systemStatus'
    ];

    public const LIGHT_ON = 'on';
    public const LIGHT_OFF = 'off';

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_ARCHIVE
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var DeviceSensorType|null
     *
     * @Assert\NotBlank
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\DeviceSensorType', inversedBy: 'sensors')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $type;

    /**
     * @todo validation for real MAC: /^([a-fA-F0-9]{2}[:-]){5}([a-fA-F0-9]{2})$/
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^([a-fA-F0-9]{2}){3,6}$/", message="Sensor ID is not valid MAC address")
     */
    #[ORM\Column(name: 'sensor_id', type: 'string', nullable: false, unique: true)]
    private $sensorId;

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
     * @var string|null
     */
    #[ORM\Column(name: 'label', type: 'string', nullable: true)]
    private $label;

    /**
     * @var DeviceSensor[]|ArrayCollection|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceSensor', mappedBy: 'sensor', fetch: 'EXTRA_LAZY')]
    private $deviceSensors;

    /**
     * @var Team
     *
     * @Assert\NotBlank
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private $team;

    /**
     * @var User|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\User', mappedBy: 'sensor', fetch: 'EXTRA_LAZY')]
    private $driver;

    /**
     * @var Asset|null
     */
    #[ORM\OneToOne(targetEntity: 'App\Entity\Asset', mappedBy: 'sensor', fetch: 'EXTRA_LAZY')]
    private $asset;

    /**
     * @var ArrayCollection|DeviceSensorHistory[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\DeviceSensorHistory', mappedBy: 'sensor', fetch: 'EXTRA_LAZY')]
    private $deviceSensorHistories;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100, nullable: true)]
    private $status;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->type = $fields['type'] ?? null;
        $this->sensorId = $fields['sensorId'] ?? null;
        $this->createdAt = Carbon::now('UTC');
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->label = $fields['label'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->deviceSensors = new ArrayCollection();
        $this->deviceSensorHistories = new ArrayCollection();
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
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
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType() ? $this->getType()->toArray() : null;
        }
        if (in_array('sensorId', $include, true)) {
            $data['sensorId'] = $this->getSensorId();
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
        if (in_array('isAutoCreated', $include, true)) {
            $data['isAutoCreated'] = $this->isAutoCreated();
        }
        if (in_array('label', $include, true)) {
            $data['label'] = $this->getLabel();
        }
        if (in_array('deviceSensors', $include, true)) {
            $data['deviceSensors'] = $this->getDeviceSensorsArray();
        }
        if (in_array('devices', $include, true)) {
            $data['devices'] = $this->getDevicesArray();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('teamId', $include, true)) {
            $data['teamId'] = $this->getTeamId();
        }
        if (in_array('asset', $include, true)) {
            $data['asset'] = $this->getAsset() ? $this->getAsset()->toArray(Asset::SENSOR_DISPLAY_VALUES) : null;
        }
        if (in_array('lastTrackerHistorySensor', $include, true)) {
            $data['lastTrackerHistorySensor'] = $this->getLastTrackerHistorySensor()
                ? $this->getLastTrackerHistorySensor()->toArray()
                : null;
        }
        if (in_array('systemStatus', $include, true)) {
            $data['systemStatus'] = $this->getSystemStatus();
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
        if (in_array('label', $include, true)) {
            $data['label'] = $this->getLabel();
        }
        if (in_array('sensorId', $include, true)) {
            $data['sensorId'] = $this->getSensorId();
        }
        if (in_array('type', $include, true)) {
            $data['type'] = $this->getType() ? $this->getType()->getLabel() : null;
        }
        if (in_array('client', $include, true)) {
            $data['client'] = $this->getTeam()->getClient()?->getName();
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
        if (in_array('lastDataValue', $include, true)) {
            $data['lastDataValue'] = $this->getLastTrackerHistorySensor()
                ? $this->getLastTrackerHistorySensor()->getValueBySensorType()
                : null;
        }
        if (in_array('lastDataReceived', $include, true)) {
            $data['lastDataReceived'] = $this->getLastTrackerHistorySensor()
                ? $this->formatDate(
                    $this->getLastTrackerHistorySensor()->getOccurredAt(),
                    self::EXPORT_DATE_FORMAT,
                    $user->getTimezone()
                ) : null;
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
     * @return \Datetime|string
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
     * @return string
     */
    public function getSensorId(): string
    {
        return $this->sensorId;
    }

    /**
     * @param string $sensorId
     * @return self
     */
    public function setSensorId(string $sensorId): self
    {
        $this->sensorId = $sensorId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoCreated(): bool
    {
        return !$this->getCreatedBy();
    }

    /**
     * @return DeviceSensorType|null
     */
    public function getType(): ?DeviceSensorType
    {
        return $this->type;
    }

    /**
     * @param DeviceSensorType|null $type
     * @return self
     */
    public function setType(?DeviceSensorType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTypeId(): ?int
    {
        return $this->getType()?->getId();
    }

    /**
     * @return string|null
     */
    public function getTypeName(): ?string
    {
        return $this->getType()?->getName();
    }

    /**
     * @return string|null
     */
    public function getTypeLabel(): ?string
    {
        return $this->getType()?->getLabel();
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return DeviceSensor[]|ArrayCollection|null
     */
    public function getDeviceSensors()
    {
        return $this->deviceSensors->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('status', DeviceSensor::STATUS_DELETED))
                ->orderBy(['createdAt' => Criteria::DESC])
        );
    }

    /**
     * @return DeviceSensor[]|ArrayCollection|null
     */
    public function getDeviceSensorsByLastOccurredAt()
    {
        $withLastOccurred = $this->deviceSensors->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('status', DeviceSensor::STATUS_DELETED))
                ->andWhere(Criteria::expr()->neq('lastOccurredAt', null))
                ->orderBy(['lastOccurredAt' => Criteria::DESC])
        );

        return !$withLastOccurred->isEmpty()
            ? $withLastOccurred
            : $this->deviceSensors->matching(
                Criteria::create()
                    ->where(Criteria::expr()->neq('status', DeviceSensor::STATUS_DELETED))
                    ->orderBy(['createdAt' => Criteria::DESC])
            );
    }

    /**
     * @return DeviceSensor[]|ArrayCollection|null
     */
    public function getDeviceSensorsWithDeleted()
    {
        $withLastOccurred = $this->deviceSensors->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('lastOccurredAt', null))
                ->orderBy(['lastOccurredAt' => Criteria::DESC])
        );

        return !$withLastOccurred->isEmpty()
            ? $withLastOccurred
            : $this->deviceSensors->matching(
                Criteria::create()
                    ->orderBy(['createdAt' => Criteria::DESC])
            );
    }

    /**
     * @param \DateTimeInterface $date
     * @return DeviceSensorHistory|null
     */
    public function getDeviceSensorHistoryWithDeletedByDate(\DateTimeInterface $date): ?DeviceSensorHistory
    {
        return $this->getDeviceSensorsHistories()->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('installedAt', $date))
                ->orderBy(['installedAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @return DeviceSensorHistory[]|ArrayCollection|null
     */
    public function getDeviceSensorsHistories()
    {
        return $this->deviceSensorHistories;
    }

    /**
     * @param \DateTimeInterface $dateFrom
     * @param \DateTimeInterface $dateTo
     * @return DeviceSensorHistory[]|ArrayCollection|null
     */
    public function getDeviceSensorsHistoriesByRange(\DateTimeInterface $dateFrom, \DateTimeInterface $dateTo)
    {
        return $this->getDeviceSensorsHistories()->matching(
            Criteria::create()
                ->where(Criteria::expr()->orX(
                    Criteria::expr()->andX(
                        Criteria::expr()->gte('installedAt', $dateFrom),
                        Criteria::expr()->lte('installedAt', $dateTo)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->gte('uninstalledAt', $dateFrom),
                        Criteria::expr()->lte('uninstalledAt', $dateTo)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->lte('installedAt', $dateFrom),
                        Criteria::expr()->gte('uninstalledAt', $dateTo)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->lt('installedAt', $dateTo),
                        Criteria::expr()->isNull('uninstalledAt')
                    ),
                ))
                ->orderBy(['installedAt' => Criteria::ASC])
        );
    }

    /**
     * @param int $rssi
     * @param \DateTimeInterface $occurredAt
     * @param Device $device
     * @return bool
     */
    public function hasStrongerDeviceSensorByRSSI(int $rssi, \DateTimeInterface $occurredAt, Device $device): bool
    {
        $occurredAt = Carbon::instance($occurredAt);

        return boolval($this->getDeviceSensors()->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('lastOccurredAt', null))
                ->andWhere(Criteria::expr()->gte(
                    'lastOccurredAt', $occurredAt->subRealSeconds(BaseBLE::RSSI_ACCURACY_TIME)
                ))
                ->andWhere(Criteria::expr()->gt('rssi', $rssi))
                ->andWhere(Criteria::expr()->neq('device', $device))
        )->count());
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @return bool
     */
    public function hasStrongerOrEqualDeviceSensorByRSSI(DeviceSensor $deviceSensor): bool
    {
        $occurredAt = Carbon::instance($deviceSensor->getLastOccurredAt());

        return boolval($this->getDeviceSensors()->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('lastOccurredAt', null))
                ->andWhere(Criteria::expr()->neq('rssi', null))
                ->andWhere(Criteria::expr()->gte(
                    'lastOccurredAt', $occurredAt->subRealSeconds(BaseBLE::RSSI_ACCURACY_TIME)
                ))
                ->andWhere(Criteria::expr()->gte('rssi', $deviceSensor->getRSSI()))
                ->andWhere(Criteria::expr()->neq('device', $deviceSensor->getDevice()))
        )
            ->filter(function (DeviceSensor $deviceSensorItem) use ($occurredAt, $deviceSensor) {
                if ($deviceSensorItem->getRSSI() == $deviceSensor->getRSSI()) {
                    return boolval($deviceSensorItem->getLastOccurredAt() > $occurredAt);
                }

                return true;
            })->count());
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @return bool
     */
    public function hasDeviceSensorWithNewestData(DeviceSensor $deviceSensor): bool
    {
        $occurredAt = Carbon::instance($deviceSensor->getLastOccurredAt());

        return boolval($this->getDeviceSensors()->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('lastOccurredAt', null))
                ->andWhere(Criteria::expr()->neq('rssi', null))
                ->andWhere(Criteria::expr()->gt(
                    'lastOccurredAt', $occurredAt->addRealSeconds(BaseBLE::RSSI_ACCURACY_TIME)
                ))
                ->andWhere(Criteria::expr()->neq('device', $deviceSensor->getDevice()))
        )->count());
    }

    /**
     * @return DeviceSensor[]|ArrayCollection|null
     */
    public function getDeviceSensorsArray()
    {
        return $this->getDeviceSensors()->map(
            static function (DeviceSensor $ds) {
                return $ds->toArray();
            }
        )->toArray();
    }

    /**
     * @return DeviceSensor|null
     */
    public function getDeviceSensorForAsset(): ?DeviceSensor
    {
        return !$this->getDeviceSensorsByLastOccurredAt()->isEmpty()
            ? $this->getDeviceSensorsByLastOccurredAt()->first()
            : null;
    }

    /**
     * @return DeviceSensor|null
     */
    public function getDeviceSensorForAssetWithDeleted(): ?DeviceSensor
    {
        return !$this->getDeviceSensorsWithDeleted()->isEmpty() ? $this->getDeviceSensorsWithDeleted()->first() : null;
    }

    /**
     * @param array $include
     * @return array|null
     * @throws \Exception
     */
    public function getDeviceSensorForAssetArray(array $include = [])
    {
        return $this->getDeviceSensorForAsset()?->toArray($include);
    }

    /**
     * @return DeviceSensor[]|ArrayCollection|null
     */
    public function getDevicesArray()
    {
        return $this->getDeviceSensors()->map(
            static function (DeviceSensor $ds) {
                return $ds->toArray(['device']);
            }
        )->toArray();
    }

    public function getStatus()
    {
        $lastDeviceSensor = $this->getLastDeviceSensor();

        return $lastDeviceSensor?->getStatus();
    }

    public function getLastDeviceSensor(): ?DeviceSensor
    {
        return $this->getDeviceSensors()->matching(
            Criteria::create()
                ->where(Criteria::expr()->neq('lastOccurredAt', null))
                ->orderBy(['lastOccurredAt' => Criteria::DESC])
                ->setMaxResults(1)
        )->first() ?: null;
    }

    /**
     * @return DeviceSensor|null
     */
    public function getLastDeviceSensorWithoutCondition(): ?DeviceSensor
    {
        return $this->getDeviceSensors()->isEmpty() ? null : $this->getDeviceSensors()->last();
    }

    public function isStatusOnline(): bool
    {
        $lastDeviceSensor = $this->getLastDeviceSensor();

        return $lastDeviceSensor && $lastDeviceSensor->isStatusOnline();
    }

    public function getLastOccurredAt()
    {
        return $this->getLastDeviceSensor()?->getLastOccurredAt();
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
     * @return self
     */
    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getDriver(): ?User
    {
        return $this->driver;
    }

    /**
     * @return Asset|null
     */
    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    /**
     * @return int|null
     */
    public function getAssetId(): ?int
    {
        return $this->getAsset()?->getId();
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->getDeviceSensorForAsset()
            ? $this->getDeviceSensorForAsset()->getVehicle()
            : null;
    }

    /**
     * @return TrackerHistorySensor|null
     * @throws \Exception
     */
    public function getLastTrackerHistorySensor(): ?TrackerHistorySensor
    {
        return $this->getLastDeviceSensor() ? $this->getLastDeviceSensor()->getLastTrackerHistorySensor() : null;
    }

    /**
     * @return \DateTime|null
     * @throws \Exception
     */
    public function getLastDataReceived(): ?\DateTime
    {
        return $this->getLastTrackerHistorySensor() ? $this->getLastTrackerHistorySensor()->getOccurredAt() : null;
    }

    /**
     * @return bool
     */
    public function isTypeWithTemperature(): bool
    {
        return DeviceSensorType::isTypeHasTemperature($this->getTypeName());
    }

    public function getClient(): ?Client
    {
        return $this->getTeam()->getClient();
    }

    public function getSystemStatus(): ?string
    {
        return $this->status;
    }

    public function setSystemStatus(string $status): ?self
    {
        $this->status = $status;

        return $this;
    }
}
