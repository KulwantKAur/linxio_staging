<?php

namespace App\Entity\FuelType;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Util\AttributesTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FuelMapping
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="Fuel mapping with this name already exists."
 * )
 * @UniqueEntity(
 *     fields={"name", "fuelType"},
 *     errorPath="fuelType",
 *     message="Fuel mapping  with this name and fuel type already exist."
 * )
 */
#[ORM\Table(name: 'fuel_mapping')]
#[ORM\Entity(repositoryClass: 'App\Repository\FuelType\FuelMappingRepository')]
class FuelMapping extends BaseEntity
{
    use AttributesTrait;

    public const DISPLAYED_VALUES = [
        'id',
        'name',
        'fuelType',
        'status',
    ];

    public const STATUS_ACTIVE = 'active';

    /**
     * FuelMapping constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields = [])
    {
        $this->setName($fields['name'] ?? null);
        $this->setFuelType($fields['fuelType'] ?? null);
        $this->setStatus($fields['status'] ?? self::STATUS_ACTIVE);
        $this->setCreatedBy($fields['createdBy'] ?? null);
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * @param array $include
     * @return array
     */
    public function toArray(array $include = []): array
    {
        $data = [];

        if (empty($include)) {
            $include = self::DISPLAYED_VALUES;
        }

        if (in_array('id', $include, true)) {
            $data['id'] = $this->getId();
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('fuelType', $include, true)) {
            $data['fuelType'] = $this->getFuelTypeArray();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
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
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 255
     * )
     * @Assert\NotBlank
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    private $name;

    /**
     * @var FuelType
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\FuelType\FuelType')]
    #[ORM\JoinColumn(name: 'fuel_type_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $fuelType;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', length: 100)]
    private $status = self::STATUS_ACTIVE;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private $createdAt;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
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
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

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
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param FuelType|null $fuelType
     * @return $this
     */
    public function setFuelType(?FuelType $fuelType)
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    /**
     * @return FuelType
     */
    public function getFuelType() :FuelType
    {
        return $this->fuelType;
    }

    /**
     * @return array|null
     */
    public function getFuelTypeArray()
    {
        return $this->fuelType ? $this->fuelType->toArray(['id', 'name']) : null;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return FuelMapping
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FuelMapping
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
     * @param User|null $createdBy
     *
     * @return FuelMapping
     */
    public function setCreatedBy(?User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return FuelMapping
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
     * @return FuelMapping
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
     * @throws \Exception
     */
    public function getUpdatedByData()
    {
        return $this->getUpdatedBy()
            ? $this->getUpdatedBy()->toArray(User::CREATED_BY_FIELDS)
            : null;
    }
}
