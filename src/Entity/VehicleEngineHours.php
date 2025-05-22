<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * VehicleEngineHours
 */
#[ORM\Table(name: 'vehicle_engine_hours')]
#[ORM\Entity(repositoryClass: 'App\Repository\VehicleEngineHoursRepository')]
class VehicleEngineHours extends BaseEntity
{
    public const DEFAULT_DISPLAY_VALUES = [
        'vehicleId',
        'engineHours',
        'prevEngineHours',
        'createdAt',
        'createdBy'
    ];

    public function __construct(array $fields = [])
    {
        $this->vehicle = $fields['vehicle'] ?? null;
        $this->engineHours = $fields['engineHours'] ?? null;
        $this->prevEngineHours = $fields['prevEngineHours'] ?? null;
        $this->createdAt = new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
    }

    public function toArray($include = []): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('vehicleId', $include, true)) {
            $data['vehicleId'] = $this->getVehicle()?->getId();
        }
        if (in_array('vehicle', $include, true)) {
            $data['vehicle'] = $this->getVehicle()?->toArray(Vehicle::DISPLAYED_VALUES);
        }
        if (in_array('engineHours', $include, true)) {
            $data['engineHours'] = $this->getEngineHours();
        }
        if (in_array('prevEngineHours', $include, true)) {
            $data['prevEngineHours'] = $this->getPrevEngineHours();
        }
        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->getCreatedAt());
        }
        if (in_array('createdBy', $include, true)) {
            $data['createdBy'] = $this->getCreatedBy()?->toArray(User::CREATED_BY_FIELDS);
        }

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
     * @var Vehicle
     *
     * @Assert\NotBlank
     */
    #[ORM\ManyToOne(targetEntity: 'Vehicle', inversedBy: 'engineHoursData')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private $vehicle;

    /**
     * @var int
     *
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    #[ORM\Column(name: 'engine_hours', type: 'bigint', nullable: false)]
    private $engineHours;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    #[ORM\Column(name: 'prev_engine_hours', type: 'bigint', nullable: true)]
    private $prevEngineHours;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: false)]
    private $createdBy;


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
     * Set engineHours.
     *
     * @param int|null $engineHours
     *
     * @return VehicleEngineHours
     */
    public function setEngineHours($engineHours = null)
    {
        $this->engineHours = $engineHours;

        return $this;
    }

    /**
     * Get engineHours.
     *
     * @return int|null
     */
    public function getEngineHours()
    {
        return $this->engineHours;
    }

    /**
     * Set prevEngineHours.
     *
     * @param int|null $prevEngineHours
     *
     * @return VehicleEngineHours
     */
    public function setPrevEngineHours($prevEngineHours = null)
    {
        $this->prevEngineHours = $prevEngineHours;

        return $this;
    }

    /**
     * Get prevEngineHours.
     *
     * @return int|null
     */
    public function getPrevEngineHours()
    {
        return $this->prevEngineHours;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return VehicleEngineHours
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
     * @param User $createdBy
     * @return self
     */
    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getDiffValue()
    {
        if ($this->getPrevEngineHours()) {
            return $this->getEngineHours() - $this->getPrevEngineHours();
        } else {
            return $this->getEngineHours();
        }
    }
}
