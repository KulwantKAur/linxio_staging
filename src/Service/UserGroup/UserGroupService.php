<?php

namespace App\Service\UserGroup;

use App\Entity\Notification\Event;
use App\Entity\PlanRolePermission;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Events\UserGroup\UserGroupCreatedEvent;
use App\Events\UserGroup\UserGroupDeletedEvent;
use App\Events\UserGroup\UserGroupUpdatedEvent;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserGroupService extends BaseService
{
    use UserGroupFieldsTrait;
    use UserGroupEntityTrait;
    use UserGroupScopeTrait;

    private NotificationEventDispatcher $notificationDispatcher;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
        'status' => 'status',
        'teamId' => 'team.id'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'usersCount' => 'usersCount'
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManager $em,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TransformedFinder $userGroupFinder,
        NotificationEventDispatcher $notificationDispatcher,
    ) {
        $this->notificationDispatcher = $notificationDispatcher;
    }

    public function create(array $data, User $currentUser): UserGroup
    {
        $this->validateUserFields($data, $currentUser, self::ACTION_CREATE);
        $data = $this->prepareCreateParams($data);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            $userGroup = new UserGroup($data);

            if ($currentUser->isInClientTeam()) {
                $userGroup->setTeam($currentUser->getTeam());
            } else {
                $team = $data['teamId'] ? $this->em->getRepository(Team::class)->find($data['teamId']) : null;

                if (!$team) {
                    throw new NotFoundHttpException($this->translator->trans('entities.user.team_not_found'));
                }

                $userGroup->setTeam($team);
            }

            $this->em->persist($userGroup);

            if (isset($data['scope'])) {
                $userGroup = $this->fillScopeValues($userGroup, $currentUser, $data['scope'], $data);
            }
            if (isset($data['areaScope'])) {
                $userGroup = $this->fillAreaScopeValues($userGroup, $currentUser, $data['areaScope'], $data);
            }

            if ($data['userIds'] ?? null) {
                $userGroup = $this->addUserToUserGroup($data['userIds'], $userGroup);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(
                new UserGroupCreatedEvent($userGroup, $currentUser),
                UserGroupCreatedEvent::NAME
            );

            return $userGroup;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function edit(array $data, User $currentUser, UserGroup $userGroup): UserGroup
    {
        $this->validateUserGroupEditFields($data, $currentUser);
        $data = $this->prepareEditParams($data);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            if ($data['name'] ?? null) {
                $userGroup->setName($data['name']);
            }

            $oldUsersPermissionIds = [];
            $newUserIds = [];
            if (isset($data['userIds'])) {
                $newUserIds = array_diff($data['userIds'], $userGroup->getUserIds());

                $userGroup = $this->addUserToUserGroup($data['userIds'], $userGroup);

                foreach ($userGroup->getUsers() as $user) {
                    if (in_array($user->getId(), $newUserIds)) {
                        continue;
                    }
                    $oldUsersPermissionIds[$user->getId()] = $this->em->getRepository(PlanRolePermission::class)
                        ->getUserPermissionIds($user);
                }
            } else {
                foreach ($userGroup->getUsers() as $user) {
                    $oldUsersPermissionIds[$user->getId()] = $this->em->getRepository(PlanRolePermission::class)
                        ->getUserPermissionIds($user);
                }
            }

            if (isset($data['scope'])) {
                $userGroup = $this->fillScopeValues($userGroup, $currentUser, $data['scope'], $data);
            }

            if (isset($data['areaScope'])) {
                $userGroup = $this->fillAreaScopeValues($userGroup, $currentUser, $data['areaScope'], $data);
            }

            if (isset($data['permissions'])) {
                $userGroup->setPermissions($data['permissions']);
                foreach ($oldUsersPermissionIds as $userId => $permissionsIds) {
                    $user = $this->em->getRepository(User::class)->find($userId);
                    $userNewPermissionIds = $this->em->getRepository(PlanRolePermission::class)
                        ->getUserPermissionIds($user);

                    //if for old users added/deleted permissions in this group
                    if (array_diff($permissionsIds, $userNewPermissionIds)
                        || array_diff($userNewPermissionIds, $permissionsIds)) {
                        $this->notificationDispatcher->addEvent(Event::ACCESS_LEVEL_CHANGED, $user);
                    }
                }
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->em->refresh($userGroup);

            $this->eventDispatcher->dispatch(
                new UserGroupUpdatedEvent($userGroup, $currentUser),
                UserGroupUpdatedEvent::NAME
            );

            $this->notificationDispatcher->dispatchEventStorage();

            return $userGroup;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function userGroupsList(array $params, User $user, bool $paginated = true)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam() || ($user->isInAdminTeam() && !($params['teamId'] ?? null))) {
            $params['teamId'] = $user->getTeamId();
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        $params = UserGroup::handleStatusParams($params);

        $fields = $this->prepareElasticFields($params);
        $elastica = new ElasticSearch($this->userGroupFinder);

        return $elastica->find($fields, $fields['_source'] ?? [], $paginated);
    }

    public function getById(int $id)
    {
        return $this->em->getRepository(UserGroup::class)->find($id);
    }

    public function remove(UserGroup $userGroup, User $currentUser)
    {
        try {
            $userGroup->setStatus(UserGroup::STATUS_DELETED);
            $userGroup->removeAllUsers();
            $userGroup->removeAllVehicles();
            $userGroup->removeAllVehicleGroups();
            $userGroup->removeAllDepots();

            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new UserGroupDeletedEvent($userGroup, $currentUser),
                UserGroupDeletedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function restore(UserGroup $userGroup, User $currentUser)
    {
        try {
            $userGroup->setStatus(UserGroup::STATUS_ACTIVE);
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new UserGroupUpdatedEvent($userGroup, $currentUser),
                UserGroupUpdatedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }

        return $userGroup;
    }

    public function archive(UserGroup $userGroup, User $currentUser)
    {
        try {
            $userGroup->setStatus(UserGroup::STATUS_ARCHIVE);
            $this->em->flush();

            $this->eventDispatcher->dispatch(
                new UserGroupUpdatedEvent($userGroup, $currentUser),
                UserGroupUpdatedEvent::NAME
            );
        } catch (\Exception $e) {
            throw $e;
        }

        return $userGroup;
    }
}
