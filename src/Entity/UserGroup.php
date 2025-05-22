<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserGroup
 */
#[ORM\Table(name: 'user_group')]
#[ORM\Entity(repositoryClass: 'App\Repository\UserGroupRepository')]
class UserGroup extends BaseEntity
{
    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DELETED
    ];

    public const LIST_STATUSES = [
        self::STATUS_ACTIVE
    ];

    public const SCOPE_VEHICLE = 'vehicle';
    public const SCOPE_DEPOT = 'depot';
    public const SCOPE_GROUP = 'group';
    public const SCOPE_AREA = 'area';
    public const SCOPE_AREA_GROUP = 'areaGroup';
    public const SCOPE_ALL = 'all';

    public const DEFAULT_DISPLAY_VALUES = [
        'name',
        'status',
        'clientId',
        'team',
        'usersCount',
        'vehiclesCount',
        'vehicleGroupsCount',
        'depotsCount',
        'areasCount',
        'areaGroupsCount',
        'permissions',
        'scope',
        'scopeValues',
        'areaScope',
        'areaScopeValues'
    ];

    public const FULL_DISPLAY_VALUES = [
        'name',
        'status',
        'clientId',
        'client',
        'usersCount',
        'vehiclesCount',
        'vehicleGroupsCount',
        'depotsCount',
        'areasCount',
        'areaGroupsCount',
        'permissions',
        'users',
        'scope',
        'scopeValues',
        'areaScope',
        'areaScopeValues'
    ];

    public function __construct(array $fields)
    {
        $this->name = $fields['name'];
        $this->users = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->vehicleGroups = new ArrayCollection();
        $this->depots = new ArrayCollection();
        $this->scope = $fields['scope'] ?? self::SCOPE_ALL;
        $this->areaScope = $fields['areaScope'] ?? self::SCOPE_ALL;
        $this->areas = new ArrayCollection();
        $this->areaGroups = new ArrayCollection();
        $this->permissions = $fields['permissions'] ?? new ArrayCollection();
        $this->team = $fields['team'] ?? null;
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

        if (in_array('users', $include, true)) {
            $data['users'] = $this->getUsersArray();
        }

        if (in_array('vehicles', $include, true)) {
            $data['vehicles'] = $this->getVehiclesArray();
        }

        if (in_array('vehiclesCount', $include, true)) {
            $data['vehiclesCount'] = $this->getVehiclesCount();
        }

        if (in_array('vehicleGroupsCount', $include, true)) {
            $data['vehicleGroupsCount'] = $this->getVehicleGroupsCount();
        }

        if (in_array('depotsCount', $include, true)) {
            $data['depotsCount'] = $this->getDepotsCount();
        }

        if (in_array('client', $include, true)) {
            $data['client'] = $this->getClient()->toArray(Client::SIMPLE_DISPLAY_VALUES);
        }

        if (in_array('clientId', $include, true)) {
            $data['clientId'] = $this->getClient()->getId();
        }

        if (in_array('team', $include, true)) {
            $data['team'] = $this->getTeam() ? $this->getTeam()->toArray() : null;
        }

        if (in_array('usersCount', $include, true)) {
            $data['usersCount'] = $this->getUsersCount();
        }

        if (in_array('scope', $include, true)) {
            $data['scope'] = $this->getScope();
        }

        if (in_array('scopeValues', $include, true)) {
            $data['scopeValues'] = $this->getScopeValues();
        }

        if (in_array('permissions', $include, true)) {
            $data['permissions'] = $this->getPermissionsArray();
        }

        if (in_array('areas', $include, true)) {
            $data['areas'] = $this->getAreasArray();
        }

        if (in_array('areasCount', $include, true)) {
            $data['areasCount'] = $this->getAreasCount();
        }

        if (in_array('areaGroupsCount', $include, true)) {
            $data['areaGroupsCount'] = $this->getAreaGroupsCount();
        }
        if (in_array('areaScope', $include, true)) {
            $data['areaScope'] = $this->getAreaScope();
        }

        if (in_array('areaScopeValues', $include, true)) {
            $data['areaScopeValues'] = $this->getAreaScopeValues();
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var Team
     */
    #[ORM\ManyToOne(targetEntity: 'Team')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id')]
    private $team;

    /**
     * @var string
     */
    #[ORM\Column(name: 'status', type: 'string', nullable: true)]
    private $status = self::STATUS_ACTIVE;

    /**
     * Many Groups have Many Users.
     */
    #[ORM\JoinTable(name: 'users_groups')]
    #[ORM\ManyToMany(targetEntity: 'User', inversedBy: 'groups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $users;

    #[ORM\JoinTable(name: 'users_vehicles')]
    #[ORM\ManyToMany(targetEntity: 'Vehicle', inversedBy: 'userGroups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $vehicles;

    #[ORM\JoinTable(name: 'users_vehicles_groups')]
    #[ORM\ManyToMany(targetEntity: 'VehicleGroup', inversedBy: 'userGroups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $vehicleGroups;

    #[ORM\JoinTable(name: 'users_vehicles_depots')]
    #[ORM\ManyToMany(targetEntity: 'Depot', inversedBy: 'userGroups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $depots;

    /**
     * @var string
     */
    #[ORM\Column(name: 'scope', type: 'string', nullable: false, options: ['default' => 'all'])]
    private $scope;

    /**
     * @var string
     */
    #[ORM\Column(name: 'area_scope', type: 'string', nullable: false, options: ['default' => 'all'])]
    private $areaScope;

    #[ORM\JoinTable(name: 'users_areas')]
    #[ORM\ManyToMany(targetEntity: 'Area', inversedBy: 'userGroups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $areas;

    #[ORM\JoinTable(name: 'users_area_groups')]
    #[ORM\ManyToMany(targetEntity: 'AreaGroup', inversedBy: 'userGroups', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $areaGroups;

    #[ORM\JoinTable(name: 'users_permissions')]
    #[ORM\ManyToMany(targetEntity: 'Permission', inversedBy: 'userGroups', cascade: ['persist'], fetch: 'EXTRA_LAZY', indexBy: 'id')]
    private $permissions;

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
     * @return UserGroup
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
     * @return UserGroup
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
     * @param string $status
     *
     * @return UserGroup
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
    public function getUsersArray()
    {
        return array_map(
            function ($user) {
                return $user->toArray(User::SIMPLE_VALUES);
            },
            $this->users->toArray()
        );
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function getUsersForEmail()
    {
        return $this->users->filter(
            fn(User $user) => in_array($user->getStatus(), [User::STATUS_ACTIVE, User::STATUS_NEW])
        );
    }

    /**
     * @return array
     */
    public function getUsersEntityArray()
    {
        return $this->users->toArray();
    }

    public function getUserIds(): array
    {
        return $this->users->map(
            function (User $user) {
                return $user->getId();
            }
        )->toArray();
    }

    /**
     * @param User $user
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     *
     */
    public function removeAllUsers()
    {
        $this->users->clear();
    }

    /**
     * @return int
     */
    public function getUsersCount(): int
    {
        return $this->getUsers()->count();
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
     * @return array
     */
    public function getVehicleIds(): array
    {
        return $this->vehicles->map(
            function (Vehicle $vehicle) {
                return $vehicle->getId();
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getVehiclesArray()
    {
        return array_map(
            function ($vehicle) {
                return $vehicle->toArray(Vehicle::LIST_DISPLAY_VALUES);
            },
            $this->vehicles->toArray()
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }

    /**
     * @return int
     */
    public function getVehiclesCount(): int
    {
        return $this->getVehicles()->count();
    }

    /**
     * @param VehicleGroup $vehicleGroup
     */
    public function addVehicleGroup(VehicleGroup $vehicleGroup)
    {
        if (!$this->vehicleGroups->contains($vehicleGroup)) {
            $this->vehicleGroups->add($vehicleGroup);
        }
    }

    /**
     * @param VehicleGroup $vehicleGroup
     */
    public function removeVehicleGroup(VehicleGroup $vehicleGroup)
    {
        $this->vehicleGroups->removeElement($vehicleGroup);
    }

    /**
     * @return array
     */
    public function getVehicleGroupIds(): array
    {
        return $this->vehicleGroups->map(
            function (VehicleGroup $vehicleGroup) {
                return $vehicleGroup->getId();
            }
        )->toArray();
    }

    /**
     * @return ArrayCollection|VehicleGroup[]
     */
    public function getVehicleGroups()
    {
        return $this->vehicleGroups;
    }

    /**
     *
     */
    public function removeAllVehicleGroups()
    {
        $this->vehicleGroups->clear();
    }

    /**
     * @return int
     */
    public function getVehicleGroupsCount(): int
    {
        return $this->getVehicleGroups()->count();
    }

    /**
     * @param Depot $depot
     */
    public function addDepot(Depot $depot)
    {
        if (!$this->depots->contains($depot)) {
            $this->depots->add($depot);
        }
    }

    /**
     * @param Depot $depot
     */
    public function removeDepot(Depot $depot)
    {
        $this->depots->removeElement($depot);
    }

    /**
     * @return ArrayCollection|Depot[]
     */
    public function getDepots()
    {
        return $this->depots;
    }

    /**
     * @return int
     */
    public function getDepotsCount(): int
    {
        return $this->getDepots()->count();
    }

    /**
     *
     */
    public function removeAllDepots()
    {
        $this->depots->clear();
    }

    /**
     * @return array
     */
    public function getDepotIds(): array
    {
        return $this->depots->map(
            function (Depot $depot) {
                return $depot->getId();
            }
        )->toArray();
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param $scope
     * @return mixed
     */
    public function setScope($scope)
    {
        return $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getAreaScope()
    {
        return $this->areaScope;
    }

    /**
     * @param $scope
     * @return mixed
     */
    public function setAreaScope($scope)
    {
        return $this->areaScope = $scope;
    }

    /**
     * @return array
     */
    public function getScopeValues()
    {
        $values = [];
        switch ($this->getScope()) {
            case UserGroup::SCOPE_VEHICLE:
                $values = $this->getVehicleIds();
                break;
            case UserGroup::SCOPE_DEPOT:
                $values = $this->getDepotIds();
                break;
            case UserGroup::SCOPE_GROUP:
                $values = $this->getVehicleGroupIds();
                break;
        }

        return $values;
    }

    public function getAreaScopeValues()
    {
        $values = [];
        switch ($this->getAreaScope()) {
            case UserGroup::SCOPE_AREA:
                $values = $this->getAreaIds();
                break;
            case UserGroup::SCOPE_AREA_GROUP:
                $values = $this->getAreaGroupIds();
                break;
        }

        return $values;
    }

    /**
     * @return Collection
     */
    public function getVehiclesByTeam()
    {
        return $this->getTeam()->getVehicles();
    }

    public function getAreasByTeam()
    {
        return $this->getTeam()->getAreas();
    }

    /**
     * @return Collection
     */
    public function getVehiclesByDepots()
    {
        return $this->getDepots()->map(
            function (Depot $depot) {
                return $depot->getVehicles();
            }
        );
    }

    /**
     * @return Collection
     */
    public function getVehiclesByVehicleGroups()
    {
        return $this->getVehicleGroups()->map(
            function (VehicleGroup $vehicleGroup) {
                return $vehicleGroup->getVehiclesEntities();
            }
        );
    }

    public function getAreasByAreaGroups()
    {
        return $this->getAreaGroups()->map(
            function (AreaGroup $areaGroup) {
                return $areaGroup->getAreas();
            }
        );
    }


    /**
     * @return Collection
     */
    public function getVehiclesByScope()
    {
        switch ($this->getScope()) {
            case UserGroup::SCOPE_VEHICLE:
                return $this->getVehicles();
            case UserGroup::SCOPE_DEPOT:
                $vehicles = new ArrayCollection();
                $vehiclesUniqueIds = [];

                $this->getVehiclesByDepots()->map(
                    function (Collection $collection) use (&$vehicles) {
                        $vehicles = new ArrayCollection(array_merge($vehicles->toArray(), $collection->toArray()));

                        return $collection;
                    }
                );

                return $vehicles->filter(
                    function (Vehicle $vehicle) use (&$vehiclesUniqueIds) {
                        if (!in_array($vehicle->getId(), $vehiclesUniqueIds)) {
                            $vehiclesUniqueIds[] = $vehicle->getId();

                            return true;
                        }

                        return false;
                    }
                );
            case UserGroup::SCOPE_GROUP:
                $vehicles = new ArrayCollection();
                $vehiclesUniqueIds = [];

                $this->getVehiclesByVehicleGroups()->map(
                    function (Collection $collection) use (&$vehicles) {
                        $vehicles = new ArrayCollection(array_merge($vehicles->toArray(), $collection->toArray()));

                        return $collection;
                    }
                );

                return $vehicles->filter(
                    function (Vehicle $vehicle) use (&$vehiclesUniqueIds) {
                        if (!in_array($vehicle->getId(), $vehiclesUniqueIds)) {
                            $vehiclesUniqueIds[] = $vehicle->getId();

                            return true;
                        }

                        return false;
                    }
                );
            default:
                return $this->getVehiclesByTeam();
        }
    }

    /**
     * @param string|null $scope
     * @return array
     */
    public function getVehiclesIdsByScope(string $scope = null): array
    {
        $scope = $scope ?? $this->getScope();

        switch ($scope) {
            case UserGroup::SCOPE_VEHICLE:
                return $this->getVehicles()->map(
                    function (Vehicle $vehicle) {
                        return $vehicle->getId();
                    }
                )->toArray();
            case UserGroup::SCOPE_DEPOT:
                $vehiclesIds = [];

                $this->getVehiclesByDepots()->map(
                    function (Collection $collection) use (&$vehiclesIds) {
                        return $collection->map(function (Vehicle $vehicle) use (&$vehiclesIds) {
                            $vehiclesIds[] = $vehicle->getId();

                            return $vehicle->getId();
                        });
                    }
                );

                return array_unique($vehiclesIds);
            case UserGroup::SCOPE_GROUP:
                $vehiclesIds = [];

                $this->getVehiclesByVehicleGroups()->map(
                    function (Collection $collection) use (&$vehiclesIds) {
                        return $collection->map(function (Vehicle $vehicle) use (&$vehiclesIds) {
                            $vehiclesIds[] = $vehicle->getId();

                            return $vehicle->getId();
                        });
                    }
                );

                return array_unique($vehiclesIds);
            default:
                return $this->getVehiclesByTeam()->map(
                    function (Vehicle $vehicle) {
                        return $vehicle->getId();
                    }
                )->toArray();
        }
    }

    public function getAreasIdsByScope(string $scope = null): array
    {
        $scope = $scope ?? $this->getScope();

        switch ($scope) {
            case UserGroup::SCOPE_AREA:
                return $this->getAreas()->map(
                    function (Area $area) {
                        return $area->getId();
                    }
                )->toArray();
            case UserGroup::SCOPE_AREA_GROUP:
                $areasIds = [];

                $this->getAreasByAreaGroups()->map(
                    function (Collection $collection) use (&$areasIds) {
                        return $collection->map(function (Area $area) use (&$areasIds) {
                            $areasIds[] = $area->getId();

                            return $area->getId();
                        });
                    }
                );

                return array_unique($areasIds);
            default:
                return $this->getAreasByTeam()->map(
                    function (Area $area) {
                        return $area->getId();
                    }
                )->toArray();
        }
    }

    /**
     * @param Area $area
     */
    public function addArea(Area $area)
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
        }
    }

    /**
     * @param Area $area
     */
    public function removeArea(Area $area)
    {
        $this->areas->removeElement($area);
    }

    /**
     *
     */
    public function removeAllAreas()
    {
        $this->areas->clear();
    }

    /**
     * @return array
     */
    public function getAreaIds(): array
    {
        return $this->areas->map(
            function (Area $area) {
                return $area->getId();
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getAreasArray()
    {
        return array_map(
            function ($area) {
                return $area->toArray();
            },
            $this->areas->toArray()
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * @return int
     */
    public function getAreasCount(): int
    {
        return $this->getAreas()->count();
    }

    /**
     * @param AreaGroup $areaGroup
     */
    public function addAreaGroup(AreaGroup $areaGroup)
    {
        if (!$this->areaGroups->contains($areaGroup)) {
            $this->areaGroups->add($areaGroup);
        }
    }

    /**
     * @param AreaGroup $areaGroup
     */
    public function removeAreaGroup(AreaGroup $areaGroup)
    {
        $this->areaGroups->removeElement($areaGroup);
    }

    /**
     * @return array
     */
    public function getAreaGroupIds(): array
    {
        return $this->areaGroups->map(
            function (AreaGroup $areaGroup) {
                return $areaGroup->getId();
            }
        )->toArray();
    }

    /**
     * @return ArrayCollection|AreaGroup[]
     */
    public function getAreaGroups()
    {
        return $this->areaGroups;
    }

    /**
     *
     */
    public function removeAllAreaGroups()
    {
        $this->areaGroups->clear();
    }

    /**
     * @return int
     */
    public function getAreaGroupsCount(): int
    {
        return $this->getAreaGroups()->count();
    }

    /**
     * @param Permission $permission
     */
    public function addPermissions(Permission $permission)
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }
    }

    /**
     * @param Permission $permission
     */
    public function removePermissions(Permission $permission)
    {
        $this->permissions->removeElement($permission);
    }

    /**
     *
     */
    public function removeAllPermissions()
    {
        $this->permissions->clear();
    }

    /**
     * @return array
     */
    public function getPermissionsIds(): array
    {
        return $this->getPermissions()->map(
            function (Permission $permission) {
                return $permission->getId();
            }
        )->toArray();
    }

    /**
     * @return array
     */
    public function getPermissionsArray()
    {
        return array_map(
            function ($permission) {
                return $permission->toArray();
            },
            $this->getPermissions()->toArray()
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getPermissions()
    {
        return new ArrayCollection($this->permissions->getValues());
    }

    public function setPermissions($permissions)
    {
        foreach ($this->permissions as $id => $product) {
            if (!isset($permissions[$id])) {
                //remove from old because it doesn't exist in new
                $this->permissions->remove($id);
            } else {
                //the product already exists do not overwrite
                unset($permissions[$id]);
            }
        }

        //add products that exist in new but not in old
        foreach ($permissions as $id => $product) {
            $this->permissions[$id] = $product;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPermissionsCount(): int
    {
        return $this->getPermissions()->count();
    }

}
