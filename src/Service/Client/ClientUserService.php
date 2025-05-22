<?php

namespace App\Service\Client;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Events\Client\ClientUpdatedEvent;
use App\Events\User\UserCreatedEvent;
use App\Events\User\UserDeletedEvent;
use App\Events\User\UserPreUpdatedEvent;
use App\Events\Sensor\SensorUserUpdatedEvent;
use App\Events\User\UserUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\File\FileService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Setting\SettingService;
use App\Service\User\UserService;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientUserService extends BaseService
{
    use ClientFieldsTrait;

    protected $translator;
    private $em;
    private $userService;
    private $userFinder;
    private $eventDispatcher;
    private $fileService;
    private $notificationDispatcher;
    private $validator;
    private $vehicleService;
    private SettingService $settingService;

    public const ELASTIC_NESTED_FIELDS = [
        'keyContactName' => 'key_contact.full_name',
        'manager' => 'manager.full_name',
        'plan' => 'plan.display_name'
    ];

    public const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
        'status' => 'status',
        'fullName' => 'fullname',
        'email' => 'email',
        'client_id' => 'client_id',
        'createdByTeamId' => 'createdBy.team.id',
        'resellerId' => 'reseller.id'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'last_logged_at' => 'last_logged_at',
        'createdAt' => 'created_at',
        'usersCount' => 'users_count',
        'activeUsersCount' => 'active_users_count',
        'vehiclesCount' => 'vehicles_count',
        'activeVehiclesCount' => 'active_vehicles_count',
        'devicesCount' => 'devices_count',
        'activeDevicesCount' => 'active_devices_count'
    ];

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        UserService $userService,
        TransformedFinder $userFinder,
        EventDispatcherInterface $eventDispatcher,
        FileService $fileService,
        NotificationEventDispatcher $notificationDispatcher,
        ValidatorInterface $validator,
        VehicleService $vehicleService,
        SettingService $settingService
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->userService = $userService;
        $this->userFinder = new ElasticSearch($userFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->fileService = $fileService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->validator = $validator;
        $this->vehicleService = $vehicleService;
        $this->settingService = $settingService;
    }

    public function createClientUser(array $fields, User $currentUser): User
    {
        $client = $this->em->getRepository(Client::class)->find($fields['clientId']);

        if (!$client) {
            throw (new ValidationException())->setErrors(
                ['client' => ['required' => $this->translator->trans('validation.errors.field.required')]]
            );
        }

        $fields['client'] = $client;
        $fields['teamType'] = $client->getTeam()->getType();
        $defaultRole = $this->em->getRepository(Role::class)->findOneBy(
            [
                'name' => Role::ROLE_ADMIN,
                'team' => 'client'
            ]
        );

        if ($fields['roleId'] ?? null) {
            $role = $this->em->getRepository(Role::class)->findOneBy(['id' => $fields['roleId'], 'team' => 'client']);
            $fields['role'] = $role ? $role : $defaultRole;
        } else {
            $fields['role'] = $defaultRole;
        }

        if ($currentUser->isInClientTeam() && $currentUser->isManagerClient()
            && $fields['role']->getName() !== Role::ROLE_CLIENT_DRIVER) {
            throw (new ValidationException())->setErrors(
                ['roleId' => ['required' => $this->translator->trans('validation.errors.field.wrong_value')]]
            );
        }

        $this->em->getConnection()->beginTransaction();
        $user = $this->userService->create($fields);

        if ($user && !$client->getKeyContact()) {
            $client->setKeyContact($user);
        }

        $this->em->flush();

        if ($fields[Setting::TIMEZONE_SETTING] ?? null) {
            $timezoneSetting = $this->settingService->setTimezoneSetting(
                $fields[Setting::TIMEZONE_SETTING], $user->getTeam(), $user->getRole(), $user
            );
            $timezoneEntity = $this->em->getRepository(TimeZone::class)->find($timezoneSetting->getValue());
            $user->setTimezone($timezoneEntity);
        }

        $this->em->getConnection()->commit();

        $this->eventDispatcher->dispatch(new UserCreatedEvent($user), UserCreatedEvent::NAME);

        if ($user->isInClientTeam()) {
            $this->notificationDispatcher->dispatch(Event::USER_CREATED, $user);
        }

        //when we update(create,delete and etc) some relative fields
        $this->eventDispatcher->dispatch(new ClientUpdatedEvent($client), ClientUpdatedEvent::NAME);

        return $user;
    }

    /**
     * @param array $params
     * @param bool $paginated
     * @return array
     */
    public function getClientUsers(array $params, bool $paginated = true)
    {
        $params = UserService::handleRoleParams($params);
        $params = User::handleStatusParams($params);
        $params['fields'] = array_merge(User::DISPLAYED_VALUES, $params['fields'] ?? []);
        $fields = $this->userService->prepareElasticFields($params);

        return $this->userFinder->find($fields, $fields['_source'], $paginated);
    }

    /**
     * @param int $clientId
     * @param array $params
     * @return array
     */
    public function getClientUsersForChat(int $clientId, array $params): array
    {
        $params['client_id'] = $clientId;
        $params['status'] = [User::STATUS_NEW, User::STATUS_ACTIVE];
        $clientUsers = $this->getClientUsers($params);
        $userIds = [];
        $clientUsers['data'] = array_filter(
            $clientUsers['data'],
            function (array $clientUserData) use ($clientId, &$userIds) {
                $clientUser = $this->getClientUser($clientId, $clientUserData['id']);

                if ($clientUser->hasPermission(Permission::CHAT_LIST)
                    || $clientUser->hasPermission(Permission::CHAT_LIST_ALL)
                ) {
                    $userIds[] = $clientUserData['id'];

                    return true;
                }

                return false;
            }
        );

        $params['user_id'] = $userIds;

        return $this->getClientUsers($params);
    }

    /**
     * @param int $clientId
     * @param int $userId
     * @return User|null
     * @throws EntityNotFoundException
     */
    public function getClientUser(int $clientId, int $userId): ?User
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);
        $user = $this->em->getRepository(User::class)->findOneBy(['id' => $userId, 'team' => $client->getTeam()]);

        if (!$user) {
            throw new EntityNotFoundException($this->translator->trans('entities.user.not_found'));
        }

        return $user;
    }

    public function editClientUser(int $clientId, int $userId, User $currentUser, array $userData)
    {
        if ($currentUser->isDriverClient() && (int)$currentUser->getId() !== $userId) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getClientUser($clientId, $userId);
        $userData = $this->prepareEditClientUserParams($userData, $currentUser, $user);
        $this->validateClientUserEditableFields($userData, $user);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $userBlocked = $this->userService->blockHandler($user, $userData);

            if (isset($userData['phone']) && $user->getPhone() !== $userData['phone']) {
                $user->unverifyPhone();
            }
            if ($userData['avatar'] ?? null) {
                $picture = $this->fileService->uploadAvatar($userData['avatar']);
                $user->setPicture($picture);
            }

            if (isset($userData['driverId']) && $currentUser->isDriverClient()) {
                unset($userData['driverId']);
            }

            //unset driver from vehicle if change role
            if (isset($userData['role']) && $userData['role']->getId() !== $user->getRole()->getId()) {
                $triggerAccessLevelChanged = $user->getRole()->getName();
                if ($user->isDriverClientOrDualAccount() && $user->getVehicle()) {
                    $this->vehicleService->unsetVehicleDriver($user->getVehicle(), $user, new \DateTime());
                }
            }

            $user->setUpdatedBy($currentUser);
            $user->setUpdatedAt(new \DateTime());

            if ($userData['groups'] ?? null) {
                $user = $this->userService->addUserToGroups($user, $userData['groups'], $currentUser);
                unset($userData['groups']);
            }
            if (isset($userData['driverSensorId']) && empty($userData['driverSensorId'])) {
                $user->setDriverSensorId(null);
                $user->setSensor(null);
            }
            if (isset($userData['driverFOBId']) && empty($userData['driverFOBId'])) {
                $user->setDriverFOBId(null);
            }

            $prevName = $user->getName();
            $prevSurname = $user->getSurname();
            $prevFullName = $user->getFullName();

            $user->setAttributes($userData);
            $this->validate($this->validator, $user, ['edit']);
            $userPreUpdatedEvent = $this->eventDispatcher
                ->dispatch(new UserPreUpdatedEvent($user), UserPreUpdatedEvent::NAME);
            $this->em->flush();
            $this->em->refresh($user);

            if ($userData[Setting::LANGUAGE_SETTING] ?? null) {
                $languageSetting = $this->settingService
                    ->setLanguageSetting($user->getTeam(), $userData[Setting::LANGUAGE_SETTING], $user,
                        $user->getRole());
                $user->setLanguage($languageSetting->getValue());
            }

            if ($userData[Setting::TIMEZONE_SETTING] ?? null) {
                $this->settingService->setTimezoneSetting(
                    $userData[Setting::TIMEZONE_SETTING], $user->getTeam(), $user->getRole(), $user
                );
            }

            $this->eventDispatcher->dispatch(new UserUpdatedEvent($user), UserUpdatedEvent::NAME);
            $this->eventDispatcher->dispatch(
                new SensorUserUpdatedEvent($user, $currentUser, $userPreUpdatedEvent),
                SensorUserUpdatedEvent::NAME
            );
            $this->em->getConnection()->commit();

            if ((isset($userData['name']) && $userData['name'] !== $prevName)
                || (isset($userData['surname']) && $userData['surname'] !== $prevSurname)) {
                $this->notificationDispatcher->dispatch(
                    Event::USER_CHANGED_NAME,
                    $user,
                    null,
                    ['oldValue' => $prevFullName ?? null]
                );
            }

            if ($userBlocked) {
                $this->notificationDispatcher->dispatch(Event::USER_BLOCKED, $user);
            }

            if ($triggerAccessLevelChanged ?? null) {
                $this->notificationDispatcher->dispatch(Event::ACCESS_LEVEL_CHANGED, $user, null,
                    ['oldRole' => $triggerAccessLevelChanged, 'triggeredBy' => $currentUser->getFullName()]);
            }

            $this->em->refresh($user);

            return $user;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }

            throw $e;
        }
    }

    /**
     * @param int $clientId
     * @param int $userId
     * @throws EntityNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function deleteClientUser(int $clientId, int $userId)
    {
        /** @var User $user */
        $user = $this->getClientUser($clientId, $userId);

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $relatedClients = $this->em->getRepository(Client::class)->findBy(['keyContact' => $user]);

            if (count($relatedClients) > 0) {
                throw (new ValidationException())->setErrors(
                    ['keyContact' => ['is_key_contact' => $this->translator->trans('entities.user.is_key_contact')]]
                );
            }

            $user->setStatus(User::STATUS_DELETED);

            if ($user->isDriverClientOrDualAccount() && $user->getVehicle()) {
                $this->vehicleService->unsetVehicleDriver($user->getVehicle(), $user, new \DateTime());
            }
            $user->removeFromAllGroups();

            $this->em->flush();
            $connection->commit();

            $this->eventDispatcher->dispatch(new UserDeletedEvent($user), UserDeletedEvent::NAME);

            if ($user->isAdmin() || $user->isSuperAdmin()) {
                $this->notificationDispatcher->dispatch(Event::ADMIN_USER_DELETED, $user);
            } else {
                $this->notificationDispatcher->dispatch(Event::USER_DELETED, $user);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
}