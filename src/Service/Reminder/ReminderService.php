<?php

namespace App\Service\Reminder;

use App\Entity\Asset;
use App\Entity\Depot;
use App\Entity\Notification\Event;
use App\Entity\Reminder;
use App\Entity\ReminderCategory;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Enums\EntityFields;
use App\Events\Reminder\ReminderCreatedEvent;
use App\Events\Reminder\ReminderDeletedEvent;
use App\Events\Reminder\ReminderUpdatedEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\Redis\RedisService;
use App\Service\ServiceRecord\ServiceRecordService;
use App\Service\User\UserServiceHelper;
use App\Service\Validation\ValidationService;
use App\Service\Vehicle\VehicleServiceHelper;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\Notification\EventDispatcher;

class ReminderService extends BaseService
{
    use ReminderFieldsTrait;

    protected $translator;
    private $em;
    private $reminderFinder;
    private $eventDispatcher;
    private $validationService;
    private $serviceRecordService;
    private $notificationDispatcher;
    protected $dashboardCache;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [
        'title' => 'title',
        'status' => 'status',
        'controlMileage' => 'mileage',
        'remainingMileage' => 'remainingMileage',
        'controlHours' => 'hours',
        'remainingHours' => 'remainingHours',
        'teamId' => 'teamId',
        'vehicleRegNo' => 'vehicle.regno',
        'regno' => 'vehicle.regno',
        'vehicleId' => 'vehicle.id',
        'assetId' => 'asset.id',
        'vehicleIds' => 'vehicle.id',
        'categoryId' => 'category.id',
        'categoryName' => 'category.name',
        'vehicleName' => 'vehicle.defaultLabel',
        'vehicleModel' => 'vehicle.model',
        'driver' => 'vehicle.driverName',
        'driverId' => 'vehicle.driverId',
        'driverEmail' => 'vehicle.driverEmail',
        'vehicleGroups' => 'vehicle.groups.id',
        'vehicleDepot' => 'vehicle.depot.id',
        'groups' => 'vehicle.groups.id',
        'depot' => 'vehicle.depot.id',
        'lastModified' => 'updatedAt',
        'createdAt' => 'createdAt',
        'type' => 'type',
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'date' => 'date',
        'controlDate' => 'date'
    ];
    public const ELASTIC_SCRIPT_FIELDS = [
        'remainingDays' => 'date'
    ];

