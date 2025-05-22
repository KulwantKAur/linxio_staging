<?php

namespace App\Entity;

use App\Entity\Tracker\TrackerHistorySensor;
use App\Service\Asset\AssetService;
use App\Util\AttributesTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Asset
 *
 * @UniqueEntity(
 *     fields={"sensor"},
 *     errorPath="sensor",
 *     message="This sensor already paired with another asset."
 * )
 */
#[ORM\Table(name: 'asset')]
#[ORM\Entity(repositoryClass: 'App\Repository\AssetRepository')]
#[ORM\EntityListeners(['App\EventListener\Asset\AssetEntityListener'])]
class Asset extends BaseEntity
{
    use AttributesTrait;

    public const ALLOWED_STATUSES = [
        self::STATUS_OK,
        self::STATUS_IN_USE,
        self::STATUS_FAULTY,
        self::STATUS_LOST,
        self::STATUS_DELETED
    ];

    public const STATUS_OK = 'ok';
    public const STATUS_IN_USE = 'in_use';
    public const STATUS_FAULTY = 'faulty';
    public const STATUS_LOST = 'lost';

    public const DEFAULT_DISPLAY_VALUES = [
        'idNumber',
        'name',
        'manufacturer',
        'model',
        'serialNumber',
        'team',
        'status',
        'category',
        'depot',
        'groups',
        'team',
        'sensor',
        'deviceSensor',
        'createdAt',
        'updatedAt'
    ];

    public const SENSOR_DISPLAY_VALUES = [
        'idNumber',
        'name',
        'manufacturer',
        'model',
        'serialNumber',
        'team',
        'status',
        'category',
        'createdAt',
        'updatedAt'
    ];

