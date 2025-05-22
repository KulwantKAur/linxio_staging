<?php

namespace App\Service\Client;

use App\Entity\Client;
use App\Entity\Notification\Event;
use App\Entity\Reseller;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Events\Client\ClientContractChangedEvent;
use App\Events\Client\ClientCreatedEvent;
use App\Events\Client\ClientStatusChangedEvent;
use App\Events\Client\ClientUpdatedEvent;
use App\Service\BaseService;
use App\Service\Billing\BillingPlanService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\File\FileService;
use App\Service\Note\NoteService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Setting\SettingService;
use App\Service\User\UserService;
use App\Service\User\UserServiceHelper;
use App\Service\Validation\ValidationService;
use App\Service\Vehicle\VehicleService;
use App\Util\DateHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientService extends BaseService
{
    use ClientFieldsTrait;

    private $clientFinder;
    private $userFinder;
    private $eventDispatcher;
    private $notificationDispatcher;
    private $validator;

    public const ELASTIC_NESTED_FIELDS = [
        'keyContactName' => 'key_contact.full_name',
        'manager' => 'manager.full_name',
        'salesManager' => 'salesManager.full_name',
        'plan' => 'plan.display_name'
    ];

    public const ELASTIC_SIMPLE_FIELDS = [
        'name' => 'name',
        'status' => 'status',
        'fullName' => 'fullname',
        'email' => 'email',
        'client_id' => 'client_id',
        'createdByTeamId' => 'createdBy.team.id',
        'ownerTeamId' => 'ownerTeam.id',
        'resellerId' => 'reseller.id',
        'contractMonths' => 'contractMonths',
        'waed' => 'waed',
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

    public const ELASTIC_SORT_FIELDS = [
        'name' => 'nameSort'
    ];

    public function __construct(
        protected readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly UserService $userService,
        private readonly NoteService $noteService,
        TransformedFinder $clientFinder,
        TransformedFinder $userFinder,
        EventDispatcherInterface $eventDispatcher,
        private readonly FileService $fileService,
        private readonly SettingService $settingService,
        private readonly ValidationService $validationService,
        NotificationEventDispatcher $notificationDispatcher,
        ValidatorInterface $validator,
        private readonly VehicleService $vehicleService,
        private readonly BillingPlanService $billingPlanService,
    ) {
        $this->clientFinder = new ElasticSearch($clientFinder);
        $this->userFinder = new ElasticSearch($userFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->validator = $validator;
    }

    public function create(array $clientData, User $currentUser): Client
    {
        $clientData = $this->prepareCreateParams($clientData);
        $this->validateClientFields($clientData, $currentUser);

        try {
            $this->em->getConnection()->beginTransaction();

            $clientTeam = Team::createNewClientTeam();
            if ($currentUser->getTeam()->isChevron()) {
                $clientTeam->setAsChevron();
            }
            $this->em->persist($clientTeam);
            $this->em->flush();

            $clientData['team'] = $clientTeam;

            $client = new Client($clientData);
            $clientTeam->setClient($client);
            if (isset($manager)) {
                $manager->addTeamPermission($clientTeam);
            }

            $this->validate($this->validator, $client);

            $this->em->persist($client);

            $this->initClientSettings($client, $currentUser);

            $timeZone = $this->setClientTimeZone($clientData, $clientTeam);
            $client->setTimeZone($timeZone);
            $client->setCreatedBy($currentUser);
            $client->setOwnerTeam($currentUser->getTeam());

            if ($clientData['salesManager'] ?? null) {
                $salesManager = $this->em->getRepository(User::class)->find($clientData['salesManager']);
                $client->setSalesManager($salesManager);
                $salesManager->addTeamPermission($client->getTeam());
            }

            $billingPlan = $this->billingPlanService->copyBillingPlanToClient($client, $currentUser);

            $this->em->flush();

            $this->eventDispatcher->dispatch(new ClientCreatedEvent($client), ClientCreatedEvent::NAME);
            $this->eventDispatcher->dispatch(new ClientStatusChangedEvent($client), ClientStatusChangedEvent::NAME);
            $this->eventDispatcher
                ->dispatch(new ClientContractChangedEvent($client, null), ClientContractChangedEvent::NAME);
            $this->notificationDispatcher->dispatch(Event::CLIENT_CREATED, $client);

            $this->em->getConnection()->commit();

            if ($clientData[Setting::LANGUAGE_SETTING] ?? null) {
                $language = $this->settingService
                    ->setLanguageSetting($client->getTeam(), $clientData[Setting::LANGUAGE_SETTING]);
                $client->setLanguage($language->getValue());
            }

            $this->handleNotesFields($clientData, $client, $currentUser);

            return $client;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $clientData
     * @param User $currentUser
     * @return Client
     * @throws EntityNotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $clientData, User $currentUser): Client
    {
        /** @var Client $client */
        $client = $this->get($id);

        try {
            $clientOld = clone $client;
            $this->em->getConnection()->beginTransaction();
            $clientData = $this->prepareEditParams($clientData, $currentUser);
            $this->validateClientFields($clientData, $currentUser);

            if ($clientData['manager'] ?? null) {
                $manager = $this->em->getRepository(User::class)->find($clientData['manager']);
                $clientData['manager'] = $manager;
                $manager->addTeamPermission($client->getTeam());
            }

            if ($clientData['salesManager'] ?? null) {
                $salesManager = $this->em->getRepository(User::class)->find($clientData['salesManager']);
                $clientData['salesManager'] = $salesManager;
                $salesManager->addTeamPermission($client->getTeam());
            }

            if ($clientData['status'] === Client::STATUS_BLOCKED_OVERDUE) {
                $keyContact = $this->em->getRepository(User::class)->find($clientData['keyContact']);
                $keyContact->setStatus(Client::STATUS_BLOCKED_OVERDUE);
            }

            if ($clientOld->getStatus() === Client::STATUS_BLOCKED_OVERDUE && $clientData['status'] !== Client::STATUS_BLOCKED_OVERDUE) {
                $keyContact = $this->em->getRepository(User::class)->find($clientData['keyContact']);
                $keyContact->setStatus(User::STATUS_ACTIVE);
            }

            $client = $this->handlePlanIdField($clientData, $client, $currentUser);
            $client->setAttributes($clientData);
            $timeZone = $this->setClientTimeZone($clientData, $client->getTeam());
            $client->setTimeZone($timeZone);
            $this->validate($this->validator, $client);

            if (isset($clientData['allowManualPayment']) && $clientData['allowManualPayment'] === false) {
                $client->setIsManualPayment(false);
            }

            if ($clientData[Setting::LANGUAGE_SETTING] ?? null) {
                $language = $this->settingService
                    ->setLanguageSetting($client->getTeam(), $clientData[Setting::LANGUAGE_SETTING]);
                $client->setLanguage($language->getValue());
            }

            $this->em->flush();

            $this->eventDispatcher->dispatch(new ClientUpdatedEvent($client), ClientUpdatedEvent::NAME);
            if ($clientOld->getStatus() !== $client->getStatus()) {
                $this->eventDispatcher->dispatch(new ClientStatusChangedEvent($client), ClientStatusChangedEvent::NAME);
            }
            $this->eventDispatcher
                ->dispatch(new ClientContractChangedEvent($client, $clientOld), ClientContractChangedEvent::NAME);


            if ($clientOld->getStatus() !== Client::STATUS_BLOCKED && $client->getStatus() === Client::STATUS_BLOCKED) {
                $this->notificationDispatcher->dispatch(Event::CLIENT_BLOCKED, $client);
            }

            $this->handleNotesFields($clientData, $client, $currentUser);

            $this->em->getConnection()->commit();

            return $client;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }

    public function getClients(array $params, User $currentUser, bool $paginated = true, $iterator = false)
    {
        $params = Client::handleStatusParams($params);
        $params = $this->prepareElasticFields($params);
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAllTeamsPermissions() && !$currentUser->isInResellerTeam()) {
            $params['fields']['teamId'] = $currentUser->getManagedTeamsIds();
        }
        if ($currentUser->isInAdminTeam()) {
            $params['fields']['teamId'] = $this->em->getRepository(Client::class)->getAdminClientTeams();
        }

        if ($currentUser->isInResellerTeam()) {
            $params['fields']['ownerTeam.id'] = $currentUser->getTeam()->getId();
        }

        $defaultFields = array_merge(Client::DEFAULT_DISPLAY_VALUES, ['manager', 'keyContact', 'plan', 'salesManager']);
        if ($currentUser->isInAdminTeam()) {
            $defaultFields[] = 'waed';
        }

        $fields = empty($params['_source']) ? $defaultFields : $params['_source'];

        return $this->clientFinder->find($params, $fields, $paginated, null, $iterator);
    }

    public function getResellerClients(Reseller $reseller, array $params, bool $paginated = true)
    {
        $params = Client::handleStatusParams($params);
        $fields = $this->prepareElasticFields($params);

        $fields['fields']['ownerTeam.id'] = $reseller->getTeam()->getId();

        return $this->clientFinder->find(
            $fields,
            empty($fields['_source'])
                ? array_merge(Client::DEFAULT_DISPLAY_VALUES, ['manager', 'salesManager', 'keyContact', 'plan'])
                : $fields['_source'],
            $paginated
        );
    }

    /**
     * @param $id
     * @return Client|null
     */
    public function getClientById($id): ?Client
    {
        return $this->em->getRepository(Client::class)->find($id);
    }

    /**
     * @param $id
     * @return mixed
     * @throws EntityNotFoundException
     */
    public function get($id)
    {
        $client = $this->em->getRepository(Client::class)->find($id);

        if (!$client) {
            throw new EntityNotFoundException($this->translator->trans('entities.client.not_found'));
        }

        return $client;
    }

    /**
     * @param Team $team
     * @param User $user
     * @return bool
     */
    public static function checkTeamAccess(Team $team, User $user)
    {
        if ($user->isInClientTeam() && $team->getId() === $user->getTeamId()) {
            return true;
        } elseif ($user->isInAdminTeam()
            && ($user->hasTeamPermission($team->getId()) || $user->isAllTeamsPermissions())) {
            return true;
        } elseif ($user->isInResellerTeam()) {
            return $user->getReseller()->checkAsResellerTeamAccess($team);
        }

        return false;
    }

    public function setClientTimeZone($clientData, $team = null, $role = null, $user = null)
    {
        $timeZoneId = DateHelper::getTimeZone($clientData, $this->em, $this->translator)->getId();
        $setting = $this->settingService->setTimezoneSetting($timeZoneId, $team, $role, $user);

        return $this->em->getRepository(TimeZone::class)->find($setting->getValue());
    }

    public function prepareExportData($clients, $params)
    {
        return $this->translateEntityArrayForExport($clients, $params['fields'] ?? []);
    }

    /**
     * @param string $type
     * @param array $params
     * @param User $currentUser
     * @param bool $paginated
     * @return array
     * @throws \ReflectionException
     */
    public function getTeamsForFilter(string $type, array $params, User $currentUser, bool $paginated = true): array
    {
        switch ($type) {
            case 'rc':
                // Only reseller teams (+ his clients)
                if ($currentUser->getReseller()) {
                    $teams = $this->em->getRepository(Reseller::class)
                        ->getResellerClientTeamsWithName($currentUser->getReseller(), $params);
                }
                break;
            case 'ar':
                // Admin teams (+ his clients) + reseller (+ his clients)
                $resellerClientTeamIds = $this->em->getRepository(Reseller::class)
                    ->getAdminResellerTeams($currentUser);
                $teams = $this->em->getRepository(Client::class)
                    ->getTeamListForFilter($currentUser, $params, $resellerClientTeamIds);

                $resellerTeams = $this->em->getRepository(Reseller::class)
                    ->getResellerTeamsByUser($currentUser, $params);

                $teams = array_merge($teams ?? null, $resellerTeams);
                $teams[0] = [
                    'id' => -1,
                    'name' => 'admin'
                ];
                break;
            case 'all':
                // Admin teams (+ his clients) + reseller (+ his clients)
                $resellerClientTeamIds = $this->em->getRepository(Reseller::class)
                    ->getAdminResellerTeams($currentUser);
                $teams = $this->em->getRepository(Client::class)
                    ->getTeamListForFilter($currentUser, $params, $resellerClientTeamIds);
                break;
            default:
                $teams = $this->em->getRepository(Client::class)->getTeamListForFilter($currentUser, $params);
        }


        return $teams;
    }
}
