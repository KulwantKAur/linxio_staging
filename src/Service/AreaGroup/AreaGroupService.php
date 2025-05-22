<?php

namespace App\Service\AreaGroup;

use App\Entity\User;
use App\Entity\Area;
use App\Entity\AreaGroup;
use App\Events\AreaGroup\AreaGroupCreatedEvent;
use App\Events\AreaGroup\AreaGroupDeletedEvent;
use App\Events\AreaGroup\AreaGroupUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AreaGroupService extends BaseService
{
    use AreaGroupHelperTrait;

    private $translator;
    private $em;
    private $eventDispatcher;
    private $areaGroupFinder;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'name' => 'name',
        'status' => 'status',
        'color' => 'color',
        'teamId' => 'team.id'
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'areasCount' => 'areasCount'
    ];

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        TransformedFinder $areaGroupFinder
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->areaGroupFinder = new ElasticSearch($areaGroupFinder);
    }

    public function create(array $data, User $currentUser): AreaGroup
    {
        $this->validateAreaFields($data, $currentUser, self::ACTION_CREATE);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            $areaGroup = new AreaGroup($data);
            $areaGroup->setTeam($currentUser->getTeam());

            $areaGroup->setCreatedBy($currentUser);
            $this->em->persist($areaGroup);

            if ($data['areaIds'] ?? null) {
                $areaGroup = $this->addAreaToAreaGroup($data['areaIds'], $areaGroup, $currentUser);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(
                new AreaGroupCreatedEvent($areaGroup, $currentUser),
                AreaGroupCreatedEvent::NAME
            );

            return $areaGroup;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function edit(array $data, User $currentUser, AreaGroup $areaGroup): AreaGroup
    {
        if ($areaGroup->getType() === AreaGroup::TYPE_FUEL_STATION) {
            return $areaGroup;
        }

        $this->validateAreaFields($data, $currentUser, self::ACTION_EDIT);
        try {
            $areaGroup->setName($data['name']);
            if ($data['color']) {
                $areaGroup->setColor($data['color']);
            }

            if (isset($data['areaIds'])) {
                $areaGroup = $this->addAreaToAreaGroup($data['areaIds'], $areaGroup, $currentUser);
            }
            $areaGroup->setUpdatedBy($currentUser);
            $areaGroup->setUpdatedAt(new \DateTime());
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new AreaGroupUpdatedEvent($areaGroup, $currentUser),
                AreaGroupUpdatedEvent::NAME
            );
            $this->em->refresh($areaGroup);

            return $areaGroup;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function areaGroupsList(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = $user->getTeam()->getId();
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        $params = AreaGroup::handleStatusParams($params);
        $params = $this->handleUserGroupParams($params, $user);

        $fields = $this->prepareElasticFields($params);

        return $this->areaGroupFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param int $id
     * @param User $user
     * @return object|null
     */
    public function getById(int $id, User $user)
    {
        return $this->em->getRepository(AreaGroup::class)->getById($id, $user);
    }

    /**
     * @param array $areaIds
     * @param AreaGroup $areaGroup
     * @param User $currentUser
     * @return AreaGroup
     * @throws \Exception
     */
    public function addAreaToAreaGroup(array $areaIds, AreaGroup $areaGroup, User $currentUser)
    {
        $idsToDelete = array_diff($areaGroup->getAreaIds(), $areaIds);
        foreach ($idsToDelete as $idToDelete) {
            $area = $this->em->getRepository(Area::class)->find($idToDelete);
            if (ClientService::checkTeamAccess($area->getTeam(), $currentUser)) {
                $area->removeFromGroup($areaGroup);
                $area->setUpdatedAt(new \DateTime());
                $areaGroup->removeArea($area);
                $areaGroup->deletedAreas[] = $area;
            }
        }

        foreach ($areaIds as $areaId) {
            $area = $this->em->getRepository(Area::class)->find($areaId);
            if (ClientService::checkTeamAccess($area->getTeam(), $currentUser)) {
                $area->addToGroup($areaGroup);
                $area->setUpdatedAt(new \DateTime());
                $areaGroup->addArea($area);
            }
        }

        return $areaGroup;
    }

    /**
     * @param AreaGroup $areaGroup
     * @param User $currentUser
     * @throws \Exception
     */
    public function remove(AreaGroup $areaGroup, User $currentUser)
    {
        if ($areaGroup->getType() === AreaGroup::TYPE_FUEL_STATION) {
            return $areaGroup;
        }

        try {
            $areaGroup->setStatus(AreaGroup::STATUS_DELETED);
            $areaGroup->deletedAreas = clone $areaGroup->getAreasEntities();

            $areaGroup->removeAllAreas();
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new AreaGroupDeletedEvent($areaGroup, $currentUser),
                AreaGroupDeletedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param AreaGroup $areaGroup
     * @param User $currentUser
     * @return AreaGroup
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function restore(AreaGroup $areaGroup, User $currentUser)
    {
        try {
            $areaGroup->setStatus(AreaGroup::STATUS_ACTIVE);
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new AreaGroupUpdatedEvent($areaGroup, $currentUser),
                AreaGroupUpdatedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }

        return $areaGroup;
    }

    public function archive(AreaGroup $areaGroup, User $currentUser)
    {
        try {
            $areaGroup->setStatus(AreaGroup::STATUS_ARCHIVE);
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new AreaGroupUpdatedEvent($areaGroup, $currentUser),
                AreaGroupUpdatedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }

        return $areaGroup;
    }
}