    public const ELASTIC_CASE_OR = [
        'vehicleName' => 'vehicle.defaultLabel',
        'defaultLabel' => 'vehicle.defaultLabel'
    ];

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TransformedFinder $reminderFinder,
        EventDispatcherInterface $eventDispatcher,
        ValidationService $validationService,
        ServiceRecordService $serviceRecordService,
        EventDispatcher $notificationDispatcher,
        RedisService $dashboardCache
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->reminderFinder = new ElasticSearch($reminderFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->validationService = $validationService;
        $this->serviceRecordService = $serviceRecordService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->dashboardCache = $dashboardCache;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return Reminder
     * @throws \Exception
     */
    public function create(array $data, User $currentUser): Reminder
    {
        $this->validateReminderFields($data, $currentUser);

        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            $reminder = new Reminder($data);
            $reminder->setCreatedBy($currentUser);

            $reminder = $this->handleCreateFields($data, $reminder, $currentUser);

            $this->em->persist($reminder);
            $this->em->flush();

//            $this->handleCreateDraftField($data, $reminder, $currentUser);

            $connection->commit();
            $this->em->refresh($reminder);

            $reminder = $this->serviceRecordService->updateStatus($reminder);

            if ($reminder ?? null) {
                $this->eventDispatcher->dispatch(new ReminderCreatedEvent($reminder), ReminderCreatedEvent::NAME);
            }

            return $reminder;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function reminderList(array $params, User $user, bool $paginated = true)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        $params = Reminder::handleStatusParams($params);

        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            $params['vehicleIds'] = isset($params['vehicleIds'])
                ? array_intersect($params['vehicleIds'], $vehicleIds)
                : $vehicleIds;
        }

        $params = self::handleDepotAndGroupsParams($params);
        $params = self::handleElasticArrayParams($params);
        $params = VehicleServiceHelper::handleDriverVehicleParams($params, $this->em, $user, false, true);
        $fields = $this->prepareElasticFields($params);

        if (isset($fields['script']['date'])) {
            $fields['script'] = $this->calculateRemainingDays('date', $fields['script']['date']);
        }

        return $this->reminderFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param int $id
     * @param User $currentUser
     * @return object|Reminder|null
     */
    public function getById(int $id, User $currentUser)
    {
        return $this->em->getRepository(Reminder::class)->getReminderById($id, $currentUser);
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @param Reminder $reminder
     * @return Reminder
     * @throws \Exception
     */
    public function edit(array $data, User $currentUser, Reminder $reminder): Reminder
    {
        $this->validateReminderFields($data, $currentUser);

        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();


            $data['updatedAt'] = new \DateTime();
            $data['updatedBy'] = $currentUser;
            if ($data['category'] ?? null) {
                $category = $this->em->getRepository(ReminderCategory::class)->find($data['category']);
                $data['category'] = $category ? $category : null;
            }

            $reminder->setAttributes($data);

            if ($data['date'] ?? null) {
                if ($data['date'] !== '') {
                    $reminder->setDate(self::parseDateToUTC($data['date']));
                }
            }

//            $reminder = $this->handleEditDraftField($data, $reminder, $currentUser);

            $this->em->flush();
            $this->em->refresh($reminder);

            $connection->commit();

            $reminder = $this->serviceRecordService->updateStatus($reminder);

            if ($reminder ?? null) {
                $this->eventDispatcher->dispatch(new ReminderUpdatedEvent($reminder), ReminderUpdatedEvent::NAME);
            }

            return $reminder;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    /**
     * @param Reminder $reminder
     * @throws \Exception
     */
    public function removeReminder(Reminder $reminder)
    {
        if (count($reminder->getServiceRecords())) {
            throw (new ValidationException())->setErrors(
                ['reminder' => ['required' => $this->translator->trans('entities.reminder.has_service_records')]]
            );
        }
        $reminder->setStatus(Reminder::STATUS_DELETED);
        $this->notificationDispatcher->dispatch(Event::SERVICE_REMINDER_DELETED, $reminder);

        $this->em->flush();

        $this->eventDispatcher->dispatch(new ReminderDeletedEvent($reminder), ReminderDeletedEvent::NAME);
    }

    /**
     * @param Reminder $reminder
     * @param User $currentUser
     * @param array $vehicles
     * @param array $depots
     * @param array $groups
     * @param array $assets
     * @return Reminder
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function duplicate(
        Reminder $reminder,
        User $currentUser,
        $vehicles = [],
        $depots = [],
        $groups = [],
        $assets = []
    ) {
        if ($vehicles) {
            $vehicles = $this->em->getRepository(Vehicle::class)
                ->findBy(['id' => $vehicles, 'team' => $reminder->getTeam()]);
            foreach ($vehicles as $vehicle) {
                $this->createReminderCopy($reminder, $currentUser, $vehicle);
            }
        }

        if ($depots) {
            $depots = $this->em->getRepository(Depot::class)
                ->findBy(['id' => $depots, 'team' => $reminder->getTeam()]);
            foreach ($depots as $depot) {
                $vehicles = $depot->getVehicles();
                foreach ($vehicles as $vehicle) {
                    $this->createReminderCopy($reminder, $currentUser, $vehicle);
                }
            }
        }

        if ($groups) {
            $vehicles = $this->em->getRepository(VehicleGroup::class)
                ->getVehiclesByGroups($groups, $reminder->getTeam());

            foreach ($vehicles as $vehicle) {
                $this->createReminderCopy($reminder, $currentUser, $vehicle);
            }
        }

        if ($assets) {
            $assets = $this->em->getRepository(Asset::class)
                ->findBy(['id' => $assets, 'team' => $reminder->getTeam()]);

            foreach ($assets as $asset) {
                $this->createReminderCopy($reminder, $currentUser, null, $asset);
            }
        }

        $this->em->flush();

        return $reminder;
    }

    /**
     * @param Reminder $reminder
     * @param Vehicle $vehicle
     * @param User $currentUser
     * @param Asset $asset
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createReminderCopy(
        Reminder $reminder,
        User $currentUser,
        ?Vehicle $vehicle = null,
        ?Asset $asset = null
    ) {
        if ($vehicle && $vehicle->getId() === $reminder->getVehicle()->getId()) {
            return;
        }
        if ($asset && $asset->getId() === $reminder->getAsset()->getId()) {
            return;
        }

        $copyReminder = clone $reminder;
        if ($vehicle) {
            $copyReminder->setVehicle($vehicle);
        } elseif ($asset) {
            $copyReminder->setAsset($asset);
        }

        $copyReminder->setCreatedBy($currentUser);
        $copyReminder->setCreatedAt(new \DateTime());
        $copyReminder->setUpdatedBy(null);
        $copyReminder->setUpdatedAt(null);
        $this->em->persist($copyReminder);
    }

    /**
     * @param User $user
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getExpiredAndDueSoonStat(User $user)
    {
        $redisKey = $this->getRedisKey($user->getTeam());
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        $expiredCount = $this->em->getRepository(Reminder::class)
            ->getCountByStatus($user, Reminder::STATUS_EXPIRED);

        $dueSoonCount = $this->em->getRepository(Reminder::class)
            ->getCountByStatus($user, Reminder::STATUS_DUE_SOON);

        $activeCount = $this->em->getRepository(Reminder::class)
            ->getCountByStatus($user, Reminder::STATUS_ACTIVE);

        $reminders = $this->em->getRepository(Reminder::class)
            ->getLastReminders($user, 3);

        $data = [
            'reminders' => array_map(
                function ($item) {
                    return $item->toArray(
                        ['title', 'vehicleRegNo', 'status', 'remainingDays', 'remainingMileage', 'remainingHours']
                    );
                },
                $reminders
            ),
            Reminder::STATUS_EXPIRED => [
                'count' => $expiredCount
            ],
            Reminder::STATUS_DUE_SOON => [
                'count' => $dueSoonCount,
            ],
            Reminder::STATUS_ACTIVE => [
                'count' => $activeCount,
            ]
        ];

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 300);

        return $data;
    }

    /**
     * @param Team $team
     * @return string
     */
    public function getRedisKey(Team $team)
    {
        return 'expiredAndDueSoonStatReminders-' . $team->getId();
    }

    /**
     * @param Team $team
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function cleanCache(Team $team)
    {
        $this->dashboardCache->deleteItem($this->getRedisKey($team));
    }

    /**
     * @param Reminder $reminder
     * @return Reminder
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function restore(Reminder $reminder, User $user)
    {
        $reminder = $this->serviceRecordService->updateStatus($reminder);
        $reminder->setUpdatedBy($user)->setUpdatedAt(new \DateTime());

        $this->em->flush();
        $this->cleanCache($reminder->getTeam());

        $this->eventDispatcher->dispatch(new ReminderUpdatedEvent($reminder), ReminderUpdatedEvent::NAME);

        return $reminder;
    }

    public function archive(Reminder $reminder, User $user)
    {
        $reminder->setStatus(Reminder::STATUS_ARCHIVE);
        $reminder->setUpdatedBy($user)->setUpdatedAt(new \DateTime());

        $this->em->flush();

        return $reminder;
    }
}
