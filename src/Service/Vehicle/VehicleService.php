<?php

namespace App\Service\Vehicle;

use App\Entity\Device;
use App\Entity\DriverHistory;
use App\Entity\FuelType\FuelType;
use App\Entity\Notification\Event;
use App\Entity\Notification\Message;
use App\Entity\Notification\NotificationMobileDevice;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\TimeZone;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\EntityManager\SlaveEntityManager;
use App\Events\User\Driver\DriverUnassignedFromVehicleEvent;
use App\Events\Vehicle\VehicleArchivedEvent;
use App\Events\Vehicle\VehicleCreatedEvent;
use App\Events\Vehicle\VehicleDeletedEvent;
use App\Events\Vehicle\VehicleRestoredEvent;
use App\Events\Vehicle\VehicleUpdatedEvent;
use App\Events\VehicleGroup\VehicleAddedToVehicleGroupEvent;
use App\Events\VehicleGroup\VehicleRemovedFromVehicleGroupEvent;
use App\Exceptions\ValidationException;
use App\Repository\Tracker\TrackerHistoryRepository;
use App\Service\BaseService;
use App\Service\Device\DeviceService;
use App\Service\DrivingBehavior\DrivingBehaviorService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\File\FileService;
use App\Service\Firebase\FCMService;
use App\Service\Note\NoteService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use App\Service\Route\RouteService;
use App\Service\Setting\SettingService;
use App\Service\TrackerProvider\TrackerProviderService;
use App\Service\User\UserServiceHelper;
use App\Service\Validation\ValidationService;
use App\Service\VehicleGroup\VehicleGroupService;
use App\Util\ExceptionHelper;
use App\Util\GeoHelper;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VehicleService extends BaseService
{
    use VehicleValidationTrait;
    use VehicleServiceFieldsTrait;

    private $vehicleFinder;
    protected $translator;
    private $tokenStorage;
    private $drivingBehaviorService;
    private $trackerProviderService;
    private $updateDriverProducer;
    private ObjectPersister $deviceObjectPersister;
    private MemoryDbService $memoryDbService;
    private FCMService $fcmService;

    public const ELASTIC_FULL_SEARCH_FIELDS = [
        'defaultlabel',
        'regNo'
    ];
    public const ELASTIC_NESTED_FIELDS = [
    ];
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'vehicleIds' => 'id',
        'type' => 'type.id',
        'client' => 'clientName',
        'model' => 'model',
        'defaultLabel' => 'defaultlabel',
        'year' => 'year',
        'vin' => 'vin',
        'regCertNo' => 'regcertno',
        'fuelType' => 'fuelType.id',
        'fuelTankCapacity' => 'fueltankcapacity',
        'clientId' => 'clientId',
        'groups' => 'groups.id',
        'depot' => 'depot.id',
        'depotName' => 'depot.name',
        'regNo' => 'regNo',
        'status' => 'status',
        'driverName' => 'driverName',
        'driver' => 'driverEmail',
        'driverId' => 'driverId',
        'teamId' => 'team.id',
        'teamType' => 'team.type',
        'isInArea' => 'isInArea',
        'areaGroupIds' => 'areasGroups.id',
        'areaIds' => 'areas.id',
        'mileage' => 'mileage',
        'engineHours' => 'engineHours',
        'hasReminders' => 'hasReminders',
        'reminderCategory' => 'remindersCategories.id',
        'isInAssetList' => 'isInAssetList',
        'deviceImei' => 'deviceImei',
        'fullSearch' => 'fullSearchField',
        'updatedAt' => 'updatedAt',
    ];
    public const ELASTIC_RANGE_FIELDS = [
        'regDate' => 'regDate',
        'serviceRecordDate' => 'reminders.activeServiceRecords.date'
    ];

    public const ELASTIC_CASE_OR = [
        'defaultLabel' => 'defaultlabel',
        'defaultabel' => 'defaultlabel'
    ];

    public const FIELDS_IN_SECONDS = [
        'parkingTime',
        'drivingTime',
        'durationTotal',
        'duration',
        'idlingTime',
        'parking_time',
        'driving_time'
    ];
    public const FIELDS_IN_METRES = [
        'distance',
        'startOdometer',
        'endOdometer',
        'privateDistance',
        'workDistance',
        'distanceTotal'
    ];

    public const VEHICLE_ACTION = 'vehicleAction';
    public const VEHICLE_DELETE = 'vehicleDelete';
    public const VEHICLE_ARCHIVE = 'vehicleArchive';

    public function __construct(
        TranslatorInterface $translator,
        private readonly EntityManager $em,
        TransformedFinder $vehicleFinder,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ValidationService $validationService,
        private readonly FileService $fileService,
        private readonly EntityHistoryService $entityHistoryService,
        private readonly RouteService $routeService,
        TokenStorageInterface $tokenStorage,
        private readonly NoteService $noteService,
        TrackerProviderService $trackerProviderService,
        private readonly LoggerInterface $logger,
        private readonly DeviceService $deviceService,
        private readonly NotificationEventDispatcher $notificationDispatcher,
        private readonly ValidatorInterface $validator,
        DrivingBehaviorService $drivingBehaviorService,
        ProducerInterface $updateDriverProducer,
        private readonly SettingService $settingService,
        private readonly SlaveEntityManager $emSlave,
        ObjectPersister $deviceObjectPersister,
        MemoryDbService $memoryDbService,
        FCMService $fcmService,
    ) {
        $this->vehicleFinder = new ElasticSearch($vehicleFinder);
        $this->tokenStorage = $tokenStorage;
        $this->drivingBehaviorService = $drivingBehaviorService;
        $this->trackerProviderService = $trackerProviderService;
        $this->updateDriverProducer = $updateDriverProducer;
        $this->deviceObjectPersister = $deviceObjectPersister;
        $this->memoryDbService = $memoryDbService;
        $this->fcmService = $fcmService;
        $this->translator = $translator;
    }

    /**
     * @param Vehicle $vehicle
     * @param User $driver
     * @return void|null
     */
    private function driverChangedPushNotification(Vehicle $vehicle, User $driver)
    {
        if ($vehicle->getTeam()->isAdminTeam()) {
            return null;
        }

        try {
            $title = $this->translator->trans(
                'entities.vehicle.you_were_unassigned_from_the_vehicle', ['%regno%' => $vehicle->getRegNo()]
            );
            $body = '';
            $device = $this->em->getRepository(NotificationMobileDevice::class)
                ->getLastLoggedDeviceByUserBy($driver->getId());
            $additionalData = [
                'type' => Message::PUSH_TYPE_FOR_UNASSIGNED_DRIVER,
                'clientId' => $vehicle->getClientId(),
                'teamId' => $vehicle->getTeam()->getId(),
                'vehicleId' => $vehicle->getId(),
                'driverId' => $driver->getId(),
            ];

            if ($title && $body && $device) {
                $this->fcmService->sendNotification(
                    $device,
                    $title,
                    $body,
                    $additionalData
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return Vehicle
     * @throws ValidationException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function create(array $data, User $currentUser): Vehicle
    {
        $this->validateVehicleFields($data, $currentUser);
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            $vehicle = new Vehicle($data);
            $vehicle = $this->handleCreateFields($vehicle, $currentUser, $data);

            $this->em->persist($vehicle);
            $this->em->flush();

            $this->handleNotesFields($vehicle, $currentUser, $data);

            $this->em->getConnection()->commit();

            $this->eventDispatcher->dispatch(new VehicleCreatedEvent($vehicle), VehicleCreatedEvent::NAME);
            $this->notificationDispatcher->dispatch(Event::VEHICLE_CREATED, $vehicle);

            return $vehicle;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function vehicleList(
        array $params,
        User $user,
        bool $paginated = true,
        $defaultFields = Vehicle::LIST_DISPLAY_VALUES
    ) {
        if (empty($params['fullSearch'])) {
            unset($params['fullSearch']);
        } else {
            $params['fullSearch'] = mb_strtolower($params['fullSearch']);
        }

        $params = UserServiceHelper::handleTeamParams($params, $user);
        $params = VehicleServiceHelper::handleUserGroupParams($params, $user, $this->em);
        $params = VehicleServiceHelper::handleDriverVehicleParams($params, $this->em, $user);

        if (isset($params['hasReminders'])) {
            $params['hasReminders'] = StringHelper::stringToBool($params['hasReminders']);
        }

        $params = self::handleDepotAndGroupsParams($params);
        $params['fields'] = array_merge($defaultFields, $params['fields'] ?? []);
        $params = Vehicle::handleStatusParams($params);
        $params = VehicleServiceHelper::handleElasticArrayParams($params);
        $fields = $this->prepareElasticFields($params);
        unset($fields['fullSearch']);

        return $this->vehicleFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    public function getById(int $id, User $user): ?Vehicle
    {
        if ($user->isControlAdmin()) {
            $vehicle = $this->em->getRepository(Vehicle::class)->find($id);
        } else {
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($user, $id);
        }

        return $vehicle;
    }

    private function addVehicleToGroups(Vehicle $vehicle, ArrayCollection $groups, User $currentUser)
    {
        $vehicleGroups = $vehicle->getGroups();

        foreach ($vehicleGroups as $vehicleGroup) {
            if (!$groups->contains($vehicleGroup)) {
                $vehicleGroup->removeVehicle($vehicle);
                $vehicle->removeFromGroup($vehicleGroup);
                $this->eventDispatcher->dispatch(
                    new VehicleRemovedFromVehicleGroupEvent($vehicle, $vehicleGroup, $currentUser),
                    VehicleRemovedFromVehicleGroupEvent::NAME
                );
            }
        }
        if ($groups->count()) {
            foreach ($groups as $group) {
                if (!$vehicle->getGroups()->contains($group)
                    && VehicleGroupService::checkVehicleGroupAccess($group, $currentUser)
                ) {
                    $group->addVehicle($vehicle);
                    $vehicle->addToGroup($group); //hack only for response, does not impact to entity database data
                    $this->eventDispatcher->dispatch(
                        new VehicleAddedToVehicleGroupEvent($vehicle, $group, $currentUser),
                        VehicleAddedToVehicleGroupEvent::NAME
                    );
                }
            }
        } elseif ($groups->count() === 0) {
            foreach ($vehicleGroups as $vehicleGroup) {
                $vehicleGroup->removeVehicle($vehicle);
                $vehicle->removeFromGroup($vehicleGroup);
                $this->eventDispatcher->dispatch(
                    new VehicleRemovedFromVehicleGroupEvent($vehicle, $vehicleGroup, $currentUser),
                    VehicleRemovedFromVehicleGroupEvent::NAME
                );
            }
        }

        return $vehicle;
    }

    public function edit(array $data, User $currentUser, Vehicle $vehicle): Vehicle
    {
        $this->validateVehicleFields($data, $currentUser, $vehicle);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $this->handleEditFields($vehicle, $currentUser, $data);
            $vehicle->setUpdatedBy($currentUser);

            if (isset($data['type'])) {
                $type = $this->em->getRepository(VehicleType::class)->findOneBy(['name' => $data['type']]);
            } elseif (isset($data['typeId'])) {
                $type = $this->em->getRepository(VehicleType::class)->find($data['typeId']);
            }
            if (isset($type)) {
                $vehicle->setType($type);
                unset($data['type']);
            }

            $vehicle = $this->handleDepot($data, $vehicle, $currentUser);
            $driver = isset($data['driverId']) ? $this->em->getRepository(User::class)
                ->findOneBy(['id' => $data['driverId']]) : null;
            $prevDriverHistory = $this->em->getRepository(DriverHistory::class)->findUnfinishedLastHistoryByVehicle(
                $vehicle
            );

            if ($driver && $driver->isDriverClientOrDualAccount()) {
                $startDate = $data['driverStartDate'] ?? null;
                $driverHistory = $this->setVehicleDriver($vehicle, $driver, $startDate, false);
            } elseif (array_key_exists('driverId', $data) && is_null($data['driverId']) && $vehicle->getDriver()) {
                $this->unsetVehicleDriver($vehicle, $vehicle->getDriver());
            }

            if (isset($data['fuelType'])) {
                $data['fuelType'] = $this->em->getRepository(FuelType::class)->find(['id' => $data['fuelType']]);
            }

            if ($data['regDate'] ?? null) {
                $data['regDate'] = self::parseDateToUTC($data['regDate']);
            }

            $vehicle = $this->unavailableHandler($vehicle, $data, $currentUser);

            $prevVehicle = clone $vehicle;
            $vehicle->setAttributes($data);

            $this->em->flush();

            $this->handleNotesFields($vehicle, $currentUser, $data);

            $this->em->getConnection()->commit();

            if ($vehicle->getDevice()) {
                $this->deviceObjectPersister->replaceOne($vehicle->getDevice());
            }

            if ($driverHistory ?? null) {
                $this->updateDriverProducer->publish($driverHistory->getId());
            }

            if ($vehicle ?? null) {
                $this->handleEditEvents($vehicle, $prevVehicle, $data, $prevDriverHistory);
            }

            return $vehicle;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    /**
     * @param Vehicle $vehicle
     * @return mixed
     */
    public function restore(Vehicle $vehicle)
    {
        if ($vehicle->getStatus() === Vehicle::STATUS_ARCHIVE) {
            $vehicle->setStatus(Vehicle::STATUS_OFFLINE);
            $this->em->flush();

            $this->eventDispatcher->dispatch(new VehicleRestoredEvent($vehicle), VehicleRestoredEvent::NAME);
        }

        return $vehicle;
    }

    /**
     * @param Vehicle $vehicle
     * @return mixed
     */
    public function undelete(Vehicle $vehicle)
    {
        if ($vehicle->getStatus() === Vehicle::STATUS_DELETED) {
            $vehicle->setStatus(Vehicle::STATUS_OFFLINE);
            $this->em->flush();

            $this->eventDispatcher->dispatch(new VehicleCreatedEvent($vehicle), VehicleCreatedEvent::NAME);
        }

        return $vehicle;
    }

    /**
     * @param Vehicle $vehicle
     * @param User $currentUser
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function removeVehicle(Vehicle $vehicle, User $currentUser)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            if ($vehicle->getDevice()) {
                $this->deviceService->uninstallDevice(
                    [
                        'device' => $vehicle->getDevice(),
                        'vehicleId' => $vehicle->getId(),
                        'updatedBy' => $currentUser
                    ],
                    $currentUser
                );
            }

            $vehicle->setStatus(Vehicle::STATUS_DELETED);
            $vehicle->setUpdatedBy($currentUser);
            $vehicle->setUpdatedAt(new \DateTime());

            $this->em->flush();
            $connection->commit();

            $this->eventDispatcher->dispatch(new VehicleDeletedEvent($vehicle), VehicleDeletedEvent::NAME);
            $this->notificationDispatcher->dispatch(Event::VEHICLE_DELETED, $vehicle);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public function archive(Vehicle $vehicle, User $currentUser)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            if ($vehicle->getDevice()) {
                $this->deviceService->uninstallDevice(
                    [
                        'device' => $vehicle->getDevice(),
                        'vehicleId' => $vehicle->getId(),
                        'updatedBy' => $currentUser
                    ],
                    $currentUser
                );
            }

            $vehicle->setStatus(Vehicle::STATUS_ARCHIVE);
            $vehicle->setUpdatedBy($currentUser);
            $vehicle->setUpdatedAt(new \DateTime());

            $this->em->flush();
            $connection->commit();

            $this->eventDispatcher->dispatch(new VehicleArchivedEvent($vehicle), VehicleArchivedEvent::NAME);
        } catch (\Exception $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->getConnection()->rollback();
            }
            throw $e;
        }
    }

    public function setVehicleDriver(
        Vehicle $vehicle,
        User $driver,
        $startDate = null,
        $checkEvent = true
    ): DriverHistory {
        $date = $startDate ? self::parseDateToUTC($startDate) : new Carbon();
        $isEventDriver = false;
        $this->validateSetDriverFields($date, $vehicle, $driver);

        try {
            $connection = $this->em->getConnection();

            /** @var DriverHistory $lastVehicleHistory */
            $lastVehicleHistory = $this->em->getRepository(DriverHistory::class)->findLastHistoryByVehicle($vehicle);

            //if current vehicle driver == target driver
            if ($lastVehicleHistory && $lastVehicleHistory->getDriver()->getId() === $driver->getId()
                && !$lastVehicleHistory->getFinishDate()
            ) {
                $lastVehicleDriverHistory = $this->em->getRepository(DriverHistory::class)
                    ->findLastHistoryByVehicleAndNotTargetDriver($vehicle, $driver);
                $this->validatePrevVehicleHistoryFinishDate($lastVehicleDriverHistory, $date);
                $lastVehicleHistory->setStartDate($date);
                $vehicle->setDriver($driver);

                $this->em->flush();
                $driverHistory = $lastVehicleHistory;
            } else {
                $this->validateDriverNewDrivingDate($driver, $date);
                $this->validatePrevVehicleHistoryDate($lastVehicleHistory, $date);

                $currVehicles = $this->em->getRepository(Vehicle::class)->findBy(['driver' => $driver]);

                $connection->beginTransaction();

                foreach ($currVehicles as $currVehicle) {
                    $this->unsetVehicleDriver($currVehicle, $driver, $date, false);
                }

                if ($vehicle->getDriver()) {
                    $this->unsetVehicleDriver($vehicle, $vehicle->getDriver(), $date, true);
                }

                $vehicle->setDriver($driver);
                $driverHistory = new DriverHistory(
                    [
                        'vehicle' => $vehicle,
                        'driver' => $driver,
                        'startDate' => $date
                    ]
                );

                $this->em->persist($driverHistory);
                $this->em->flush();
                $connection->commit();
                $isEventDriver = true;

                $this->driverChangeNotification($vehicle);
                $this->eventDispatcher->dispatch(new VehicleUpdatedEvent($vehicle), VehicleUpdatedEvent::NAME);
            }
            //inside transaction - need to call this method directly
            if (!$connection->isTransactionActive()) {
                $this->updateDriverProducer->publish($driverHistory->getId());
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }

        if ($isEventDriver !== false && $checkEvent !== false) {
            $this->sendDriverChangedEvent($vehicle, $lastVehicleHistory);
        }

        return $driverHistory;
    }

    public function sendDriverChangedEvent($vehicle, ?DriverHistory $lastVehicleHistory)
    {
        if ($lastVehicleHistory?->getDriver()?->getId() === $vehicle->getDriver()?->getId()) {
            return;
        }

        $oldValue = ($lastVehicleHistory && $lastVehicleHistory->getDriver() && $lastVehicleHistory->getDriver()->getFullName())
            ? $lastVehicleHistory->getDriver()->getFullName()
            : null;

        $this->notificationDispatcher->dispatch(Event::VEHICLE_REASSIGNED, $vehicle, null, ['oldValue' => $oldValue]);
    }

    public function unsetVehicleDriver(
        Vehicle $vehicle,
        User $driver,
        $finishDate = null,
        bool $sendNotification = true
    ): Vehicle {
        try {
            $vehicle->setDriver(null);
            $finishDate = $finishDate ?? new Carbon();
            /** @var DriverHistory $driverHistory */
            $driverHistory = $this->em->getRepository(DriverHistory::class)->findByUnfinishedHistory($vehicle, $driver);

            if ($driverHistory) {
                if (is_string($finishDate)) {
                    $finishDate = new Carbon($finishDate);
                }
                if ($finishDate < $driverHistory->getStartDate()) {
                    throw new \Exception($this->translator->trans('validation.errors.field.wrong_value'));
                }
                $driverHistory->setFinishDate($finishDate ?? new Carbon());
            }

            $this->em->flush();

            if ($sendNotification) {
                $this->driverChangeNotification($vehicle);
                $this->driverChangedPushNotification($vehicle, $driver);
            }

            $this->eventDispatcher->dispatch(new VehicleUpdatedEvent($vehicle), VehicleUpdatedEvent::NAME);
            $this->eventDispatcher->dispatch(
                new DriverUnassignedFromVehicleEvent($driver, $vehicle),
                DriverUnassignedFromVehicleEvent::NAME
            );

            return $vehicle;
        } catch (\Exception $e) {
            $this->logger->error('Vehicle id: ' . $vehicle->getId());
            $this->logger->error('DriverHistory id: ' . (isset($driverHistory) ? $driverHistory->getId() : 'null'));
            $this->logger->error('datetime: ' . $finishDate->format('Y-m-d H:i:s'));
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());

            return $vehicle;
        }
    }

    public function getDailyData(Vehicle $vehicle): array
    {
        if (!$vehicle->getDevice()) {
            return [
                'distance' => 0,
                'duration' => 0,
                'avgSpeed' => 0,
                'idleDuration' => 0,
            ];
        }

        $todayData = $this->memoryDbService->getFromJson(VehicleRedisModel::getTodayDataKey($vehicle->getId()));
        $timezone = $vehicle->getTimeZoneName();
        $ts = isset($todayData['firstTs']) ? Carbon::createFromTimestamp($todayData['firstTs'], $timezone) : null;
        $now = (new Carbon())->setTimezone($timezone);

        if ($todayData && $ts && $ts->day === $now->day) {
            return [
                'distance' => $todayData['distance'],
                'duration' => $todayData['duration'],
                'avgSpeed' => $todayData['avgSpeed'],
                'idleDuration' => $todayData['idleDuration'] ?? 0,
            ];
        } else {
            return [
                'distance' => 0,
                'duration' => 0,
                'avgSpeed' => 0,
                'tsDay' => $ts?->day,
                'nowDay' => $now->day,
                'idleDuration' => 0,
            ];
        }
    }

    /**
     * @param Vehicle $vehicle
     * @return float|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAverageDailyMileage(Vehicle $vehicle): ?float
    {
        $periodDay = 30;
        $currentUser = $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User
            ? $this->tokenStorage->getToken()->getUser()
            : null;
        $timezone = ($currentUser && $currentUser->getTimezone())
            ? $currentUser->getTimezone()
            : TimeZone::DEFAULT_TIMEZONE['name'];
        $time = Carbon::now()->setTimezone($timezone);
        $dateFrom = clone $time;
        $dateTo = (clone $time)->subDays($periodDay);
        $finishedData = $this->em->getRepository(TrackerHistory::class)
            ->getClosestOdometerAndTsByVehicleAndDate($vehicle, $dateFrom, $dateTo);
        $startedData = $this->em->getRepository(TrackerHistory::class)
            ->getClosestOdometerAndTsByVehicleAndDate($vehicle, $dateTo, $dateFrom, false);

        if (!$finishedData || !$startedData) {
            return null;
        }

        $dayOfFinishedOdometer = Carbon::instance($finishedData['ts']);
        $dayOfStartedOdometer = Carbon::instance($startedData['ts']);
        $daysDiff = $dayOfFinishedOdometer->diffInDays($dayOfStartedOdometer);
        $odometerDiff = $finishedData['odometer'] - $startedData['odometer'];

        return round(($odometerDiff > 0 ? $odometerDiff : 0) / ($daysDiff + 1));
    }

    /**
     * @param Vehicle $vehicle
     * @return int|null
     * @throws \Exception
     */
    public function driverChangeNotification(Vehicle $vehicle)
    {
        if ($vehicle->getTeam()->isAdminTeam()) {
            return null;
        }

        return $this->trackerProviderService->driverChangeNotification($vehicle);
    }

    /**
     * @param array $criteria
     *
     * @return Vehicle|null
     */
    public function getVehicleBy(array $criteria): ?Vehicle
    {
        return $this->em->getRepository(Vehicle::class)->findOneBy($criteria);
    }

    /**
     * @param Device $device
     * @param array $trackerHistoryIDs
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateVehicleDriver(Device $device, array $trackerHistoryIDs): void
    {
        $vehicle = $device->getVehicle();
        $driver = $vehicle ? $vehicle->getDriver() : null;

        if ($vehicle && $driver) {
            $trackerHistories = $this->em->getRepository(TrackerHistory::class)
                ->getTrackerHistoriesTsAndIgnitionByIds($trackerHistoryIDs);

            if ($trackerHistories) {
                $previousTrackerHistories = $this
                    ->getPreviousTrackerHistoriesDatesAndIgnition($device, $trackerHistories[0]['ts']);
                $trackerHistories = array_merge($previousTrackerHistories, $trackerHistories);
                $recordDateWithIgnitionOff = $this->getStartDateRecordWithIgnitionOff($vehicle, $trackerHistories);

                if ($recordDateWithIgnitionOff) {
                    $this->unsetVehicleDriver($vehicle, $driver, new \DateTime());
                }
            }
        }
    }

    /**
     * @param Device $device
     * @param \DateTime $endDate
     * @return array|\Doctrine\ORM\Query
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getPreviousTrackerHistoriesDatesAndIgnition(Device $device, \DateTime $endDate)
    {
        $lastRecordWithIgnitionOn = $this->em->getRepository(TrackerHistory::class)
            ->getFirstTrackerRecordsByDeviceWithIgnition($device, $endDate);
        $newestRecordsDateAndIgnition = [];

        if ($lastRecordWithIgnitionOn) {
            $lastRecordDateWithIgnitionOff = $this->em->getRepository(TrackerHistory::class)
                ->getNextTrackerHistoryDateWithIgnition($lastRecordWithIgnitionOn);

            if ($lastRecordDateWithIgnitionOff != $endDate) {
                $newestRecordsDateAndIgnition = $this->em->getRepository(TrackerHistory::class)
                    ->getTrackerRecordsDateAndIgnitionByDevice(
                        $device->getId(),
                        $lastRecordDateWithIgnitionOff,
                        $endDate
                    );
            }
        }

        return $newestRecordsDateAndIgnition;
    }

    /**
     * @param Vehicle $vehicle
     * @param array|null $trackerHistories
     * @return \DateTime|null
     */
    private function getStartDateRecordWithIgnitionOff(Vehicle $vehicle, ?array $trackerHistories): ?\DateTime
    {
        $totalIgnitionOff = $maxTotalIgnitionOff = 0;
        $firstRecordIgnitionOff = null;
        $vehicleEngineOffSetting = $this->settingService
            ->getTeamSettingValueByKey($vehicle->getTeam(), Setting::VEHICLE_ENGINE_OFF);
        /** @var DriverHistory $lastDriverHistory */
        $lastDriverHistory = $this->em->getRepository(DriverHistory::class)->findUnfinishedLastHistoryByVehicle($vehicle);

        /** @var array $trackerHistory */
        foreach ($trackerHistories as $key => $trackerHistory) {
            if (isset($trackerHistories[$key - 1]) && $trackerHistories[$key - 1]['ignition'] == 0) {
                $firstRecordIgnitionOff = $firstRecordIgnitionOff ?? $trackerHistories[$key - 1]['ts'];
                $totalIgnitionOff += $trackerHistory['ts']->getTimestamp()
                    - $trackerHistories[$key - 1]['ts']->getTimestamp();
                $maxTotalIgnitionOff = ($totalIgnitionOff > $maxTotalIgnitionOff)
                    ? $totalIgnitionOff
                    : $maxTotalIgnitionOff;
            } else {
                $maxTotalIgnitionOff = ($totalIgnitionOff > $maxTotalIgnitionOff)
                    ? $totalIgnitionOff
                    : $maxTotalIgnitionOff;
                $totalIgnitionOff = 0;
                $firstRecordIgnitionOff = null;
            }

            if ($vehicleEngineOffSetting['enable'] && $maxTotalIgnitionOff > $vehicleEngineOffSetting['value'] && $lastDriverHistory) {
                //check if time from set driver more than engineoff setting
                $sFromStart = Carbon::instance($lastDriverHistory->getStartDate())->diffInSeconds(new Carbon(), false);
                if ($sFromStart > $vehicleEngineOffSetting['value']) {
                    return $firstRecordIgnitionOff;
                }
            }
        }

        return null;
    }

    public function createVehicleType(array $data, array $files, User $currentUser): ?VehicleType
    {
        $type = null;
        VehicleServiceHelper::validateVehicleTypeParams($data, $files, $this->translator);
        $files = VehicleServiceHelper::prepareVehicleTypePictures($files, $this->fileService, $currentUser);

        $type = new VehicleType(array_merge($data, $files));
        if ($currentUser->isInAdminTeam()) {
            $type->setTeam(null);
        } else {
            $type->setTeam($currentUser->getTeam());
        }

        $this->em->persist($type);
        $this->em->flush();


        return $type;
    }

    public function editVehicleType($id, array $data, array $files, User $currentUser): ?VehicleType
    {
        $type = null;
        $files = VehicleServiceHelper::prepareVehicleTypePictures($files, $this->fileService, $currentUser);

        $type = $this->em->getRepository(VehicleType::class)->getVehiclesTypeById($id, $currentUser);

        if (isset($files[VehicleType::DEFAULT_PICTURE])) {
            $type->setDefaultPicture($files[VehicleType::DEFAULT_PICTURE]);
        }
        if (isset($files[VehicleType::DRIVING_PICTURE])) {
            $type->setDrivingPicture($files[VehicleType::DRIVING_PICTURE]);
        }
        if (isset($files[VehicleType::IDLING_PICTURE])) {
            $type->setIdlingPicture($files[VehicleType::IDLING_PICTURE]);
        }
        if (isset($files[VehicleType::STOPPED_PICTURE])) {
            $type->setStoppedPicture($files[VehicleType::STOPPED_PICTURE]);
        }

        if ($type) {
            $type->setName($data['name']);
        }

        $this->em->flush();

        return $type;
    }

    public function deleteVehicleType($id, User $currentUser): ?VehicleType
    {
        $type = $this->em->getRepository(VehicleType::class)->getVehiclesTypeById($id, $currentUser);

        if ($type) {
            if ($type->getVehiclesCount()) {
                throw new \Exception('Can\'t delete - there are vehicles of this type');
            }
            $type->setStatus(VehicleType::STATUS_DELETED);
            $this->em->flush();
        }

        return $type;
    }

    public function restoreVehicleType($id, User $currentUser): ?VehicleType
    {
        $type = $this->em->getRepository(VehicleType::class)->getVehiclesTypeById($id, $currentUser);

        if ($type) {
            $type->setStatus(VehicleType::STATUS_ACTIVE);
            $this->em->flush();
        }

        return $type;
    }

    public function getVehicleTypes(User $user, $data = [])
    {
        $order = StringHelper::getOrder($data);
        $sort = StringHelper::getSort($data, 'name');
        $statuses = isset($data['showDeleted']) && StringHelper::stringToBool($data['showDeleted'])
            ? VehicleType::ALLOWED_STATUSES : [VehicleType::STATUS_ACTIVE];

        return $this->em->getRepository(VehicleType::class)->getVehicleTypes($user, $statuses, $sort, $order);
    }

    public function getVehicleListExportData($params, User $user, $paginated = false)
    {
        $vehicles = $this->vehicleList($params, $user, $paginated);

        return $this->translateEntityArrayForExport($vehicles, $params['fields'] ?? []);
    }

    public function getLastTrackerRecordByVehicle(Vehicle $vehicle, $occurredAt = null): ?TrackerHistory
    {
        $lastTR = null;
        $device = $vehicle->getDevice();

        if ($device) {
            $lastTR = $occurredAt
                ? $this->em->getRepository(TrackerHistory::class)
                    ->getLastTrackerRecordByDeviceAndDate($device, $occurredAt)
                : ($device->getLastTrackerRecord()?->getTrackerHistory());

            if ($lastTR && $lastTR->getVehicle() !== $vehicle) {
                return null;
            }
        } else {
            $lastTR = $occurredAt
                ? $this->em->getRepository(TrackerHistory::class)
                    ->getLastTrackerRecordByVehicleAndDate($vehicle, $occurredAt)
                : $this->em->getRepository(TrackerHistory::class)->getLastRecordByVehicle($vehicle);
        }

        return $lastTR;
    }

    public function changeOrder(array $data, User $currentUser)
    {
        foreach ($data as $item) {
            $type = $this->em->getRepository(VehicleType::class)->getVehiclesTypeById($item['id'], $currentUser);
            if ($type) {
                $this->em->getRepository(VehicleType::class)->updateOrder($item['id'], $item['order']);
            }
        }
    }

    /**
     * @param Route $route
     * @param array $include
     * @param bool $withLastCoordinates
     * @throws \Exception
     */
    public function addCoordinatesToRoute(Route $route, $include = [], $withLastCoordinates = true)
    {
        return $this->routeService->setCoordinatesToRoute($route, $include, $withLastCoordinates);
    }

    public function updateDriver(Vehicle $vehicle, User $driver, \DateTime $startDate, \DateTime $finishDate)
    {
        /** @var DriverHistory[] $dhs */
        $dhs = $this->em->getRepository(DriverHistory::class)
            ->findDriverHistoryByDateRange($vehicle, $startDate, $finishDate);

        foreach ($dhs as $dh) {
            if ($dh->getStartDate() < $startDate && $dh->getFinishDate() > $finishDate) {
                $newDh = clone $dh;
                $this->em->persist($newDh);

                $dh->setFinishDate($startDate);
                $newDh->setStartDate($finishDate);
            } elseif ($dh->getStartDate() < $startDate) {
                $dh->setFinishDate($startDate);
            } elseif ($dh->getFinishDate() > $finishDate) {
                $dh->setStartDate($finishDate);
            } else {
                $this->em->remove($dh);
            }
        }

        $dhForCreate = new DriverHistory(
            [
                'driver' => $driver,
                'vehicle' => $vehicle,
                'startDate' => $startDate,
                'finishDate' => $finishDate,
            ]
        );
        $this->em->persist($dhForCreate);
        $this->em->flush();
    }

    public function getVehicleThByDate(
        Vehicle $vehicle,
        \DateTime|string $dateFrom,
        \DateTime|string $dateTo,
        array $params,
        User $user
    ): array {
        $routeOptimizationSetting = $user->getSettingByName(Setting::ROUTE_OPTIMIZATION);

        $query = $this->em->getRepository(TrackerHistory::class)
            ->getThIterable(
                $vehicle, $dateFrom, $dateTo, 'th.ts',
                'th.id, th.ts, th.speed, th.temperatureLevel, th.movement, th.ignition, th.lat, th.lng',
                'ASC'
            );

        $count = $this->em->getRepository(TrackerHistory::class)
            ->getThIterable($vehicle, $dateFrom, $dateTo, false, 'count(th.id) as c')->getSingleResult()['c'];

        //return all if condition
        if (($count / $params['count'] < 1) || !$routeOptimizationSetting?->getValue()) {
            $coordinates = [];
            foreach ($query->toIterable() as $coordinate) {
                $coordinates[] = GeoHelper::convertCoordinatesForResponse($coordinate);
            }

            return $coordinates;
        }

        $nn = 0;
        $n = round($count / $params['count'], 0, PHP_ROUND_HALF_DOWN);
        $tempResult = [];
        $result = [];

        foreach ($query->toIterable() as $coordinate) {
            if (empty($result)) {
                $result[] = GeoHelper::convertCoordinatesForResponse($coordinate);
            }

            if ($nn == $n) {
                $result[] = TrackerHistoryRepository::getCoordinatesOptimisatedBySpeed($tempResult, $result,
                    $params['type']);
                $tempResult = [];
                $nn = 0;
            }
            $tempResult[] = GeoHelper::convertCoordinatesForResponse($coordinate);
            $nn++;
        }

        return array_merge($result, $tempResult);
    }

    public static function handleUninstallAction(string $action, Vehicle $vehicle, User $currentUser): Vehicle
    {
        if ($action === self::VEHICLE_ARCHIVE) {
            $vehicle->setStatus(Vehicle::STATUS_ARCHIVE);
            $vehicle->setUpdatedBy($currentUser);
            $vehicle->setUpdatedAt(new \DateTime());
        }
        if ($action === self::VEHICLE_DELETE) {
            $vehicle->setStatus(Vehicle::STATUS_DELETED);
            $vehicle->setUpdatedBy($currentUser);
            $vehicle->setUpdatedAt(new \DateTime());
        }

        return $vehicle;
    }
}
