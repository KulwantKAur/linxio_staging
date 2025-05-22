<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * VehicleGroup
 */
#[ORM\Table(name: 'vehicle_group')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\VehicleGroupRepository')]
class VehicleGroup extends BaseEntity
{
    public const STATUS_DELETED = BaseEntity::STATUS_DELETED_NUM;
    public const STATUS_ACTIVE = BaseEntity::STATUS_ACTIVE_NUM;

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'status',
        'clientId',
        'team',
        'vehiclesCount',
        'color'
    ];

    public const FULL_DISPLAY_VALUES = [
        'name',
        'status',
        'vehicles',
        'clientId',
        'client',
        'vehiclesCount',
        'color'
    ];

    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->vehicles = new ArrayCollection();
        $this->color = $fields['color'] ?? null;
        $this->userGroups = new ArrayCollection();
        $this->assets = new ArrayCollection();
    }

    public function toArray(array $include = []): array
    {
        $data = [
            'id' => $this->id
        ];
        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }

        if (in_array('name', $include, true)) {
            $data['name'] = $this->getName();
        }

        if (in_array('status', $include, true)) {
            $data['status'] = $this->getStatus();
        }

        if (in_array('vehicles', $include, true)) {
            $data['vehicles'] = $this->getVehicles();
        }

        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient() ? $this->getClient()->toArray() : null;
        }

        if (in_array('clientId', $include, true)) {
            $data['clientId'] = $this->getClient() ? $this->getClient()->getId() : null;
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam() ? $this->getTeam()->toArray() : null;
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
    private $status = self::STATUS_ACTIVE;

    /**
     * Many Groups have Many Vehicles.
     */
    #[ORM\JoinTable(name: 'vehicles_groups')]
    #[ORM\ManyToMany(targetEntity: 'Vehicle', inversedBy: 'groups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $vehicles;

    /**
     * Many Groups have Many Vehicles.
     */
    #[ORM\JoinTable(name: 'assets_groups')]
    #[ORM\ManyToMany(targetEntity: 'Asset', inversedBy: 'groups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $assets;

    /**
     * @var string
     */
    #[ORM\Column(name: 'color', type: 'string', length: 50, nullable: true)]
    private $color;

    /**
     * Many Vehicles have Many Groups.
     */
    #[ORM\ManyToMany(targetEntity: 'UserGroup', mappedBy: 'vehicleGroups', fetch: 'EXTRA_LAZY')]
    private $userGroups;

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
     * Set name
     *
     * @param string $name
     *
     * @return VehicleGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
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
     * @return VehicleGroup
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
     * Set status
     *
     * @param integer $status
     *
     * @return VehicleGroup
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
     * Get client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->getTeam()->getClient();
    }

    /**
     * @return array
     */
    public function getVehicles()
    {
        return array_map(
            function ($vehicle) {
                return $vehicle->toArray(Vehicle::DISPLAYED_VALUES);
            },
            $this->vehicles->toArray()
        );
    }

    /**
     * @return Collection
     */
    public function getVehiclesEntities(): Collection
    {
        return $this->vehicles;
    }

    /**
     * @return ArrayCollection
     */
    public function getVehicleEntities()
    {
        return $this->vehicles;
    }

    public function getVehicleIds(): array
    {
        return $this->vehicles->map(
            function (Vehicle $vehicle) {
                return $vehicle->getId();
            }
        )->toArray();
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
     *
     */
    public function removeAllVehicles()
    {
        $this->vehicles->clear();
    }

    /**
     * @return int
     */
    public function getVehiclesCount(): int
    {
        return count($this->getVehicles());
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

    /**
     * @return array
     */
    public function getAssets()
    {
        return array_map(
            function ($asset) {
                return $asset->toArray(Asset::DEFAULT_DISPLAY_VALUES);
            },
            $this->assets->toArray()
        );
    }

    public function getAssetEntities(): ArrayCollection
    {
        return $this->assets;
    }

    public function getAssetIds(): array
    {
        return $this->assets->map(
            function (Asset $asset) {
                return $asset->getId();
            }
        )->toArray();
    }

    public function addAsset(Asset $asset)
    {
        if (!$this->assets->contains($asset)) {
            $this->assets->add($asset);
        }
    }

    /**
     * @param Asset $asset
     */
    public function removeAsset(Asset $asset)
    {
        $this->assets->removeElement($asset);
    }

    public function removeAllAssets()
    {
        $this->assets->clear();
    }

    public function getAssetsCount(): int
    {
        return $this->assets->count();
    }
}
