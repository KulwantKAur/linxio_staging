<?php

namespace App\Entity;

use App\Repository\BillingPlanRepository;
use App\Util\AttributesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillingPlanRepository::class)]
class BillingPlan extends BaseEntity
{

    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'deviceVehicleActive',
        'deviceVehicleDeactivated',
        'devicePersonalActive',
        'devicePersonalDeactivated',
        'deviceAssetActive',
        'deviceAssetDeactivated',
        'deviceSatelliteActive',
        'deviceSatelliteDeactivated',
        'vehicleVirtual',
        'tempSensor',
        'status',
        'clientCount',
        'vehicleArchived',
        'sensorArchived',
        'signPostVehicle'
    ];

    public const EDITABLE_FIELDS = [
        'name',
        'deviceVehicleActive',
        'deviceVehicleDeactivated',
        'devicePersonalActive',
        'devicePersonalDeactivated',
        'deviceAssetActive',
        'deviceAssetDeactivated',
        'deviceSatelliteActive',
        'deviceSatelliteDeactivated',
        'vehicleVirtual',
        'tempSensor',
        'vehicleArchived',
        'sensorArchived',
        'signPostVehicle'
    ];

    public function __construct(array $fields = [])
    {
        $this->name = $fields['name'] ?? null;
        $this->deviceVehicleActive = $fields['deviceVehicleActive'] ?? null;
        $this->deviceVehicleDeactivated = $fields['deviceVehicleDeactivated'] ?? null;
        $this->devicePersonalActive = $fields['devicePersonalActive'] ?? null;
        $this->devicePersonalDeactivated = $fields['devicePersonalDeactivated'] ?? null;
        $this->deviceAssetActive = $fields['deviceAssetActive'] ?? null;
        $this->deviceAssetDeactivated = $fields['deviceAssetDeactivated'] ?? null;
        $this->deviceSatelliteActive = $fields['deviceSatelliteActive'] ?? null;
        $this->deviceSatelliteDeactivated = $fields['deviceSatelliteDeactivated'] ?? null;
        $this->vehicleVirtual = $fields['vehicleVirtual'] ?? null;
        $this->vehicleArchived = $fields['vehicleArchived'] ?? null;
        $this->tempSensor = $fields['tempSensor'] ?? null;
        $this->sensorArchived = $fields['sensorArchived'] ?? null;
        $this->signPostVehicle = $fields['signPostVehicle'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->clients = new ArrayCollection();
        $this->plan = $fields['plan'] ?? null;
        $this->team = $fields['team'] ?? null;
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('deviceVehicleActive', $include, true)) {
            $data['deviceVehicleActive'] = $this->getDeviceVehicleActive();
        }

        if (in_array('deviceVehicleDeactivated', $include, true)) {
            $data['deviceVehicleDeactivated'] = $this->getDeviceVehicleDeactivated();
        }

        if (in_array('devicePersonalActive', $include, true)) {
            $data['devicePersonalActive'] = $this->getDevicePersonalActive();
        }

        if (in_array('devicePersonalDeactivated', $include, true)) {
            $data['devicePersonalDeactivated'] = $this->getDevicePersonalDeactivated();
        }

        if (in_array('deviceAssetActive', $include, true)) {
            $data['deviceAssetActive'] = $this->getDeviceAssetActive();
        }

        if (in_array('deviceAssetDeactivated', $include, true)) {
            $data['deviceAssetDeactivated'] = $this->getDeviceAssetDeactivated();
        }

        if (in_array('vehicleVirtual', $include, true)) {
            $data['vehicleVirtual'] = $this->getVehicleVirtual();
        }

        if (in_array('tempSensor', $include, true)) {
            $data['tempSensor'] = $this->getTempSensor();
        }

        if (in_array('vehicleArchived', $include, true)) {
            $data['vehicleArchived'] = $this->getVehicleArchived();
        }

        if (in_array('sensorArchived', $include, true)) {
            $data['sensorArchived'] = $this->getSensorArchived();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('deviceSatelliteActive', $include, true)) {
            $data['deviceSatelliteActive'] = $this->getDeviceSatelliteActive();
        }

        if (in_array('deviceSatelliteDeactivated', $include, true)) {
            $data['deviceSatelliteDeactivated'] = $this->getDeviceSatelliteDeactivated();
        }

        if (in_array('signPostVehicle', $include, true)) {
            $data['signPostVehicle'] = $this->getSignPostVehicle();
        }

        return $data;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'float', nullable: true)]
    private $deviceVehicleActive;

    #[ORM\Column(type: 'float', nullable: true)]
    private $deviceVehicleDeactivated;

    #[ORM\Column(type: 'float', nullable: true)]
    private $devicePersonalActive;

    #[ORM\Column(type: 'float', nullable: true)]
    private $devicePersonalDeactivated;

    #[ORM\Column(type: 'float', nullable: true)]
    private $deviceAssetActive;

    #[ORM\Column(type: 'float', nullable: true)]
    private $deviceAssetDeactivated;

    #[ORM\Column(type: 'float', nullable: true)]
    private $deviceSatelliteActive;

    #[ORM\Column(type: 'float', nullable: true)]
    private $deviceSatelliteDeactivated;

    #[ORM\Column(type: 'float', nullable: true)]
    private $vehicleVirtual;

    #[ORM\Column(type: 'float', nullable: true)]
    private $tempSensor;

    #[ORM\Column(type: 'float', nullable: true)]
    private $vehicleArchived;

    #[ORM\Column(type: 'float', nullable: true)]
    private $sensorArchived;

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
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100, nullable: true)]
    private $status = self::STATUS_ACTIVE;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team', inversedBy: 'billingPlan')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true)]
    private $team;

    /**
     * @var Plan
     */
    #[ORM\ManyToOne(targetEntity: 'Plan')]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id', nullable: true)]
    private $plan;

    #[ORM\Column(type: 'float', nullable: true)]
    private $signPostVehicle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDeviceVehicleActive(): ?float
    {
        return $this->deviceVehicleActive;
    }

    public function setDeviceVehicleActive(?float $deviceVehicleActive): self
    {
        $this->deviceVehicleActive = $deviceVehicleActive;

        return $this;
    }

    public function getDeviceVehicleDeactivated(): ?float
    {
        return $this->deviceVehicleDeactivated;
    }

    public function setDeviceVehicleDeactivated(?float $deviceVehicleDeactivated): self
    {
        $this->deviceVehicleDeactivated = $deviceVehicleDeactivated;

        return $this;
    }

    public function getDevicePersonalActive(): ?float
    {
        return $this->devicePersonalActive;
    }

    public function setDevicePersonalActive(?float $devicePersonalActive): self
    {
        $this->devicePersonalActive = $devicePersonalActive;

        return $this;
    }

    public function getDevicePersonalDeactivated(): ?float
    {
        return $this->devicePersonalDeactivated;
    }

    public function setDevicePersonalDeactivated(?float $devicePersonalDeactivated): self
    {
        $this->devicePersonalDeactivated = $devicePersonalDeactivated;

        return $this;
    }

    public function getDeviceAssetActive(): ?float
    {
        return $this->deviceAssetActive;
    }

    public function setDeviceAssetActive(?float $deviceAssetActive): self
    {
        $this->deviceAssetActive = $deviceAssetActive;

        return $this;
    }

    public function getDeviceAssetDeactivated(): ?float
    {
        return $this->deviceAssetDeactivated;
    }

    public function setDeviceAssetDeactivated(?float $deviceAssetDeactivated): self
    {
        $this->deviceAssetDeactivated = $deviceAssetDeactivated;

        return $this;
    }

    public function getVehicleVirtual(): ?float
    {
        return $this->vehicleVirtual;
    }

    public function setVehicleVirtual(?float $vehicleVirtual): self
    {
        $this->vehicleVirtual = $vehicleVirtual;

        return $this;
    }

    public function getTempSensor(): ?float
    {
        return $this->tempSensor;
    }

    public function setTempSensor(?float $tempSensor): self
    {
        $this->tempSensor = $tempSensor;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BillingPlan
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
     * @return BillingPlan
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
     * @return BillingPlan
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
     * @return BillingPlan
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
     */
    public function getUpdatedByData()
    {
        return $this->getUpdatedBy()?->toArray(User::CREATED_BY_FIELDS);
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return BillingPlan
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set plan
     *
     * @param Plan $plan
     *
     * @return BillingPlan
     */
    public function setPlan(?Plan $plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get planId
     *
     * @return Plan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set team
     *
     * @param Team $team
     *
     * @return BillingPlan
     */
    public function setTeam(Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * Get teamId
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->getTeam()->getId();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    public function getVehicleArchived(): ?float
    {
        return $this->vehicleArchived;
    }

    public function setVehicleArchived(?float $vehicleArchived): self
    {
        $this->vehicleArchived = $vehicleArchived;

        return $this;
    }

    public function getSensorArchived(): ?float
    {
        return $this->sensorArchived;
    }

    public function setSensorArchived(?float $sensorArchived): self
    {
        $this->sensorArchived = $sensorArchived;

        return $this;
    }

    public function getFieldsForHistory()
    {
        return [
            'deviceVehicleActive' => $this->getDeviceVehicleActive(),
            'deviceVehicleDeactivated' => $this->getDeviceVehicleDeactivated(),
            'devicePersonalActive' => $this->getDevicePersonalActive(),
            'devicePersonalDeactivated' => $this->getDevicePersonalDeactivated(),
            'deviceAssetActive' => $this->getDeviceAssetActive(),
            'deviceAssetDeactivated' => $this->getDeviceAssetDeactivated(),
            'vehicleVirtual' => $this->getVehicleVirtual(),
            'vehicleArchived' => $this->getVehicleArchived(),
            'sensorArchived' => $this->getSensorArchived(),
            'tempSensor' => $this->getTempSensor(),
            'signPostVehicle' => $this->getSignPostVehicle(),
        ];
    }

    public function archive()
    {
        $this->setStatus(BaseEntity::STATUS_ARCHIVE);

        $newBillingPlan = clone $this;
        $newBillingPlan->setStatus(BaseEntity::STATUS_ACTIVE);
        $newBillingPlan->setCreatedAt(new \DateTime());

        return $newBillingPlan;
    }

    public function getDeviceSatelliteActive(): ?float
    {
        return $this->deviceSatelliteActive;
    }

    public function setDeviceSatelliteActive(?float $deviceSatelliteActive): self
    {
        $this->deviceSatelliteActive = $deviceSatelliteActive;

        return $this;
    }

    public function getDeviceSatelliteDeactivated(): ?float
    {
        return $this->deviceSatelliteDeactivated;
    }

    public function setDeviceSatelliteDeactivated(?float $deviceSatelliteDeactivated): self
    {
        $this->deviceSatelliteDeactivated = $deviceSatelliteDeactivated;

        return $this;
    }

    public function getSignPostVehicle(): ?float
    {
        return $this->signPostVehicle;
    }

    public function setSignPostVehicle(?float $signPostVehicle): self
    {
        $this->signPostVehicle = $signPostVehicle;

        return $this;
    }
}
