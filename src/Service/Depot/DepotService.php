<?php

namespace App\Service\Depot;

use App\Entity\Depot;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Events\Depot\DepotCreatedEvent;
use App\Events\Depot\DepotDeletedEvent;
use App\Events\Depot\DepotUpdatedEvent;
use App\Events\Depot\VehicleAddedToDepotEvent;
use App\Events\Depot\VehicleRemovedFromDepotEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DepotService extends BaseService
{
    use DepotServiceFieldsTrait;

    protected $translator;
    private $em;
    private $depotFinder;
    private $eventDispatcher;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'status' => 'status',
        'vehiclesCount' => 'vehiclesCount',
        'teamId' => 'team.id'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'vehiclesCountFilter' => 'vehiclesCount'
    ];

    /**
     * DepotService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TransformedFinder $depotFinder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $depotFinder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->depotFinder = new ElasticSearch($depotFinder);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return Depot
     * @throws ValidationException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function create(array $data, User $currentUser): Depot
    {
        $this->validateVehicleFields($data, $currentUser);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $depot = new Depot($data);
            if ($currentUser->isInClientTeam()) {
                $depot->setTeam($currentUser->getTeam());
            } else {
                $team = $this->em->getRepository(Team::class)->find($data['teamId']);
                $depot->setTeam($team);
            }
            $depot->setCreatedBy($currentUser);
            $depot->setCreatedAt(new \DateTime());

            $this->em->persist($depot);

            if ($data['vehicleIds'] ?? null) {
                $depot = $this->addVehicleToDepot($data['vehicleIds'], $depot, $currentUser);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            if ($depot ?? null) {
                $this->eventDispatcher->dispatch(new DepotCreatedEvent($depot), DepotCreatedEvent::NAME);
            }

            return $depot;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param array $vehicleIds
     * @param Depot $depot
     * @param User $currentUser
     * @return Depot
     */
    public function addVehicleToDepot(array $vehicleIds, Depot $depot, User $currentUser)
    {
        $depotVehiclesIds = $depot->getVehicleIds();
        $idsToDelete = array_diff($depotVehiclesIds, $vehicleIds);

        foreach ($idsToDelete as $idToDelete) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($idToDelete);

            if (ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser)) {
                $vehicle->setDepot(null);
                $depot->removeVehicle($vehicle);
                $this->eventDispatcher->dispatch(
                    new VehicleRemovedFromDepotEvent($vehicle, $depot, $currentUser),
                    VehicleRemovedFromDepotEvent::NAME
                );
            }
        }

        foreach ($vehicleIds as $vehicleId) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);

            if (
                ClientService::checkTeamAccess($vehicle->getTeam(), $currentUser) &&
                !in_array($vehicleId, $depotVehiclesIds)
            ) {
                $vehicle->setDepot($depot);
                $depot->addVehicle($vehicle);
                $this->eventDispatcher->dispatch(
                    new VehicleAddedToDepotEvent($vehicle, $depot, $currentUser),
                    VehicleAddedToDepotEvent::NAME
                );
            }
        }

        return $depot;
    }

    public function depotList(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        } elseif ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        $params = Depot::handleStatusParams($params);

        if ($user->hasDepotGroupScope()) {
            $params['id'] = $this->em->getRepository(UserGroup::class)->getUserDepotsIdFromUserGroup($user) ?? [];
        }

        $fields = $this->prepareElasticFields($params);

        return $this->depotFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    public function getById(int $id, User $user)
    {
        $depot = null;
        if ($user->isInAdminTeam()) {
            $depot = $this->em->getRepository(Depot::class)->find($id);
        } elseif ($user->isInClientTeam()) {
            $depot = $this->em->getRepository(Depot::class)->getVehicleDepotById($user, $id);
        }

        return $depot;
    }

    public function edit(array $data, User $currentUser, Depot $depot): Depot
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            if ($currentUser->isInAdminTeam()) {
                $team = $data['teamId'] ? $this->em->getRepository(Team::class)->find($data['teamId']) : null;
                if (ClientService::checkTeamAccess($team, $currentUser)) {
                    $depot->setTeam($team);
                }
            }

            $data['updatedAt'] = new \DateTime();
            $data['updatedBy'] = $currentUser;
            $depot->setAttributes($data);

            if (isset($data['vehicleIds'])) {
                $depot = $this->addVehicleToDepot($data['vehicleIds'], $depot, $currentUser);
            }
            $this->em->flush();

            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(new DepotUpdatedEvent($depot), DepotUpdatedEvent::NAME);

            return $depot;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function removeDepot(Depot $depot, array $data, User $currentUser)
    {
        try {
            $depot->setStatus(Depot::STATUS_DELETED);
            $depot->setUpdatedBy($currentUser);
            $depot->setUpdatedAt(new \DateTime());

            $batchSize = 20;
            $i = 1;
            $vehicles = $this->em->getRepository(Vehicle::class)->getVehiclesByDepotIterator($depot);

            if (isset($data['assignDepotId'])) {
                $assignDepot = $this->em->getRepository(Depot::class)->find($data['assignDepotId']);

                if ($assignDepot && $assignDepot->getId() !== $depot->getId()) {
                    foreach ($vehicles as $vehicle) {
                        $vehicle[0]->setDepot($assignDepot);
                        if (($i++ % $batchSize) === 0) {
                            $this->em->flush();
                            $this->em->clear();
                            $assignDepot = $this->em->merge($assignDepot);
                        }
                    }
                }
            } else {
                foreach ($vehicles as $vehicle) {
                    $vehicle[0]->setDepot(null);
                    if (($i++ % $batchSize) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }
                }
            }

            $this->em->flush();

            $this->eventDispatcher->dispatch(new DepotDeletedEvent($depot), DepotDeletedEvent::NAME);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function restore(Depot $depot, User $currentUser)
    {
        try {
            $depot->setStatus(Depot::STATUS_ACTIVE);
            $depot->setUpdatedBy($currentUser);
            $depot->setUpdatedAt(new \DateTime());

            $this->em->flush();

            $this->eventDispatcher->dispatch(new DepotUpdatedEvent($depot), DepotUpdatedEvent::NAME);

            return $depot;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function archive(Depot $depot, User $currentUser)
    {
        try {
            $depot->setStatus(Depot::STATUS_ARCHIVE);
            $depot->setUpdatedBy($currentUser);
            $depot->setUpdatedAt(new \DateTime());

            $this->em->flush();

            $this->eventDispatcher->dispatch(new DepotUpdatedEvent($depot), DepotUpdatedEvent::NAME);

            return $depot;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