    public function __construct(array $fields = [])
    {
        $this->idNumber = $fields['idNumber'] ?? null;
        $this->name = $fields['name'] ?? null;
        $this->manufacturer = $fields['manufacturer'] ?? null;
        $this->model = $fields['model'] ?? null;
        $this->serialNumber = $fields['serialNumber'] ?? null;
        $this->team = $fields['team'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_OK;
        $this->sensor = $fields['sensor'] ?? null;
        $this->category = $fields['category'] ?? null;
        $this->depot = $fields['depot'] ?? null;
        $this->createdAt = $fields['createdAt'] ?? new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->groups = new ArrayCollection();
        $this->assetSensorHistories = new ArrayCollection();
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('idNumber', $include, true)) {
            $data['idNumber'] = $this->getIdNumber();
        }
        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }
        if (in_array('manufacturer', $include, true)) {
            $data['manufacturer'] = $this->getManufacturer();
        }
        if (in_array('model', $include, true)) {
            $data['model'] = $this->getModel();
        }
        if (in_array('serialNumber', $include, true)) {
            $data['serialNumber'] = $this->getSerialNumber();
        }
        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }
        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }
        if (in_array('category', $include, true)) {
            $data['category'] = $this->getCategory();
        }
        if (in_array('sensor', $include, true)) {
            $data['sensor'] = $this->getSensor()?->toArray();
        }
        if (in_array('device', $include, true)) {
            $data['device'] = $this->getDevice()?->toArray(Device::SIMPLE_FIELDS);
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray();
        }
        if (in_array('location', $include, true)) {
            $data['location'] = $this->getLocation();
        }
        if (in_array('coordinates', $include, true)) {
            $data['coordinates'] = $this->getCoordinatesData();
        }
        if (in_array('deviceSensor', $include, true)) {
            $data['deviceSensor'] = $this->getSensor()?->getDeviceSensorForAssetArray();
        }
        if (in_array('lastTrackerHistorySensor', $include, true)) {
            $data['lastTrackerHistorySensor'] = $this->getLastTrackerHistorySensor()?->toArray();
        }
        if (in_array('depot', $include, true)) {
            $data['depot'] = $this->depot ? $this->getDepotData() : null;
        }
        if (in_array('groups', $include, true)) {
            $data['groups'] = $this->getGroupsArray();
        }
        if (in_array('temperature', $include, true)) {
            $deviceSensor = $this->getDeviceSensor();
            $data['temperature'] = $deviceSensor && $deviceSensor->getLastTrackerHistorySensor()
                ? $deviceSensor->getLastTrackerHistorySensor()->getTemperature()
                : null;
        }
        if (in_array('humidity', $include, true)) {
            $deviceSensor = $this->getDeviceSensor();
            $data['humidity'] = $deviceSensor && $deviceSensor->getLastTrackerHistorySensor()
                ? $deviceSensor->getLastTrackerHistorySensor()->getHumidity()
                : null;
        }
        if (in_array('light', $include, true)) {
            $deviceSensor = $this->getDeviceSensor();
            $data['light'] = $deviceSensor && $deviceSensor->getLastTrackerHistorySensor()
                ? $deviceSensor->getLastTrackerHistorySensor()->getLight()
                : null;
        }
        if (in_array('groupsList', $include, true)) {
            $data['groupsList'] = $this->getGroupsString();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('lastOccurredAt', $include, true)) {
            $data['lastOccurredAt'] = $this->formatDate($this->getLastOccurredAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()?->toArray(User::CREATED_BY_FIELDS);
        }
        if (in_array('updatedAt', $include, true)) {
            $data['updatedAt'] = $this->formatDate($this->getUpdatedAt());
        }
        if (in_array('updatedBy', $include, true)) {
            $data['updatedBy'] = $this->getUpdatedByData();
        }
        if (in_array('todayData', $include, true)) {
            $data['todayData'] = $this->getTodayData();
        }
        if (in_array('gpsStatus', $include, true)) {
            $data['gpsStatus'] = $this->getGPSStatus();
        }

        $data = $this->getNestedFields('vehicle', $include, $data);
        $data = $this->getNestedFields('device', $include, $data);

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'id_number', type: 'string', length: 255, nullable: false)]
    private $idNumber;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private $name;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'manufacturer', type: 'string', length: 255, nullable: true)]
    private $manufacturer;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'model', type: 'string', length: 255, nullable: true)]
    private $model;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'serialNumber', type: 'string', length: 255, nullable: true)]
    private $serialNumber;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 0,
     *      max = 250
     * )
     */
    #[ORM\Column(name: 'category', type: 'string', length: 255, nullable: true)]
    private $category;

    /**
     * @var Team
     *
     * @Assert\NotBlank
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'assets')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var Sensor
     */
    #[ORM\OneToOne(targetEntity: 'Sensor', inversedBy: 'asset')]
    #[ORM\JoinColumn(name: 'sensor_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $sensor;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 250
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'status', type: 'string', length: 255, nullable: false)]
    private $status;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Depot', inversedBy: 'assets')]
    #[ORM\JoinColumn(name: 'depot_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $depot;

    /**
     * Many Assets have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'VehicleGroup', mappedBy: 'assets', fetch: 'EXTRA_LAZY')]
    private $groups;

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
     * @var TrackerHistorySensor|null
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Tracker\TrackerHistorySensor')]
    #[ORM\JoinColumn(name: 'last_tracker_history_sensor_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $lastTrackerHistorySensor;

    /**
     * @var ArrayCollection|AssetSensorHistory[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\AssetSensorHistory', mappedBy: 'asset', fetch: 'EXTRA_LAZY')]
    private $assetSensorHistories;

    /**
     * @var ArrayCollection|AssetSensorHistory[]|null
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Reminder', mappedBy: 'asset', fetch: 'EXTRA_LAZY')]
    private $reminders;

    /**
     * @var AssetService
     */
    private $assetService;

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
     * Set idNumber.
     *
     * @param string|null $idNumber
     *
     * @return Asset
     */
    public function setIdNumber($idNumber = null)
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    /**
     * Get idNumber.
     *
     * @return string|null
     */
    public function getIdNumber()
    {
        return $this->idNumber;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return Asset
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set manufacturer.
     *
     * @param string $manufacturer
     *
     * @return Asset
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get manufacturer.
     *
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Set model.
     *
     * @param string $model
     *
     * @return Asset
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set serialNumber.
     *
     * @param string $serialNumber
     *
     * @return Asset
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber.
     *
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * Set team.
     *
     * @param Team $team
     *
     * @return Asset
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team.
     *
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Asset
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Asset
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
     * @return Asset
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
     * @param \DateTime $updatedAt
     *
     * @return Asset
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
     * Set updatedBy
     *
     * @param User $updatedBy
     *
     * @return Asset
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
     * @return array|null
     */
    public function getUpdatedByData()
    {
        return $this->getUpdatedBy() ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS) : null;
    }

    public function getSensor(): ?Sensor
    {
        return $this->sensor;
    }

    public function setSensor(?Sensor $sensor)
    {
        $this->sensor = $sensor;

        return $this;
    }

    /**
     * Set depot
     *
     * @param Depot $depot
     *
     * @return Asset
     */
    public function setDepot(?Depot $depot)
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * Get depot
     *
     * @return Depot
     */
    public function getDepot()
    {
        return $this->depot;
    }

    /**
     * @return array|null
     */
    public function getDepotData()
    {
        return $this->depot ? $this->getDepot()->toArray(['name', 'status', 'createdAt', 'color']) : null;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getGroupsArray(): array
    {
        return $this->getGroups()->map(
            static function (VehicleGroup $g) {
                return $g->toArray(['name', 'color']);
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getGroupsString()
    {
        $groups = array_map(
            function ($group) {
                return $group->getName();
            },
            $this->groups->toArray()
        );

        return implode(",", $groups);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     */
    public function addToGroup(VehicleGroup $vehicleGroup)
    {
        $this->groups->add($vehicleGroup);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     */
    public function removeFromGroup(VehicleGroup $vehicleGroup)
    {
        $this->groups->removeElement($vehicleGroup);
    }

    /**
     * @param $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return Vehicle|null
     */
    public function getVehicle(): ?Vehicle
    {
        return $this->getSensor()?->getDeviceSensorForAsset()?->getVehicle();
    }

    /**
     * @return Vehicle|null
     * @throws \Exception
     */
    public function getLastVehicle(): ?Vehicle
    {
        $lastTrackerHistorySensor = $this->getLastTrackerHistorySensor();

        return $lastTrackerHistorySensor?->getVehicle();
    }

    /**
     * @return Device|null
     */
    public function getDevice(): ?Device
    {
        $sensor = $this->getSensor();

        return $sensor && $sensor->getDeviceSensorForAsset()
            ? $sensor->getDeviceSensorForAsset()->getDevice()
            : null;
    }

    /**
     * @return Device|null
     */
    public function getDeviceWithDeleted(): ?Device
    {
        $sensor = $this->getSensor();

        return $sensor?->getDeviceSensorForAssetWithDeleted()?->getDevice();
    }

    /**
     * @return string|null
     */
    public function getLocation()
    {
        $lastAssetSensorHistory = $this->getLastAssetSensorHistory();

        if ($lastAssetSensorHistory && $lastAssetSensorHistory->isUninstalled()) {
            $uninstalledAt = $lastAssetSensorHistory->getUninstalledAt();

            return $this->getDeviceWithDeleted()?->getLastRouteByDate($uninstalledAt)?->getAddress();
        }

        return $this->getDeviceWithDeleted()?->getLastTrackerRecord()?->getAddress();
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getCoordinatesData()
    {
        $lastAssetSensorHistory = $this->getLastAssetSensorHistory();

        if ($lastAssetSensorHistory && $lastAssetSensorHistory->isUninstalled()) {
            $uninstalledAt = $lastAssetSensorHistory->getUninstalledAt();

            return $this->getDeviceWithDeleted()?->getLastTrackerRecordByDate($uninstalledAt)?->toArrayCoordinates();
        }

        return $this->getDeviceWithDeleted()?->getLastTrackerRecord()?->toArrayCoordinates();
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function getGPSStatus(): ?bool
    {
        return $this->getDeviceWithDeleted() && $this->getDeviceWithDeleted()->getTrackerData() &&
        isset($this->getDeviceWithDeleted()->getTrackerData()['gpsStatus'])
            ? $this->getDeviceWithDeleted()->getTrackerData()['gpsStatus']
            : null;
    }

    public function getClient()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient() : null;
    }

    public function getClientName()
    {
        return $this->getTeam()->isClientTeam() ? $this->getTeam()->getClient()->getName() : null;
    }

    public function getTimeZoneName()
    {
        return $this->getClient() ? $this->getClient()->getTimeZoneName() : TimeZone::DEFAULT_TIMEZONE['name'];
    }

    /**
     * @return DeviceSensor|null
     */
    public function getDeviceSensor(): ?DeviceSensor
    {
        return $this->getSensor()?->getDeviceSensorForAsset();
    }

    public function getLastOccurredAt()
    {
        return $this->getSensor()?->getLastOccurredAt();
    }

    /**
     * @return \DateTimeInterface|null
     * @throws \Exception
     */
    public function getLastOccurredAtBySensorHistory(): ?\DateTimeInterface
    {
        return $this->getSensor() && $this->getSensor()->getDeviceSensorForAsset()
        && $this->getSensor()->getDeviceSensorForAsset()->getLastTrackerHistorySensor()
            ? $this->getSensor()->getDeviceSensorForAsset()
                ->getLastTrackerHistorySensor()->getOccurredAt()
            : null;
    }

    public function getLastOccurredAtFormatted()
    {
        return $this->getSensor() && $this->getSensor()->getLastOccurredAt()
            ? $this->formatDate($this->getSensor()->getLastOccurredAt())
            : null;
    }

    //for notification 'asset missed'
    public function getDuration()
    {
        return $this->getLastOccurredAt()
            ? (new Carbon())->diffInSeconds($this->getLastOccurredAt())
            : null;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function getLastDataValue(): ?string
    {
        return $this->getSensor() && $this->getSensor()->getDeviceSensorForAsset()
        && $this->getSensor()->getDeviceSensorForAsset()->getLastTrackerHistorySensor()
            ? $this->getSensor()->getDeviceSensorForAsset()
                ->getLastTrackerHistorySensor()->getValueBySensorType()
            : null;
    }

    /**
     * @return TrackerHistorySensor|null
     * @throws \Exception
     */
    public function getLastTrackerHistorySensor(): ?TrackerHistorySensor
    {
        if ($this->lastTrackerHistorySensor) {
            return $this->lastTrackerHistorySensor;
        }

        $lastAssetSensorHistory = $this->getLastAssetSensorHistory();

        if ($lastAssetSensorHistory && $lastAssetSensorHistory->isUninstalled()) {
            $uninstalledAt = $lastAssetSensorHistory->getUninstalledAt();

            return $this->getSensor()?->getDeviceSensorForAssetWithDeleted()
                ?->getLastTrackerSensorsHistoryByDate($uninstalledAt);
        }

        return $this->getSensor()?->getDeviceSensorForAssetWithDeleted()?->getLastTrackerHistorySensor();
    }

    /**
     * @param TrackerHistorySensor|null $lastTrackerHistorySensor
     * @return self
     */
    public function setLastTrackerHistorySensor(?TrackerHistorySensor $lastTrackerHistorySensor): self
    {
        $this->lastTrackerHistorySensor = $lastTrackerHistorySensor;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTodayData(): ?array
    {
        return $this->assetService->getTodayData($this);
        // TODO NEED TO FIX IT
        return [
            'distance' => 0,
            'duration' => 0,
            'avgSpeed' => 0,
        ];
//        return $this->getAssetService()->getDailyData($this);
        return $this->getVehicle()?->getTodayData() ?? [
            'distance' => 0,
            'duration' => 0,
            'avgSpeed' => 0,
        ];
    }

    /**
     * @param AssetService $assetService
     * @return self
     */
    public function setAssetService(AssetService $assetService): self
    {
        $this->assetService = $assetService;

        return $this;
    }

    /**
     * @return AssetService
     */
    public function getAssetService(): AssetService
    {
        return $this->assetService;
    }

    /**
     * @return AssetSensorHistory[]|ArrayCollection|null
     */
    public function getAssetSensorHistories()
    {
        return $this->assetSensorHistories;
    }

    /**
     * @return AssetSensorHistory|null
     */
    public function getLastAssetSensorHistory(): ?AssetSensorHistory
    {
        return $this->getAssetSensorHistories()->matching(
            Criteria::create()->orderBy(['installedAt' => Criteria::DESC])
        )->first() ?: null;
    }

    public function getAssetSensorHistoriesByRange(
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): ?Collection {
        return $this->getAssetSensorHistories()->matching(
            Criteria::create()
                ->where(Criteria::expr()->orX(
                    Criteria::expr()->andX(
                        Criteria::expr()->gte('installedAt', $dateFrom),
                        Criteria::expr()->lte('installedAt', $dateTo)
                    ),
                    Criteria::expr()->andX(
                        Criteria::expr()->isNull('uninstalledAt'),
                        Criteria::expr()->lte('installedAt', $dateFrom)
                    )
                ))
                ->orderBy(['installedAt' => Criteria::ASC])
        ) ?: null;
    }

    public function getSensorBleId(): ?string
    {
        return $this->getSensor()?->getSensorId();
    }

    public function getSensorLabel(): ?string
    {
        return $this->getSensor()?->getLabel();
    }

    /**
     * @return bool
     */
    public function getIsWithVehicle(): bool
    {
        return $this->getSensor() && $this->getSensor()->getVehicle();
    }

    public function getTodayDataKey(): string
    {
        return 'asset_' . $this->getId();
    }

}
