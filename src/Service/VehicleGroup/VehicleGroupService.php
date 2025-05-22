<?php

namespace App\Service\VehicleGroup;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Events\VehicleGroup\VehicleAddedToVehicleGroupEvent;
use App\Events\VehicleGroup\VehicleGroupCreatedEvent;
use App\Events\VehicleGroup\VehicleGroupDeletedEvent;
use App\Events\VehicleGroup\VehicleGroupUpdatedEvent;
use App\Events\VehicleGroup\VehicleRemovedFromVehicleGroupEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VehicleGroupService extends BaseService
{
    use VehicleGroupServiceFieldsTrait;

    private $translator;
    private $em;
    private $eventDispatcher;
    private $vehicleGroupFinder;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'vehiclesCount' => 'vehiclesCount',
        'status' => 'status',
        'teamId' => 'team.id'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'vehiclesCountFilter' => 'vehiclesCount'
    ];

    /**
     * VehicleGroupService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param TransformedFinder $vehicleGroupFinder
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        TransformedFinder $vehicleGroupFinder
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->vehicleGroupFinder = new ElasticSearch($vehicleGroupFinder);;
    }

    public function create(array $data, User $currentUser): VehicleGroup
    {
        $this->validateVehicleFields($data, $currentUser, self::ACTION_CREATE);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            $vehicleGroup = new VehicleGroup($data);

            if ($currentUser->isInClientTeam()) {
                $vehicleGroup->setTeam($currentUser->getTeam());
            } else {
                $team = $this->em->getRepository(Team::class)->find($data['teamId']);
                $vehicleGroup->setTeam($team);
            }

            $this->em->persist($vehicleGroup);

            if ($data['vehicleIds'] ?? null) {
                $vehicleGroup = $this->addVehicleToVehicleGroup($data['vehicleIds'], $vehicleGroup, $currentUser);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(
                new VehicleGroupCreatedEvent($vehicleGroup, $currentUser),
                VehicleGroupCreatedEvent::NAME
            );

            return $vehicleGroup;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function edit(array $data, User $currentUser, VehicleGroup $vehicleGroup): VehicleGroup
    {
        $this->validateVehicleFields($data, $currentUser, self::ACTION_EDIT);
        try {
            if ($currentUser->isInAdminTeam()) {
                $team = $this->em->getRepository(Team::class)->find($data['teamId']);
                $vehicleGroup->setTeam($team);
            }
            $vehicleGroup->setName($data['name']);
            if (array_key_exists('color', $data)) {
                $vehicleGroup->setColor($data['color']);
            }

            if (isset($data['vehicleIds'])) {
                $vehicleGroup = $this->addVehicleToVehicleGroup($data['vehicleIds'], $vehicleGroup, $currentUser);
            }

            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new VehicleGroupUpdatedEvent($vehicleGroup, $currentUser),
                VehicleGroupUpdatedEvent::NAME
            );

            return $vehicleGroup;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function vehicleGroupsList(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        if ($user->needToCheckUserGroup()) {
            $params['id'] = $this->em->getRepository(UserGroup::class)->getUserVehicleGroupsIdFromUserGroup($user) ?? [];
        }
        $params = VehicleGroup::handleStatusParams($params);

        $fields = $this->prepareElasticFields($params);

        return $this->vehicleGroupFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    public function getById(int $id, User $currentUser)
    {
        return $this->em->getRepository(VehicleGroup::class)->getVehicleGroupById($currentUser, $id);
    }

    public static function checkVehicleGroupAccess(VehicleGroup $vehicleGroup, User $user)
    {
        if ($user->isInClientTeam() && $vehicleGroup->getTeam()->getId() === $user->getTeamId()) {
            return true;
        } elseif ($user->isInAdminTeam() && ($user->hasTeamPermission(
                    $vehicleGroup->getTeam()->getId()
                ) || $user->isAllTeamsPermissions())) {
            return true;
        }
        return false;
    }

    public function addVehicleToVehicleGroup(array $vehicleIds, VehicleGroup $vehicleGroup, User $currentUser)
    {
        $vehicleGroupVehiclesIds = $vehicleGroup->getVehicleIds();
        $idsToDelete = array_diff($vehicleGroupVehiclesIds, $vehicleIds);

        foreach ($idsToDelete as $idToDelete) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($idToDelete);

            if (ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $vehicle->removeFromGroup($vehicleGroup);
                $vehicle->setUpdatedAt(new \DateTime());
                $vehicleGroup->removeVehicle($vehicle);
                $this->eventDispatcher->dispatch(
                    new VehicleRemovedFromVehicleGroupEvent($vehicle, $vehicleGroup, $currentUser),
                    VehicleRemovedFromVehicleGroupEvent::NAME
                );
            }
        }

        foreach ($vehicleIds as $vehicleId) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);

            if (
                ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser) &&
                !in_array($vehicleId, $vehicleGroupVehiclesIds)
            ) {
                $vehicle->addToGroup($vehicleGroup);
                $vehicle->setUpdatedAt(new \DateTime());
                $vehicleGroup->addVehicle($vehicle);
                $this->eventDispatcher->dispatch(
                    new VehicleAddedToVehicleGroupEvent($vehicle, $vehicleGroup, $currentUser),
                    VehicleAddedToVehicleGroupEvent::NAME
                );
            }
        }

        return $vehicleGroup;
    }

    /**
     * @param VehicleGroup $vehicleGroup
     * @param User $currentUser
     * @throws \Exception
     */
    public function remove(VehicleGroup $vehicleGroup, User $currentUser)
    {
        try {
            $vehicleGroup->setStatus(VehicleGroup::STATUS_DELETED);
            $vehicleGroup->removeAllVehicles();

            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new VehicleGroupDeletedEvent($vehicleGroup, $currentUser),
                VehicleGroupDeletedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function restore(VehicleGroup $vehicleGroup, User $currentUser)
    {
        try {
            $vehicleGroup->setStatus(VehicleGroup::STATUS_ACTIVE);

            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new VehicleGroupUpdatedEvent($vehicleGroup, $currentUser),
                VehicleGroupUpdatedEvent::NAME
            );

            return $vehicleGroup;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function archive(VehicleGroup $vehicleGroup, User $currentUser)
    {
        try {
            $vehicleGroup->setStatus(VehicleGroup::STATUS_ARCHIVE);

            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new VehicleGroupUpdatedEvent($vehicleGroup, $currentUser),
                VehicleGroupUpdatedEvent::NAME
            );

            return $vehicleGroup;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
