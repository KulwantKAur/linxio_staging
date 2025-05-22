<?php

namespace App\Service\User;

use App\Entity\Notification\Event;
use App\Entity\Reseller;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\UserDevice;
use App\Entity\UserGroup;
use App\Enums\EntityHistoryTypes;
use App\Events\User\UserAddedToGroupEvent;
use App\Events\User\UserArchivedEvent;
use App\Events\User\UserDeletedEvent;
use App\Events\User\UserRemovedFromGroupEvent;
use App\Events\User\UserUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Mailer\MailSender;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\File\FileService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Route\RouteService;
use App\Service\Setting\SettingService;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService extends BaseService
{
    use UserServiceFieldsTrait;

    protected $translator;
    private $em;
    private $fileService;
    private $encoder;
    private $userFinder;
    private $entityHistoryService;
    private $eventDispatcher;
    private $mailSender;
    private $tokenStorage;
    private $notificationDispatcher;
    private $routeService;
    private $validator;
    private SettingService $settingService;

    public const ELASTIC_NESTED_FIELDS = [
        'role' => 'role.name'
    ];
    public const ELASTIC_SIMPLE_FIELDS = [
        'status' => 'status',
        'fullName' => 'fullname',
        'driver' => 'fullname',
        'email' => 'email',
        'team_type' => 'team_type',
        'client_id' => 'client_id',
        'teamId' => 'team.id',
        'vehicleIds' => 'vehicle.id',
        'driverSensorId' => 'driver_sensor_id',
        'driverFOBId' => 'driverFOBId',
        'user_id' => 'id',
        'driverId' => 'id',
        'isDualAccount' => 'isDualAccount',
        'isInDriverList' => 'isInDriverList',
        'driver_id' => 'driverId',
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'last_logged_at' => 'last_logged_at'
    ];
    public const ELASTIC_FULL_SEARCH_FIELDS = [
        'driver_sensor_id',
    ];
    public const FILTER_ROLE_ADMIN_DRIVER = 'admin-driver';
    public const FILTER_ROLE_MANAGER_DRIVER = 'manager-driver';

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        FileService $fileService,
        UserPasswordHasherInterface $encoder,
        TransformedFinder $userFinder,
        EntityHistoryService $entityHistoryService,
        EventDispatcherInterface $eventDispatcher,
        MailSender $mailSender,
        TokenStorageInterface $tokenStorage,
        NotificationEventDispatcher $notificationDispatcher,
        RouteService $routeService,
        ValidatorInterface $validator,
        SettingService $settingService,
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->fileService = $fileService;
        $this->encoder = $encoder;
        $this->userFinder = new ElasticSearch($userFinder);
        $this->entityHistoryService = $entityHistoryService;
        $this->eventDispatcher = $eventDispatcher;
        $this->mailSender = $mailSender;
        $this->tokenStorage = $tokenStorage;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->routeService = $routeService;
        $this->validator = $validator;
        $this->settingService = $settingService;
    }

    public function create(array $userData): User
    {
        $this->validateUserFields($userData);
        $user = new User($userData);
        $user->setPassword(null);
        $user = $this->handleCreateParams($user, $userData);

        $user->setStatus(User::STATUS_NEW);
        $user->setVerifyToken(VerificationService::generateVerifyToken());

        $this->validate($this->validator, $user, ['create']);
        $this->em->persist($user);

        if (isset($picture)) {
            $picture->setCreatedBy($user);
        }

        $this->em->flush();
        $this->em->refresh($user);

        if ($userData[Setting::LANGUAGE_SETTING] ?? null) {
            $languageSetting = $this->settingService
                ->setLanguageSetting($user->getTeam(), $userData[Setting::LANGUAGE_SETTING], $user, $user->getRole());
            $user->setLanguage($languageSetting->getValue());
        }

        if ($user->is2FAEnabled()) {
            $this->mailSender->verifyPhone($user);
        } else {
            $this->mailSender->setPassword($user);
        }

        if ($user->isInAdminTeam() || $user->isInResellerTeam()) {
            $this->notificationDispatcher->dispatch(Event::ADMIN_USER_CREATED, $user);
        }

        return $user;
    }

    /**
     * @param int $userId
     * @return User
     */
    public function get(int $userId): User
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($userId);

        if (!$user) {
            throw new NotFoundHttpException($this->translator->trans('auth.user.not_found'));
        }

        return $user;
    }

    /**
     * @param string $email
     * @return User
     */
    public function findUserByEmail(string $email): User
    {
        $user = $this->em->getRepository(User::class)->findByEmail($email);

        if (!$user) {
            throw new BadCredentialsException($this->translator->trans('auth.invalid_credentials'));
        }

        return $user;
    }

    /**
     * @param string $phone
     * @return User
     */
    public function findUserByPhone(string $phone): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['phone' => $phone]);

        if (!$user) {
            throw new NotFoundHttpException($this->translator->trans('auth.user.not_found_with_phone'));
        }

        return $user;
    }

    /**
     * @param string $token
     * @return User
     */
    public function findUserByVerifyToken(string $token): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['verifyToken' => $token]);

        if (!$user) {
            throw new NotFoundHttpException($this->translator->trans('auth.user.not_found_with_verify_token'));
        }

        return $user;
    }

    /**
     * @param User $user
     * @param $password
     * @return User
     * @throws \Exception
     */
    public function updatePassword(User $user, $password): User
    {
        $oldPassword = $user->getPassword();
        $newPassword = $this->encoder->hashPassword($user, $password);

        if ($newPassword == $oldPassword) {
            throw (new ValidationException())->setErrors([$this->translator->trans('auth.reset_password.the_same')]);
        }

        $user->setPassword($newPassword);
        $this->em->flush();

        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isInResellerTeam()) {
            $this->notificationDispatcher->dispatch(Event::ADMIN_USER_PWD_RESET, $user);
        } else {
            $this->notificationDispatcher->dispatch(Event::USER_PWD_RESET, $user);
        }

        return $user;
    }

    /**
     * @param array $params
     * @param bool $paginated
     * @return array
     */
    public function usersList(array $params, bool $paginated = true)
    {
        $params = self::handleRoleParams($params);
        $params = User::handleStatusParams($params);
        $params['team_type'] = isset($params['teamType']) ? $params['teamType'] : Team::TEAM_ADMIN;
        $params['fields'] = array_merge(User::DISPLAYED_VALUES, $params['fields'] ?? []);
        $fields = $this->prepareElasticFields($params);

        return $this->userFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getAdminUserById(int $id)
    {
        return $this->em->getRepository(User::class)->findByIdAndTeam($id, Team::TEAM_ADMIN);
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->em->getRepository(User::class)->find($id);
    }

    public function editAdminTeamUser(int $id, array $userData, User $currentUser): ?User
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findByIdAndTeam($id, Team::TEAM_ADMIN);
        if (!$user) {
            throw new NotFoundHttpException($this->translator->trans('auth.user.not_found'));
        }
        $userData = $this->prepareEditParams(
            $userData,
            array_merge(self::getEditableFieldsByUser($currentUser, $user), ['email', 'teamPermissions'])
        );
        $this->validateEditableFields($userData);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            $prevUser = clone $user;

            $userBlocked = $this->blockHandler($user, $userData);

            $user = $this->handleEditParams($user, $userData);

            $user->setAttributes($userData);
            $user->setUpdatedBy($currentUser);
            $user->setUpdatedAt(new \DateTime());

            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(new UserUpdatedEvent($user), UserUpdatedEvent::NAME);

            if ((isset($userData['name']) && $userData['name'] !== $prevUser->getName())
                || (isset($userData['surname']) && $userData['surname'] !== $prevUser->getSurname())) {
                $this->notificationDispatcher->dispatch(
                    Event::ADMIN_USER_CHANGED_NAME,
                    $user,
                    null,
                    ['oldValue' => $prevUser->getName() ?? $prevUser->getSurname() ?? null]
                );
            }

            if ($userBlocked) {
                $this->notificationDispatcher->dispatch(Event::ADMIN_USER_BLOCKED, $user);
            }
            if (isset($userData['role']) && $prevUser->getRole()->getId() !== $userData['role']->getId()) {
                $this->notificationDispatcher->dispatch(Event::ACCESS_LEVEL_CHANGED, $user, null,
                    ['oldRole' => $prevUser->getRole()->getName(), 'triggeredBy' => $currentUser->getFullName()]);
            }

            return $user;
        } catch (\Exception $e) {
            $this->em->clear();
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param User $user
     * @param array $userData
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function blockHandler(User $user, array $userData)
    {
        if (isset($userData['isBlocked'])) {
            $isBlocked = filter_var($userData['isBlocked'], FILTER_VALIDATE_BOOLEAN);
            if (true === $isBlocked && !$user->isBlocked()) {
                $this->entityHistoryService->create(
                    $user,
                    $user->getStatus(),
                    EntityHistoryTypes::USER_STATUS,
                    $user->getCreatedBy()
                );
                $user->setStatus(User::STATUS_BLOCKED);
                unset($userData['isBlocked']);

                //check clients permissions if block user
                if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
                    $this->checkUserIsClientManager($user);
                }

                return true;
            } elseif (false === $isBlocked && $user->isBlocked()) {
                $user->setStatus(User::STATUS_ACTIVE);
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param array $exclude
     * @return array
     * @throws \Exception
     */
    public function checkUserIsClientManager(User $user, array $exclude = [])
    {
        $clientsWithManager = [];
        foreach ($user->getTeamPermissions() as $team) {
            $client = $team->getClient();
            if (!$client) {
                return [];
            }

            if (!in_array($team->getId(), $exclude)) {
                $user->removeTeamPermission($team);
            }
        }
        if (count($clientsWithManager)) {
            throw (new ValidationException())->setErrors($clientsWithManager);
        }

        return $clientsWithManager;
    }

    /**
     * @param User $user
     * @param array $permissions
     * @return User
     * @throws \Exception
     */
    public function updateTeamPermissions(User $user, array $permissions = [])
    {
        if (($user->isSalesManager() || $user->isAccountManager()) && (!$user->isAllTeamsPermissions() ?? null)) {
            $this->checkUserIsClientManager($user, $permissions);
            foreach ($permissions as $permission) {
                $team = $this->em->getRepository(Team::class)->find($permission);
                if ($team && !$user->hasTeamPermission($team->getId())) {
                    $user->addTeamPermission($team);
                }
            }
        }

        return $user;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isUserAvailableLogin(User $user)
    {
        return in_array(
            $user->getStatus(),
            [
                User::STATUS_ACTIVE,
                User::STATUS_NEW,
            ],
            true
        );
    }

    /**
     * @param int $managerId
     * @param array $teamIds
     * @return mixed
     */
    public function addTeamsToManager(int $managerId, array $teamIds)
    {
        $manager = $this->em->getRepository(User::class)->find($managerId);
        $teams = $this->em->getRepository(Team::class)->findIDS($teamIds);
        
        foreach ($teams as $team) {
            if ($manager->isAccountManager()) {
                $team->getClient()->setManager($manager);
            } elseif ($manager->isSalesManager()) {
                $team->getClient()->setSalesManager($manager);
            }
            $manager->addTeamPermission($team);
        }
        $this->em->flush();

        return $manager;
    }

    /**
     * @param int $id
     * @return User|object|null
     * @throws \Exception
     */
    public function deleteUserById(int $id)
    {
        $user = $this->em->getRepository(User::class)->find($id);
        $user->setStatus(User::STATUS_DELETED);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new UserDeletedEvent($user), UserDeletedEvent::NAME);

        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isInResellerTeam()) {
            $this->notificationDispatcher->dispatch(Event::ADMIN_USER_DELETED, $user);
        } else {
            $this->notificationDispatcher->dispatch(Event::USER_DELETED, $user);
        }

        return $user;
    }

    public function archiveUserById(int $id): User
    {
        $user = $this->em->getRepository(User::class)->find($id);
        $user->setStatus(User::STATUS_ARCHIVE);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new UserArchivedEvent($user), UserArchivedEvent::NAME);

        return $user;
    }

    /**
     * @return Team
     */
    public function getAdminTeam()
    {
        $adminTeam = $this->em->getRepository(Team::class)->findOneBy(
            [
                'type' => Team::TEAM_ADMIN
            ]
        );

        if (!$adminTeam) {
            $adminTeam = Team::createNewAdminTeam();
            $this->em->persist($adminTeam);
            $this->em->flush();
        }

        return $adminTeam;
    }

    /**
     * @param $id
     * @param User $currentUser
     * @return User
     */
    public function restore($id, User $currentUser): User
    {
        if ($currentUser->isDriverClient() && $currentUser->getId() !== $id) {
            throw new AccessDeniedException();
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if ($user->getStatus() === User::STATUS_ARCHIVE) {
            $statusHistory = $this->entityHistoryService->listWithExclude(
                User::class,
                $user->getId(),
                EntityHistoryTypes::USER_STATUS,
                [User::STATUS_ARCHIVE]
            )->first();

            if ($statusHistory) {
                $user->setStatus($statusHistory->getPayload());
            } else {
                $user->setStatus(User::STATUS_NEW);
            }
            $this->em->flush();

            $this->eventDispatcher->dispatch(new UserUpdatedEvent($user), UserUpdatedEvent::NAME);
        }

        return $user;
    }

    public function undelete($id): User
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if ($user->getStatus() === User::STATUS_DELETED) {
            $statusHistory = $this->entityHistoryService->listWithExclude(
                User::class,
                $user->getId(),
                EntityHistoryTypes::USER_STATUS,
                [User::STATUS_DELETED]
            )->first();

            if ($statusHistory) {
                $user->setStatus($statusHistory->getPayload());
            } else {
                $user->setStatus(User::STATUS_ACTIVE);
            }
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return User|string|null
     */
    public function getLoggedUser()
    {
        return $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
    }

    /**
     * @param string $email
     * @param string $deviceId
     * @return object|null
     */
    public function findUserByEmailAndDeviceId(string $email, string $deviceId)
    {
        return $this->em->getRepository(UserDevice::class)->findByIdAndTeam($email, $deviceId);
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @param bool $paginated
     * @param bool $withDualAccount
     * @return array
     * @throws \ReflectionException
     */
    public function getDrivers(array $params, User $currentUser, bool $paginated = true, bool $withDualAccount = true)
    {
        $params = $this->handleParamsForDriversAndDualAccounts($params, $withDualAccount);
        $params = User::handleStatusParams($params);

        if ($currentUser->isInClientTeam()) {
            $params['teamId'] = $currentUser->getTeam()->getId();
        }
        if ($currentUser->isClientManager() && !$currentUser->isAllTeamsPermissions()) {
            $params['teamId'] = $currentUser->getManagedTeamsIds();
        }
        if ($currentUser->needToCheckUserGroup()) {
            $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($currentUser);
            $params['vehicleIds'] = $vehicleIds;
        }

        $params['fields'] = array_merge(User::DRIVERS_LIST_FIELDS, $params['fields'] ?? []);
        $fields = $this->prepareElasticFields($params);

        return $this->userFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getDailyData(User $user): array
    {
        $currentUser = $this->tokenStorage->getToken()
        && $this->tokenStorage->getToken()->getUser() instanceof User
            ? $this->tokenStorage->getToken()->getUser() : null;

        if ($currentUser && $currentUser->getTimezone()) {
            $time = Carbon::now()->setTimezone($currentUser->getTimezone());
            $dateFrom = $time->startOfDay()->toIso8601String();
            $dateTo = $time->endOfDay()->toIso8601String();
        } else {
            $time = Carbon::now()->setTimezone(TimeZone::DEFAULT_TIMEZONE['name']);
            $dateFrom = $time->startOfDay()->toIso8601String();
            $dateTo = $time->endOfDay()->toIso8601String();
        }

        return $this->routeService->getDriverOrVehicleRoutes($dateFrom, $dateTo, $currentUser, $user, null);
    }

    /**
     * @param User $user
     * @param array|string $groupIds
     * @param User $currentUser
     * @return User
     */
    public function addUserToGroups(User $user, $groupIds, User $currentUser)
    {
        $userGroupIds = $user->getGroupsId();
        $idsToDelete = (!is_array($groupIds) && StringHelper::isNullString($groupIds))
            ? $userGroupIds
            : array_diff($userGroupIds, $groupIds);
        $idsToAdd = (!is_array($groupIds) && StringHelper::isNullString($groupIds)) ? [] : $groupIds;

        foreach ($idsToDelete as $idToDelete) {
            $group = $this->em->getRepository(UserGroup::class)->find($idToDelete);

            if (ClientService::checkTeamAccess($group->getTeam(), $currentUser)) {
                $group->removeUser($user);
                $user->setUpdatedAt(new \DateTime());
                $user->removeFromGroup($group);
                $this->eventDispatcher
                    ->dispatch(new UserRemovedFromGroupEvent($user, $group), UserRemovedFromGroupEvent::NAME);
            }
        }

        foreach ($idsToAdd as $groupId) {
            $group = $this->em->getRepository(UserGroup::class)->find($groupId);

            if (ClientService::checkTeamAccess($group->getTeam(), $currentUser) && !in_array($groupId, $userGroupIds)) {
                $user->addToGroup($group);
                $user->setUpdatedAt(new \DateTime());
                $group->addUser($user);
                $this->eventDispatcher
                    ->dispatch(new UserAddedToGroupEvent($user, $group), UserAddedToGroupEvent::NAME);
            }
        }

        return $user;
    }

    public function prepareUserListExportData($users, $params)
    {
        return $this->translateEntityArrayForExport($users, $params['fields']);
    }

    public function editUser(User $user, User $currentUser, array $userData)
    {
        $userData = $this->prepareEditUserParams($userData, $currentUser, $user);

        $this->validateUserEditableFields($userData, $user);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $userBlocked = $this->blockHandler($user, $userData);

            if (isset($userData['phone']) && $user->getPhone() !== $userData['phone']) {
                $user->unverifyPhone();
            }

            if ($userData['avatar'] ?? null) {
                $picture = $this->fileService->uploadAvatar($userData['avatar']);
                $user->setPicture($picture);
            }

            if ($userData['groups'] ?? null) {
                $user = $this->addUserToGroups($user, $userData['groups'], $currentUser);
                unset($userData['groups']);
            }
            $prevUser = clone $user;

            $user->setAttributes($userData);
            $user->setUpdatedBy($currentUser);

            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->em->refresh($user);

            $this->eventDispatcher->dispatch(new UserUpdatedEvent($user), UserUpdatedEvent::NAME);

            if ((isset($userData['name']) && $userData['name'] !== $prevUser->getName())
                || (isset($userData['surname']) && $userData['surname'] !== $prevUser->getSurname())) {
                $this->notificationDispatcher->dispatch(
                    $user->getUserChangedNameEvent(), $user, null, ['oldValue' => $prevUser->getFullName() ?? null]
                );
            }

            if ($userBlocked) {
                $this->notificationDispatcher->dispatch($user->getUserBlockedEvent(), $user);
            }

            return $user;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function resellerUserList(array $params, Reseller $reseller, $paginated = true): array
    {
        $params = User::handleStatusParams($params);
        $params['teamId'] = $reseller->getTeam()->getId();
        $fields = $this->prepareElasticFields($params);

        return $this->userFinder->find(
            $fields,
            empty($fields['_source'])
                ? array_merge(User::SIMPLE_VALUES, ['lastLoggedAt'])
                : $fields['_source'],
            $paginated
        );
    }

    /**
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function updateUserNetworkStatus(User $user): User
    {
        $user->setLastOnlineDate(new \DateTime());
        $user->setNetworkStatus(User::NETWORK_STATUS_ONLINE);
        $this->em->flush();

        return $user;
    }

    /**
     * @param string $type
     * @param array $params
     * @param User $currentUser
     * @param bool $paginated
     * @return array
     * @throws \ReflectionException
     */
    public function getUsersForFilter(string $type, array $params, User $currentUser, bool $paginated = true)
    {
        switch ($type) {
            case 'all':
            case 'arc':
                $params['teamId'] = $this->em->getRepository(User::class)->getUsersForFilter(true, true, true);
                break;
            case 'ar':
                $params['teamId'] = $this->em->getRepository(User::class)->getUsersForFilter(true, true, false);
                break;
            case 'rc':
                $resellerClientTeamIds = $this->em->getRepository(Reseller::class)
                    ->getResellerClientTeams($currentUser->getReseller());
                $resellerClientTeamIds[] = $currentUser->getTeam()->getId();
                $params['teamId'] = $resellerClientTeamIds;
                break;
            case 'c':
                if ($currentUser->isInClientTeam()) {
                    $params['teamId'] = $currentUser->getTeam()->getId();
                }
                if ($currentUser->isClientManager() && !$currentUser->isAllTeamsPermissions()) {
                    $params['teamId'] = $currentUser->getManagedTeamsIds();
                }
                break;
            default:
                $params['teamId'] = $this->em->getRepository(User::class)->getUsersForFilter();
        }

        $params['status'] = User::STATUS_ALL;
        $params['showDeleted'] = true;
        $params = User::handleStatusParams($params);
        $fields = $this->prepareElasticFields($params);

        return $this->userFinder->find(
            $fields,
            empty($fields['_source'])
                ? array_merge(User::SIMPLE_VALUES, ['team', 'createdBy'])
                : $fields['_source'],
            $paginated
        );
    }
}
