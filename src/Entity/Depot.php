<?php

namespace App\Entity;

use App\Util\AttributesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Depot
 */
#[ORM\Table(name: 'vehicle_depot')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\DepotRepository')]
class Depot extends BaseEntity
{
    use AttributesTrait;

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'team',
        'status',
        'vehicles',
        'vehiclesCount',
        'createdAt',
        'color'
    ];

    public const SIMPLE_DISPLAY_VALUES = [
        'name',
        'status',
        'color'
    ];

    public const STATUS_DELETED = BaseEntity::STATUS_DELETED_NUM;
    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE_NUM;

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];
    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->team = $fields['team'] ?? null;
        $this->status = $fields['status'] ?? self::STATUS_ACTIVE;
        $this->createdAt = $fields['createdAt'] ?? new \DateTime();
        $this->createdBy = $fields['createdBy'] ?? null;
        $this->updatedAt = $fields['updatedAt'] ?? null;
        $this->updatedBy = $fields['updatedBy'] ?? null;
        $this->color = $fields['color'] ?? null;
        $this->vehicles = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
    }

    public function toArray(array $include = []): array
    {
        $data = [];
        $data['id'] = $this->getId();

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->name;
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam()->toArray();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('createdAt', $include, true)) {
            $data['createdAt'] = $this->formatDate($this->createdAt);
        }

        if (in_array('vehicles', $include, true)) {
            $data['vehicles'] = $this->getVehiclesArray();
        }

        if (in_array('vehiclesCount', $include, true)) {
            $data['vehiclesCount'] = $this->getVehiclesCount();
        }

        if (in_array('color', $include, true)) {
            $data['color'] = $this->getColor();
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
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var integer
     */
    #[ORM\Column(name: 'status', type: 'integer', nullable: true)]
    private $status;

    /**
     * @var int
     */
    #[ORM\OneToMany(targetEntity: 'Vehicle', mappedBy: 'depot', fetch: 'EXTRA_LAZY')]
    private $vehicles;

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
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private $updatedBy;

    /**
     * @var string
     */
    #[ORM\Column(name: 'color', type: 'string', length: 50, nullable: true)]
    private $color;

    /**
     * Many Vehicles have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'depots', fetch: 'EXTRA_LAZY')]
    private $userGroups;

    /**
     * Many Assets have Many Groups.
     */
    #[ORM\OneToMany(targetEntity: 'Asset', mappedBy: 'depot', fetch: 'EXTRA_LAZY')]
    private $assets;

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
     * @return Depot
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
     * Set team
     *
     * @param Team $team
     *
     * @return Depot
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
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Depot
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Depot
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
     * Set createdBy.
     *
     * @param int $createdBy
     *
     * @return Depot
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return Depot
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedBy.
     *
     * @param \DateTime|null $updatedBy
     *
     * @return Depot
     */
    public function setUpdatedBy($updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return ArrayCollection
     */
    public function getVehicles()
    {
        return $this->vehicles ?? new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getVehiclesArray(): array
    {
        return $this->getVehicles()->map(
            function (Vehicle $vehicle) {
                return $vehicle->toArray(['model']);
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getVehicleIds(): array
    {
        return $this->getVehicles()->map(
            function (Vehicle $vehicle) {
                return $vehicle->getId();
            }
        )->toArray();
    }

    /**
     * @return int
     */
    public function getVehiclesCount(): int
    {
        return $this->getVehicles()->count();
    }

    /**
     * @param Vehicle $vehicle
     */
    public function addVehicle(Vehicle $vehicle)
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles->add($vehicle);
        }
    }

    /**
     * @param Vehicle $vehicle
     */
    public function removeVehicle(Vehicle $vehicle)
    {
        $this->vehicles->removeElement($vehicle);
    }


    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string|null $color
     * @return $this
     */
    public function setColor(?string $color = null)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @param UserGroup $userGroup
     */
    public function addToUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->add($userGroup);
    }

    /**
     * @param UserGroup $userGroup
     */
    public function removeFromUserGroup(UserGroup $userGroup)
    {
        $this->userGroups->removeElement($userGroup);
    }

    /**
     * @return Collection|UserGroup[]
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsersFromUserGroups(): Collection
    {
        $users = new ArrayCollection();
        $usersIds = [];
        $this->getUserGroups()->map(
            function (UserGroup $userGroup) use (&$users) {
                $users = new ArrayCollection(array_merge($users->toArray(), $userGroup->getUsers()->toArray()));

                return $userGroup;
            }
        );

        return $users->filter(
            function (User $user) use (&$usersIds) {
                if (!in_array($user->getId(), $usersIds)) {
                    $usersIds[] = $user->getId();

                    return true;
                }

                return false;
            }
        );
    }

    public function getAssets(): ?ArrayCollection
    {
        return $this->assets;
    }
}