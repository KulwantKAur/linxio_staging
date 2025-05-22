<?php

namespace App\Service\Device;

use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DeviceReplacement;
use App\Entity\DeviceVendor;
use App\Entity\File;
use App\Entity\Notification\Event;
use App\Entity\Reseller;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Enums\EntityHistoryTypes;
use App\Events\Device\DeviceChangedTeamEvent;
use App\Events\Device\DeviceDeactivatedEvent;
use App\Events\Device\DeviceDeletedEvent;
use App\Events\Device\DeviceInstalledEvent;
use App\Events\Device\DeviceRestoredEvent;
use App\Events\Device\DeviceUnavailableEvent;
use App\Events\Device\DeviceUninstalledEvent;
use App\Events\Device\DeviceUpdatedEvent;
use App\Events\Vehicle\VehicleStatusChangedEvent;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\Device\Import\DevicesVehiclesDriverImport;
use App\Service\Device\Import\DevicesVehiclesImport;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\File\FileService;
use App\Service\Note\NoteService;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleOdometerService;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeviceService extends BaseService
{
    use DeviceFieldsTrait;

    protected $translator;
    protected $em;
    private $noteService;
    private $deviceFinder;
    private $eventDispatcher;
    private $entityHistoryService;
    private $notificationDispatcher;
    private $fileService;
    private $devicesVehiclesImport;
    private $vehicleObjectPersister;
    private $deviceObjectPersister;
    private $validator;
    private $vehicleOdometerService;
    private $deviceServiceResolver;
    private $clientObjectPersister;
    private DeviceCommandService $deviceCommandService;
    private DeviceStreamService $deviceStreamService;
    private PaginatorInterface $paginator;

    public const ELASTIC_NESTED_FIELDS = [];

    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'imei' => 'imei',
        'imsi' => 'imsi',
        'model' => 'modelName',
        'vendor' => 'vendorName',
        'status' => 'processedStatus',
        'statusExt' => 'statusExt',
        'phone' => 'phone',
        'clientId' => 'clientId',
        'client' => 'client',
        'mileage' => 'mileage',
        'engineHours' => 'engineHours',
        'fuelType' => 'fuelType.id',
        'fuelTankCapacity' => 'fuelTankCapacity',
        'team' => 'team',
        'teamId' => 'team.id',
        'createdByTeamId' => 'createdBy.team.id',
        'resellerId' => 'reseller.id',
        'reseller' => 'reseller.companyName',
        'vehicleIds' => 'vehicle.id',
        'vehicleRegNo' => 'vehicle.regno',
        'usage' => 'usage',
        'isDeactivated' => 'isDeactivated',
        'isUnavailable' => 'isUnavailable',
        'iccid' => 'iccid',
        'contractId' => 'contractId',
        'previousPhones' => 'previousPhones',
        'modelAlias' => 'modelAlias',
        'vendorAlias' => 'vendorAlias',
        'plan' => 'plan.name',
        'chevronAccountId' => 'team.chevronAccountId',
        'ownership' => 'ownership',
    ];

    public const ELASTIC_RANGE_FIELDS = [
        'lastActiveTime' => 'lastActiveTime',
        'installDate' => 'install_date',
        'contractFinishAt' => 'contractFinishAt',
        'contractStartAt' => 'contractStartAt',
        'lastDataReceivedAt' => 'lastDataReceivedAt',
        'addedToTeam' => 'addedToTeam',
        'deactivatedAt' => 'deactivatedAt',
    ];

    /**
     * @param Device $device
     * @param Device $deviceNew
     * @return void
     */
    private function prepareDevicesDueToReplacement(Device $device, Device $deviceNew)
    {
        $device->setStatus(Device::STATUS_UNAVAILABLE); // need it?
        $device->setIsDeactivated(true);
        $this->eventDispatcher->dispatch(new DeviceDeactivatedEvent($device), DeviceDeactivatedEvent::NAME);
        $device->setIsUnavailable(true);
        $device->setBlockingMessage($this->translator->trans('entities.device.device_was_replaced_by', [
            '%imei%' => $deviceNew->getImei()
        ]));
        $this->eventDispatcher->dispatch(new DeviceUnavailableEvent($device), DeviceUnavailableEvent::NAME);
        $deviceNew->setContractFinishAt($device->getContractFinishAt());

        if ($device->getContractId()) {
            $deviceNew->setContractId($device->getContractId());
        }
    }

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        NoteService $noteService,
        TransformedFinder $deviceFinder,
        EventDispatcherInterface $eventDispatcher,
        EntityHistoryService $entityHistoryService,
        NotificationEventDispatcher $notificationDispatcher,
        FileService $fileService,
        DevicesVehiclesImport $devicesVehiclesImport,
        ObjectPersister $vehicleObjectPersister,
        ObjectPersister $deviceObjectPersister,
        DeviceServiceResolver $deviceServiceResolver,
        ValidatorInterface $validator,
        VehicleOdometerService $vehicleOdometerService,
        ObjectPersister $clientObjectPersister,
        DeviceCommandService $deviceCommandService,
        DeviceStreamService $deviceStreamService,
        PaginatorInterface $paginator
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->noteService = $noteService;
        $this->deviceFinder = new ElasticSearch($deviceFinder);
        $this->eventDispatcher = $eventDispatcher;
        $this->entityHistoryService = $entityHistoryService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->fileService = $fileService;
        $this->devicesVehiclesImport = $devicesVehiclesImport;
        $this->vehicleObjectPersister = $vehicleObjectPersister;
        $this->deviceObjectPersister = $deviceObjectPersister;
        $this->validator = $validator;
        $this->vehicleOdometerService = $vehicleOdometerService;
        $this->deviceServiceResolver = $deviceServiceResolver;
        $this->clientObjectPersister = $clientObjectPersister;
        $this->deviceCommandService = $deviceCommandService;
        $this->paginator = $paginator;
        $this->deviceStreamService = $deviceStreamService;
    }

    /**
     * @param array $data
     * @param User $currentUser
     * @return Device
     * @throws ValidationException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function create(array $data, User $currentUser): Device
    {
        $this->validateDeviceFields($data, $currentUser);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $device = new Device($data);
            $device = $this->handleCreateFields($device, $data, $currentUser);

            $this->em->persist($device);
            $this->em->flush();

            $this->handleNotesFields($data, $device, $currentUser);

            $this->em->getConnection()->commit();

            return $device;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param int $id
     * @param User $currentUser
     * @return object|Device|null
     * @throws \Exception
     */
    public function getById(int $id, User $currentUser)
    {
        return $this->deviceServiceResolver->getInstance($currentUser)->getById($id);
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     */
    public function deviceList(array $params, User $user, bool $paginated = true, bool $iterator = false)
    {
        $params['fields'] = $params['fields']
            ?? array_merge(Device::DEFAULT_DISPLAY_VALUES, ['model.name', 'vendor.name']);
        $params = $this->deviceServiceResolver->getInstance($user)->prepareDeviceListParams($params);

        $params = Device::handleStatusParams($params);

        if ($user->needToCheckUserGroup()) {
            $vehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($user);
            $params[self::ELASTIC_CASE_OR]['vehicle.id'][] = $vehicleIds;
            $params[self::ELASTIC_CASE_OR]['vehicle.id'][] = null;
        }

        $fields = $this->prepareElasticFields($params);

        return $this->deviceFinder->find($fields, $fields['_source'] ?? [], $paginated, $user, $iterator);
    }

    public function installDevice(array $params, Device $device, User $currentUser): Device
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $prevDevice = clone $device;
            $this->validateInstallDeviceFields($params, $currentUser);

            $vehicle = $this->em->getRepository(Vehicle::class)
                ->getVehicleByIdForInstall($currentUser, $params['vehicleId']);

            $odometer = $params['odometer'] ?? null;
            $deviceInstallation = $this->em->getRepository(DeviceInstallation::class)->findBy(
                ['device' => $device, 'uninstallDate' => null]
            );

            $vehicleInstallation = $this->em->getRepository(DeviceInstallation::class)->findBy(
                ['vehicle' => $vehicle, 'uninstallDate' => null,]
            );

            if (!$deviceInstallation && !$vehicleInstallation && $vehicle) {
                $deviceInstallation = new DeviceInstallation(
                    [
                        'vehicle' => $vehicle,
                        'device' => $device,
                        'installDate' => new \DateTime(),
                        'odometer' => $odometer
                    ]
                );
                $this->em->persist($deviceInstallation);

                $device->install($deviceInstallation);
                $device->setUpdatedAt(new \DateTime());     //for elasticsearch populate
                $device->setUpdatedBy($currentUser);
                $device->setTeam($vehicle->getTeam());

                if ($prevDevice->getTeamId() !== $device->getTeamId()) {
                    $this->eventDispatcher->dispatch(new DeviceChangedTeamEvent($device), DeviceChangedTeamEvent::NAME);
                }

                if ($device->getIsDeactivated()) {
                    $device->setIsDeactivated(false);
                    $this->eventDispatcher->dispatch(new DeviceDeactivatedEvent($device), DeviceDeactivatedEvent::NAME);
                }

                if (isset($params['files']) && $params['files']->get('files')) {
                    foreach ($params['files']->get('files') as $file) {
                        $fileEntity = $this->fileService->uploadDeviceInstallationFile($file, $currentUser);
                        $deviceInstallation->addFile($fileEntity);
                    }
                }

                if (!is_null($odometer) && $odometer > 0) {
                    $this->vehicleOdometerService
                        ->saveByVehicleAndDataAndUser($vehicle, ['odometer' => $odometer], $currentUser);
                    $this->deviceCommandService->updateDeviceOdometer($device, $currentUser, $odometer);
                }

                $this->em->flush();
                $this->eventDispatcher->dispatch(new DeviceInstalledEvent($device), DeviceInstalledEvent::NAME);
                $this->notificationDispatcher->dispatch(Event::DEVICE_OFFLINE, $device);
                $this->eventDispatcher->dispatch(new VehicleStatusChangedEvent($vehicle),
                    VehicleStatusChangedEvent::NAME);
            } else {
                if ($deviceInstallation) {
                    $errors['device'] = ['required' => $this->translator->trans('entities.device.device_installed')];
                } elseif ($vehicleInstallation) {
                    $errors['vehicle'] = ['required' => $this->translator->trans('entities.vehicle.vehicle_installed')];
                }

                throw (new ValidationException())->setErrors($errors);
            }

            $this->em->getConnection()->commit();

            return $device;
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @return object|null
     * @throws ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function uninstallDevice(array $params, User $currentUser)
    {
        $this->validateInstallDeviceFields($params, $currentUser);

        $vehicle = $this->em->getRepository(Vehicle::class)->find($params['vehicleId']);
        $device = $params['device'];
        $deviceInstallation = $this->em->getRepository(DeviceInstallation::class)->findOneBy(
            [
                'device' => $params['device'],
                'vehicle' => $vehicle,
                'uninstallDate' => null,
            ]
        );

        if ($params['isUnavailable'] ?? null) {
            $device->setIsUnavailable(true);
            if ($params['blockingMessage'] ?? null) {
                $device->setBlockingMessage($params['blockingMessage']);
            }

            $this->notificationDispatcher->dispatch(Event::DEVICE_UNAVAILABLE, $device);
        } else {
            $device->setStatus(Device::STATUS_IN_STOCK);
        }

        if ($deviceInstallation) {
            $this->eventDispatcher->dispatch(
                new DeviceUninstalledEvent($deviceInstallation, $currentUser), DeviceUninstalledEvent::NAME
            );
            $this->em->flush();
            $this->eventDispatcher->dispatch(new VehicleStatusChangedEvent($vehicle), VehicleStatusChangedEvent::NAME);

            if ($device->isInStock()) {
                $this->notificationDispatcher->dispatch(Event::DEVICE_IN_STOCK, $device);
            }
        }

        return $device;
    }

    /**
     * @param array $data
     * @param Device $device
     * @param User $currentUser
     * @return Device
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit(array $data, Device $device, User $currentUser): Device
    {
        $data = $this->validateDeviceFields($data, $currentUser, $device);
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $device = $this->handleEditFields($device, $data, $currentUser);

            $this->em->flush();

            $this->handleNotesFields($data, $device, $currentUser);

            $this->em->getConnection()->commit();

//            if ($device ?? null) {
//                $this->eventDispatcher->dispatch(new DeviceUpdatedEvent($device), DeviceUpdatedEvent::NAME);
//            }

            return $device;
        } catch (\Exception $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->getConnection()->rollback();
            }
            throw $e;
        }
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function removeDevice(Device $device, User $currentUser)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $device->setStatus(Device::STATUS_DELETED)
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($currentUser);

            $this->em->flush();
            $connection->commit();

            $this->eventDispatcher->dispatch(new DeviceDeletedEvent($device), DeviceDeletedEvent::NAME);
            $this->notificationDispatcher->dispatch(Event::DEVICE_DELETED, $device);
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    /**
     * @param Device $device
     * @return Device
     */
    public function restore(Device $device)
    {
        if ($device->getStatus() === Device::STATUS_DELETED) {
            $statusHistory = $this->entityHistoryService->listWithExclude(
                Device::class,
                $device->getId(),
                EntityHistoryTypes::AREA_STATUS,
                [Device::STATUS_DELETED]
            )->first();
            if ($statusHistory) {
                $device->setStatus($statusHistory->getPayload());
            } else {
                $device->setStatus(Device::STATUS_IN_STOCK);
            }

            $this->eventDispatcher->dispatch(new DeviceRestoredEvent($device), DeviceRestoredEvent::NAME);
        }

        return $device;
    }

    /**
     * @param $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @param $filter
     * @param User $user
     * @return array
     */
    public function getCoordinatesByDevice($deviceId, $dateFrom, $dateTo, $filter, User $user): array
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            throw new NotFoundHttpException($this->translator->trans('services.tracker.device_not_found'));
        }

        // todo consider company timezone
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);

        switch ($filter) {
            case 'hourly':
                $coordinates = $this->em->getRepository(TrackerHistory::class)
                    ->getHourlyCoordinatesByDeviceId($deviceId, $dateFrom, $dateTo);
                break;
            case 'daily':
                $timezone = $user->getTimezone();
                $coordinates = $this->em->getRepository(TrackerHistory::class)
                    ->getDailyCoordinatesByDeviceId(
                        $deviceId,
                        $dateFrom->setTimezone($timezone),
                        $dateTo->setTimezone($timezone),
                        $timezone
                    );
                break;
            case 'onePerHour':
                $timezone = $user->getTimezone();
                $coordinates = $this->em->getRepository(TrackerHistory::class)
                    ->getOnePerHourCoordinateByDeviceId(
                        $deviceId,
                        $dateFrom->setTimezone($timezone),
                        $dateTo->setTimezone($timezone),
                        $timezone
                    );
                break;
            default:
                //TODO implement timezone if need in the future
                $coordinates = $this->em->getRepository(TrackerHistory::class)
                    ->getCoordinatesByDeviceId($deviceId, $dateFrom, $dateTo);
        }

        return $coordinates;
    }

    /**
     * @param $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function getQueryCoordinatesByDevice($deviceId, $dateFrom, $dateTo): QueryBuilder
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            throw new NotFoundHttpException($this->translator->trans('services.tracker.device_not_found'));
        }

        // todo consider company timezone
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);

        return $this->em->getRepository(TrackerHistory::class)
            ->getQueryCoordinatesByDeviceId($deviceId, $dateFrom, $dateTo);
    }

    /**
     * @param int $deviceId
     * @param $startDate
     * @param bool|null $excludeEqual
     * @return Route|null
     * @throws \Doctrine\ORM\Exception\NotSupported
     */
    public function getLastRouteStartedFromDate(int $deviceId, $startDate, ?bool $excludeEqual = false): ?Route
    {
        return $this->em->getRepository(Route::class)
            ->getLastRouteStartedFromDate($deviceId, $startDate, $excludeEqual);
    }

    /**
     * @param string|null $deviceImei
     * @param string $regNo
     * @return array
     */
    public function getDeviceInstallation(?string $deviceImei = null, ?string $regNo = null, ?User $currentUser = null)
    {
        $team = $currentUser->isInClientTeam() ? $currentUser->getTeam() : null;
        if ($deviceImei || $regNo) {
            return $this->em->getRepository(DeviceInstallation::class)
                ->findByDeviceImeiOrVehicleRegNo($deviceImei, $regNo, $team, $currentUser);
        } else {
            return [];
        }
    }

    /**
     * @param array $data
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function parseImportFiles(array $data, User $user)
    {
        $connection = $this->em->getConnection();
        try {
            $result = [];
            if ($data['files']->get('files') ?? null) {
                foreach ($data['files']->get('files') as $file) {
                    /** @var File $fileEntity */
                    $fileEntity = $this->fileService->uploadDevicesVehiclesFile($file, $user);
                    $connection->beginTransaction();
                    $result = array_merge(
                        $result,
                        $this->devicesVehiclesImport->importData(
                            $fileEntity,
                            $user,
                            $this->vehicleObjectPersister,
                            $this->deviceObjectPersister
                        )
                    );
                    $connection->rollBack();
                }
            }
            return [
                'file' => isset($fileEntity) && $fileEntity ? $fileEntity->toArray() : [],
                'data' => $result
            ];
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function parseImportFilesVehiclesDrivers(
        array $data,
        DevicesVehiclesDriverImport $devicesVehiclesDriverImport,
        User $user,
    ) {
        $connection = $this->em->getConnection();

        try {
            $devicesVehiclesDriverImport->forSave = boolval($data['save'] ?? null);
            $result = [];

            if ($data['files']->get('files') ?? null) {
                foreach ($data['files']->get('files') as $file) {
                    /** @var File $fileEntity */
                    $fileEntity = $this->fileService->uploadDevicesVehiclesFile($file, $user);
                    $connection->beginTransaction();
                    $result = array_merge(
                        $result,
                        $devicesVehiclesDriverImport->importData(
                            $fileEntity,
                            $user,
                            $data
                        )
                    );
                    $devicesVehiclesDriverImport->forSave ? $connection->commit() : $connection->rollBack();
                }
            }

            return [
                'file' => isset($fileEntity) && $fileEntity ? $fileEntity->toArray() : [],
                'data' => $result
            ];
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    /**
     * @param array $data
     * @param User $user
     * @return array
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function saveImportFiles(array $data, User $user)
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $result = [];
            if ($data['files'] ?? null) {
                $files = $this->em->getRepository(File::class)->findBy(['id' => $data['files']]);
                foreach ($files as $file) {
                    if ($file->getCreatedBy()->getId() === $user->getId()) {
                        $result = array_merge(
                            $result,
                            $this->devicesVehiclesImport->importFile($file, $user, $this->clientObjectPersister)
                        );
                    }
                }
            }
            $connection->commit();

            return [
                'file' => isset($file) && $file ? $file->toArray() : [],
                'data' => $result
            ];
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function getDeviceListExportData($params, User $user, $paginated = false)
    {
        $iterator = $this->deviceList($params, $user, $paginated, true);
        $response = new StreamedResponse();
        $response->setCallback(function () use ($params, $user, $iterator) {
            $handle = fopen('php://output', 'w');
            $header = false;
            foreach ($iterator as $device) {
                $data = $device->toExport($params['fields'] ?? [], $user);
                $data2 = $this->translateEntityArrayForExport([$data], $params['fields'], Device::class, $user);
                $this->em->clear();
                if (!$header) {
                    fputcsv($handle, $data2[0]);
                    $header = true;
                }

                fputcsv($handle, $data2[1]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    public function resellerDeviceList(array $params, User $user, Reseller $reseller, bool $paginated = true)
    {
        $params['fields'] = $params['fields']
            ?? array_merge(Device::DEFAULT_DISPLAY_VALUES, ['model.name', 'vendor.name']);

        $resellerClientTeamIds = $this->em->getRepository(Reseller::class)->getResellerClientTeams($reseller);
        $resellerClientTeamIds[] = $reseller->getTeam()->getId();
        $params['teamId'] = $resellerClientTeamIds;

        if (isset($params['status'])) {
            $params['status'] = $params['status'] === Device::STATUS_ALL ? Device::ALLOWED_STATUSES : $params['status'];
        } else {
            $params['status'] = Device::LIST_STATUSES;
        }

        $fields = $this->prepareElasticFields($params);

        return $this->deviceFinder->find($fields, $fields['_source'] ?? [], $paginated, $user);
    }

    public function getResellerDeviceListExportData($params, User $user, Reseller $reseller)
    {
        $devices = $this->resellerDeviceList($params, $user, $reseller, false);

        return $this->translateEntityArrayForExport($devices, $params['fields'], Device::class, $user);
    }

    public function updateDeviceInstallation(array $params, Device $device, User $currentUser): Device
    {
        if (!$device->getDeviceInstallation()) {
            return $device;
        }

        $installation = $device->getDeviceInstallation();
        $newInstallDate = Carbon::parse($params['installDate'])->setTimezone('UTC');

        if ($newInstallDate > new \DateTime() || $newInstallDate < $installation->getInstallDate()) {
            throw new \InvalidArgumentException('Wrong date value');
        }

        $installation->setInstallDate($newInstallDate);
        $this->em->getRepository(Route::class)
            ->removeDeviceRoutesByPeriod($device, null, $installation->getInstallDate());
        $this->em->flush();

        return $device;
    }

    /**
     * @param Device $device
     * @param Vehicle|null $vehicle
     * @param DeviceInstallation $deviceInstallation
     * @return void
     */
    public function clearDeviceCommandsDuringUninstall(
        Device $device,
        ?Vehicle $vehicle,
        DeviceInstallation $deviceInstallation
    ) {
        $this->deviceCommandService->clearDeviceOdometerCommands(
            $device,
            $vehicle,
            $deviceInstallation->getInstallDate(),
            $deviceInstallation->getUninstallDate()
        );
    }

    public function bulkContract(array $data, User $user): void
    {
        foreach ($data['deviceId'] as $deviceId) {
            $device = $this->em->getRepository(Device::class)->find($deviceId);
            if (!ClientService::checkTeamAccess($device->getTeam(), $user)) {
                throw new AccessDeniedException();
            }
            if (isset($data['contractId']) && $data['contractId']) {
                $device->setContractId($data['contractId']);
            }
            if (isset($data['contractFinishAt']) && $data['contractFinishAt']) {
                $device->setContractFinishAt(new \DateTime($data['contractFinishAt']));
            }
            if (isset($data['contractStartAt']) && $data['contractStartAt']) {
                $device->setContractStartAt(new \DateTime($data['contractStartAt']));
            }
        }
        $this->em->flush();
    }

    public function bulkOwnership(array $data, User $user): void
    {
        foreach ($data['deviceId'] as $deviceId) {
            $device = $this->em->getRepository(Device::class)->find($deviceId);
            if (!ClientService::checkTeamAccess($device->getTeam(), $user)) {
                throw new AccessDeniedException();
            }
            if (isset($data['ownership']) && $data['ownership']) {
                $device->setOwnership($data['ownership']);
            }
        }
        $this->em->flush();
    }

    /**
     * @param Device $device
     * @return array|null
     */
    public function getDeviceVideoData(
        Device $device,
    ): ?array {
        return $this->deviceStreamService->getVideoData($device);
    }

    /**
     * @param array $params
     * @param Device $device
     * @param Device $deviceNew
     * @param User $currentUser
     * @return DeviceReplacement
     * @throws ValidationException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function replaceDeviceToNewDevice(
        array $params,
        Device $device,
        Device $deviceNew,
        User $currentUser
    ): DeviceReplacement {
        if (!$device->getImei() || !$deviceNew->getImei()) {
            throw new ValidationException('Devices should have not empty imei');
        }

        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();
            $vehicle = $device->getVehicle();

            if ($vehicle) {
                $this->uninstallDevice(
                    [
                        'device' => $device,
                        'vehicleId' => $vehicle->getId(),
                        'updatedBy' => $currentUser
                    ],
                    $currentUser
                );
                // @todo odometer?
                $this->installDevice(
                    [
                        'vehicleId' => $vehicle->getId(),
                        'updatedBy' => $currentUser
                    ],
                    $deviceNew,
                    $currentUser
                );
            }

            $deviceReplacement = new DeviceReplacement($params);
            $deviceReplacement->setDeviceOld($device);
            $deviceReplacement->setDeviceNew($deviceNew);
            $deviceReplacement->setImeiOld($device->getImei());
            $deviceReplacement->setImeiNew($deviceNew->getImei());
            $deviceReplacement->setCreatedBy($currentUser);
            $deviceReplacement->setTeam($device->getTeam());
            $this->em->persist($deviceReplacement);
            $this->prepareDevicesDueToReplacement($device, $deviceNew);
            $this->em->flush();
            $this->createNotesDuringDeviceReplacement($device, $deviceNew, $currentUser);
            $connection->commit();
            $this->notificationDispatcher->dispatch(Event::DEVICE_REPLACED, $device);
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }

            throw $e;
        }

        return $deviceReplacement;
    }

    /**
     * @param array $params
     * @param User $currentUser
     * @return PaginationInterface
     */
    public function listReplacements(
        array $params,
        User $currentUser
    ): PaginationInterface {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $sort = isset($params['sort']) ? ltrim($params['sort'], ' -') : 'createdAt';
        $order = isset($params['sort']) && !str_starts_with($params['sort'], '-') ? Criteria::ASC : Criteria::DESC;
        $params = UserServiceHelper::handleTeamParams($params, $currentUser);
        if ($currentUser->isInResellerTeam()) {
            $params['teamId'] = $this->em->getRepository(Reseller::class)->getResellerClientTeams($currentUser->getReseller());
            $params['teamId'][] = $currentUser->getTeamId();
        }

        $query = $this->em->getRepository(DeviceReplacement::class)->getAllQuery($params, $sort, $order);
        $pagination = $this->paginator->paginate(
            $query,
            $page,
            ($limit == 0) ? 1 : $limit,
            ['sortFieldParameterName' => '~', 'wrap-queries' => true]
        );
        $pagination->setItems($this->formatNestedItemsToArray(
            $pagination->getItems(),
            DeviceReplacement::DEFAULT_DISPLAY_VALUES,
            $currentUser
        ));

        return $pagination;
    }

    /**
     * @param int $id
     * @return DeviceReplacement|null
     */
    public function getReplacementById(int $id)
    {
        return $this->em->getRepository(DeviceReplacement::class)->find($id);
    }

    /**
     * @param DeviceReplacement $deviceReplacement
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeDeviceReplacement(DeviceReplacement $deviceReplacement)
    {
        $this->em->remove($deviceReplacement);
        $this->em->flush();
    }

    /**
     * @param array $params
     * @param DeviceReplacement $deviceReplacement
     * @return DeviceReplacement
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDeviceReplacement(array $params, DeviceReplacement $deviceReplacement): DeviceReplacement
    {
        $deviceReplacement->setAttributes($params);

        if (isset($params['isReturned'])) {
            $deviceOld = $deviceReplacement->getDeviceOld();
            $deviceOld?->setStatus($params['isReturned'] ? Device::STATUS_RETURNED : Device::STATUS_IN_STOCK);
        }

        $this->em->flush();

        return $deviceReplacement;
    }

    /**
     * @param Device $device
     * @return bool
     * @throws \Exception
     */
    public function wakeupDevice(Device $device): bool
    {
        $result = match ($device->getVendorName()) {
            DeviceVendor::VENDOR_STREAMAX => $this->deviceStreamService->wakeupDevice($device),
            default => $this->deviceCommandService->wakeupDevice($device),
        };

        if (!$result) {
            throw new \Exception($this->translator->trans('entities.device.can_not_wakeup_device'));
        }

        return $result;
    }

    /**
     * @param Device $device
     * @param string $text
     * @return bool
     * @throws \Exception
     */
    public function sendTTSToDevice(Device $device, string $text): bool
    {
        return match ($device->getVendorName()) {
            DeviceVendor::VENDOR_STREAMAX => $this->deviceStreamService->sendTTSToDevice($device, $text),
            default => false,
        };
    }
}
