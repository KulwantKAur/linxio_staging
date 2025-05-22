<?php

namespace App\Entity\FuelType;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Util\AttributesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FuelIgnoreList
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="Fuel ignore with this name already exists."
 * )
 */
#[ORM\Table(name: 'fuel_ignore_list')]
#[ORM\Entity(repositoryClass: 'App\Repository\FuelType\FuelIgnoreListRepository')]
class FuelIgnoreList extends BaseEntity
{
    use AttributesTrait;

    public const DISPLAYED_VALUES = [
        'id',
        'name'
    ];

    /**
     * FuelIgnoreList constructor.
     * @param array $fields
     * @throws \Exception
     */
    public function __construct(array $fields = [])
    {
        $this->setName($fields['name'] ?? null);
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
     * @return FuelIgnoreList
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FuelIgnoreList
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
     * @return FuelIgnoreList
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
     * @return FuelIgnoreList
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
     * @return FuelIgnoreList
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
