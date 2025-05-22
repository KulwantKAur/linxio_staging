<?php

namespace App\Service\Reseller;

use App\Entity\Reseller;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Events\Reseller\ResellerCreatedEvent;
use App\Events\Reseller\ResellerUpdatedEvent;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\File\FileService;
use App\Service\Note\NoteService;
use App\Service\PlatformSetting\PlatformSettingService;
use App\Service\Setting\SettingService;
use App\Service\Setting\TimeZoneService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResellerService extends BaseService
{
    use ResellerServiceFieldsTrait;

    protected $translator;
    private $em;
    private $resellerFinder;
    private $eventDispatcher;
    private $validator;
    private $fileService;
    private $timeZoneService;
    private $userService;
    private $noteService;
    private $platformSettingService;
    private SettingService $settingService;

    public const ELASTIC_NESTED_FIELDS = [
        'manager' => 'manager.full_name',
        'salesManager' => 'salesManager.full_name'
    ];

    public const ELASTIC_SIMPLE_FIELDS = [
        'companyName' => 'companyName',
        'keyContact' => 'keyContact.fullName',
        'domain' => 'domain',
        'status' => 'status'
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'usersCount' => 'users_count',
        'activeUsersCount' => 'active_users_count',
        'devicesCount' => 'devices_count',
        'activeDevicesCount' => 'active_devices_count'
    ];

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $resellerFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        FileService $fileService,
        TimeZoneService $timeZoneService,
        UserService $userService,
        NoteService $noteService,
        PlatformSettingService $platformSettingService,
        SettingService $settingService
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->resellerFinder = new ElasticSearch($resellerFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->fileService = $fileService;
        $this->timeZoneService = $timeZoneService;
        $this->userService = $userService;
        $this->noteService = $noteService;
        $this->platformSettingService = $platformSettingService;
        $this->settingService = $settingService;
    }

    public function create(array $data, User $currentUser): Reseller
    {
        $resellerTeam = Team::createNewResellerTeam();
        $this->em->persist($resellerTeam);

        $data = $this->prepareResellerData($data);

        $reseller = new Reseller($data);
        $reseller->setTeam($resellerTeam);
        $reseller->setCreatedBy($currentUser);

        $this->validate($this->validator, $reseller);

        $this->em->persist($reseller);

        $timeZone = $this->timeZoneService->setTimeZone(
            $this->timeZoneService->getTimeZone($data)->getId(),
            $resellerTeam
        );
        $reseller->setTimeZone($timeZone);

        $this->em->flush();
        $this->em->refresh($reseller);

        if ($data[Setting::LANGUAGE_SETTING] ?? null) {
            $language = $this->settingService
                ->setLanguageSetting($reseller->getTeam(), $data[Setting::LANGUAGE_SETTING]);
            $reseller->setLanguage($language->getValue());
        }

        $this->eventDispatcher->dispatch(new ResellerCreatedEvent($reseller), ResellerCreatedEvent::NAME);

        $this->handleResellerNotes($data, $reseller, $currentUser);

        return $reseller;
    }

    public function edit(Reseller $reseller, array $data, User $currentUser)
    {
        $data = $this->prepareResellerData($data);

        $reseller->setAttributes($data);

        $timeZone = $this->timeZoneService->setTimeZone(
            $this->timeZoneService->getTimeZone($data)->getId(),
            $reseller->getTeam()
        );
        $reseller->setTimeZone($timeZone);
        $reseller->setUpdatedBy($currentUser);
        $reseller->setUpdatedAt(new \DateTime());

        $this->validate($this->validator, $reseller);

        $this->em->flush();

        if ($data[Setting::LANGUAGE_SETTING] ?? null) {
            $language = $this->settingService
                ->setLanguageSetting($reseller->getTeam(), $data[Setting::LANGUAGE_SETTING]);
            $reseller->setLanguage($language->getValue());
        }

        $this->eventDispatcher->dispatch(new ResellerUpdatedEvent($reseller), ResellerUpdatedEvent::NAME);

        $this->handleResellerNotes($data, $reseller, $currentUser);

        return $reseller;
    }

    public function getById($id): ?Reseller
    {
        return $this->em->getRepository(Reseller::class)->find($id);
    }

    public function resellerList(array $params, $paginated = true): array
    {
        $params = self::handleStatusParams($params);
        $fields = $this->prepareElasticFields($params);

        return $this->resellerFinder->find(
            $fields,
            empty($fields['_source'])
                ? array_merge(
                    Reseller::DEFAULT_DISPLAY_VALUES,
                    [
                        'usersCount',
                        'activeUsersCount',
                        'devicesCount',
                        'activeDevicesCount'
                    ]
                )
                : $fields['_source'],
            $paginated
        );
    }

    public function createResellerUser(Reseller $reseller, array $data, User $currentUser): User
    {
        $data['reseller'] = $reseller;
        $data['teamType'] = $reseller->getTeam()->getType();
        $data['team'] = $reseller->getTeam();
        $defaultRole = $this->em->getRepository(Role::class)->findOneBy(
            [
                'name' => Role::ROLE_RESELLER_ADMIN,
                'team' => Team::TEAM_RESELLER
            ]
        );
        if ($data['roleId'] ?? null) {
            $role = $this->em->getRepository(Role::class)->findOneBy(
                ['id' => $data['roleId'], 'team' => Team::TEAM_RESELLER]
            );
            $data['role'] = $role ? $role : $defaultRole;
        } else {
            $data['role'] = $defaultRole;
        }
        $data['createdBy'] = $currentUser;

        $user = $this->userService->create($data);

        if ($user && !$reseller->getKeyContact()) {
            $reseller->setKeyContact($user);
        }

        $this->em->flush();

        return $user;
    }

    public function editResellerUser(Reseller $reseller, User $user, array $data, User $currentUser): User
    {
        if ($data['roleId'] ?? null) {
            $role = $this->em->getRepository(Role::class)->findOneBy(
                ['id' => $data['roleId'], 'team' => Team::TEAM_RESELLER]
            );
            if ($role) {
                $data['role'] = $role;
            } else {
                unset($data['role']);
            }
        }

        $data['updatedBy'] = $currentUser;

        $user = $this->userService->editUser($user, $currentUser, $data);

        if ($user && !$reseller->getKeyContact()) {
            $reseller->setKeyContact($user);
        }

        $this->em->flush();

        return $user;
    }

    public function getResellerUser($id, Reseller $reseller): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['id' => $id, 'team' => $reseller->getTeam()]);
    }

    public function prepareExportData(array $items, array $fields = [])
    {
        return $this->translateEntityArrayForExport($items, $fields, Reseller::class);
    }
}