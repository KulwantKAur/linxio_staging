<?php

namespace App\Service\Device;

use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\DeviceSensor;
use App\Entity\DeviceSensorType;
use App\Entity\Route;
use App\Entity\Sensor;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistorySensor;
use App\Entity\User;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Enums\EntityFields;
use App\Events\DeviceSensor\DeviceSensorInstalledEvent;
use App\Events\DeviceSensor\DeviceSensorUninstalledEvent;
use App\Events\Sensor\SensorCreatedEvent;
use App\Events\Sensor\SensorUpdatedEvent;
use App\Report\Builder\Sensors\SensorReportHelper;
use App\Report\Builder\Sensors\TempByVehicleReportBuilder;
use App\Service\Client\ClientService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\User\UserServiceHelper;
use App\Util\ArrayHelper;
use App\Util\DateHelper;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeviceSensorService extends DeviceService
{
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'deviceId' => 'device.id',
        'sensorId' => 'sensor.id',
        'sensorBLEId' => 'sensor.sensorId',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'type' => 'sensor.typeLabel',
        'vendor' => 'vendorName',
        'label' => 'sensor.label',
        'imei' => 'device.imei',
        'regNo' => 'device.vehicle.regNo',
        'typeId' => 'sensor.typeId',
        'teamId' => 'teamId',
        'isDeleted' => 'isDeleted',
    ];

    private $emSlave;
    private $deviceSensorFinder;
    private $eventDispatcher;
    protected $validator;
    protected $em;
    protected $deviceCommandService;
    protected $translator;
    protected $paginator;

    /**
     * DeviceSensorService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param ValidatorInterface $validator
     * @param DeviceCommandService $deviceCommandService
     * @param TransformedFinder $deviceSensorFinder
     * @param SlaveEntityManager $emSlave
     * @param EventDispatcherInterface $eventDispatcher
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        ValidatorInterface $validator,
        DeviceCommandService $deviceCommandService,
        TransformedFinder $deviceSensorFinder,
        SlaveEntityManager $emSlave,
        EventDispatcherInterface $eventDispatcher,
        PaginatorInterface $paginator
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->deviceCommandService = $deviceCommandService;
        $this->deviceSensorFinder = new ElasticSearch($deviceSensorFinder);
        $this->emSlave = $emSlave;
        $this->eventDispatcher = $eventDispatcher;
        $this->paginator = $paginator;
    }

    /**
     * @param int $id
     * @return object|DeviceSensor|null
     */
    public function getDeviceSensorById(int $id)
    {
        return $this->em->getRepository(DeviceSensor::class)->find($id);
    }

    /**
     * @param User $user
     * @param array $params
     * @param bool $paginated
     * @return array
     * @throws \Elastica\Exception\ElasticsearchException
     */
    public function listDeviceSensor(User $user, array $params, bool $paginated = true): array
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        $params['fields'] = array_merge(DeviceSensor::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
        $params = self::handleStatusParams($params);
        $fields = $this->prepareElasticFields($params);

        return $this->deviceSensorFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param Device $device
     * @param array $sensorData
     * @param User $currentUser
     * @return DeviceSensor
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createDeviceSensor(Device $device, array $sensorData, User $currentUser): DeviceSensor
    {
        if (!ClientService::checkTeamAccess($device->getTeam(), $currentUser)) {
            throw new AccessDeniedHttpException('');
        }

        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();
            $sensor = $this->addSensorFromDevice($sensorData, $device, $currentUser);
            $deviceSensor = $this->addDeviceSensor($device, $sensor, $currentUser);
            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->eventDispatcher->dispatch(new SensorCreatedEvent($sensor), SensorCreatedEvent::NAME);
            $this->eventDispatcher
                ->dispatch(new DeviceSensorInstalledEvent($deviceSensor), DeviceSensorInstalledEvent::NAME);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $deviceSensor;
    }

    /**
     * @param Sensor $sensor
     * @param array $data
     * @param User $currentUser
     * @return Sensor
     * @throws \Exception
     */
    public function updateSensor(Sensor $sensor, array $data, User $currentUser): Sensor
    {
        try {
            if (isset($data['sensorId'])) {
                $sensor->setSensorId($data['sensorId']);
            }
            if (isset($data['type'])) {
                $sensorType = $this->em->getRepository(DeviceSensorType::class)->find($data['type']);

                if (!$sensorType) {
                    throw new NotFoundHttpException(
                        $this->translator->trans('entities.device_sensor_type.id_does_not_exist')
                    );
                }

                $sensor->setType($sensorType);
            }
            if (isset($data['label'])) {
                $sensor->setLabel($data['label']);
            }
            if (isset($data['teamId'])) {
                if (!$currentUser->isInAdminTeam()) {
                    throw new AccessDeniedException($this->translator->trans('entities.sensor.deny_to_change_team'));
                }

                $team = $this->em->getRepository(Team::class)->find($data['teamId']);

                if (!$team) {
                    throw new NotFoundHttpException(
                        $this->translator->trans('entities.sensor.team_id_does_not_exist')
                    );
                }
                if (!$sensor->getDeviceSensors()->isEmpty()
                    && ($sensor->getLastDeviceSensorWithoutCondition()->getTeamId() != $data['teamId'])
                ) {
                    throw new AccessDeniedHttpException(
                        $this->translator->trans('entities.device_sensor.sensor_has_device_from_another_team')
                    );
                }

                $sensor->setTeam($team);
            } else {
                $sensor->setTeam($currentUser->getTeam());
            }

            $sensor->setUpdatedAt(new \DateTime());
            $sensor->setUpdatedBy($currentUser);
            $this->validate($this->validator, $sensor);

            $this->em->flush();
            $this->eventDispatcher->dispatch(new SensorUpdatedEvent($sensor), SensorUpdatedEvent::NAME);

            return $sensor;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Sensor $sensor
     * @param array $data
     * @param User $currentUser
     * @return Sensor
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function updateSensorAndDependencies(Sensor $sensor, array $data, User $currentUser): Sensor
    {
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();
            $oldSensorId = $sensor->getSensorId();
            $sensor = $this->updateSensor($sensor, $data, $currentUser);
            $this->updateSensorDependenciesByType($oldSensorId, $sensor, $data, $currentUser);

            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->eventDispatcher->dispatch(new SensorUpdatedEvent($sensor), SensorUpdatedEvent::NAME);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $sensor;
    }

    /**
     * @param string $oldSensorId
     * @param Sensor $sensor
     * @param array $data
     * @param User $currentUser
     * @throws \Exception
     */
    private function updateSensorDependenciesByType(
        string $oldSensorId,
        Sensor $sensor,
        array $data,
        User $currentUser
    ) {
        switch ($sensor->getTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
                $this->updateTempAndHumiditySensorDependencies($oldSensorId, $sensor, $data, $currentUser);
                break;
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                $this->updateTrackingBeaconSensorDependencies($oldSensorId, $sensor, $data, $currentUser);
                break;
            case DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE:
            default:
                break;
        }
    }

    /**
     * @param string $oldSensorId
     * @param Sensor $sensor
     * @param array $data
     * @param User $currentUser
     * @throws \Exception
     */
    private function updateTempAndHumiditySensorDependencies(
        string $oldSensorId,
        Sensor $sensor,
        array $data,
        User $currentUser
    ) {
        if (isset($data['sensorId']) && $oldSensorId != $data['sensorId']) {
            $deviceSensors = $sensor->getDeviceSensors();

            foreach ($deviceSensors as $deviceSensor) {
                $this->deviceCommandService->removeDeviceWithNewTempAndHumiditySensorId(
                    $deviceSensor->getDevice(),
                    $currentUser,
                    $oldSensorId
                );
                $this->deviceCommandService->updateDeviceWithNewTempAndHumiditySensorId(
                    $deviceSensor->getDevice(), $currentUser, $data['sensorId']
                );
            }
        }
    }

    /**
     * @param string $oldSensorId
     * @param Sensor $sensor
     * @param array $data
     * @param User $currentUser
     * @throws \Exception
     */
    private function updateTrackingBeaconSensorDependencies(
        string $oldSensorId,
        Sensor $sensor,
        array $data,
        User $currentUser
    ) {
        if (isset($data['sensorId']) && $oldSensorId != $data['sensorId']) {
            $deviceSensors = $sensor->getDeviceSensors();

            foreach ($deviceSensors as $deviceSensor) {
                $this->deviceCommandService->removeDeviceWithNewTrackingBeaconSensorId(
                    $deviceSensor->getDevice(),
                    $currentUser,
                    $oldSensorId
                );
                $this->deviceCommandService->updateDeviceWithNewTrackingBeaconSensorId(
                    $deviceSensor->getDevice(), $currentUser, $data['sensorId']
                );
            }
        }
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @param array $data
     * @param User $currentUser
     * @return DeviceSensor
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDeviceSensor(DeviceSensor $deviceSensor, array $data, User $currentUser): DeviceSensor
    {
        $this->updateSensorAndDependencies($deviceSensor->getSensor(), $data, $currentUser);
        $deviceSensor->setUpdatedAt(new \DateTime());
        $deviceSensor->setUpdatedBy($currentUser);
        $this->em->flush();

        return $deviceSensor;
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @param User $currentUser
     * @return DeviceSensor
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function deleteDeviceSensor(DeviceSensor $deviceSensor, User $currentUser): DeviceSensor
    {
        if (!ClientService::checkTeamAccess($deviceSensor->getDevice()->getTeam(), $currentUser)) {
            throw new AccessDeniedHttpException('');
        }

        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();
            $device = $deviceSensor->getDevice();
            $device->setUpdatedAt(new \DateTime());
            $device->setUpdatedBy($currentUser);
            $this->deviceCommandService
                ->removeDeviceWithNewTempAndHumiditySensorId($device, $currentUser, $deviceSensor->getSensorId());
            $deviceSensor->setAsDeleted();
            $deviceSensor->setUpdatedAt(new \DateTime());
            $deviceSensor->setUpdatedBy($currentUser);

            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->eventDispatcher
                ->dispatch(new DeviceSensorUninstalledEvent($deviceSensor), DeviceSensorUninstalledEvent::NAME);
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $deviceSensor;
    }

    /**
     * @param array $sensorData
     * @param Device $device
     * @param User $currentUser
     * @return Sensor
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function addSensorFromDevice(array $sensorData, Device $device, User $currentUser): Sensor
    {
        if (!isset($sensorData['sensorId'])) {
            throw new \InvalidArgumentException(
                $this->translator->trans('entities.sensor.sensor_id_required')
            );
        }

        $sensor = $this->em->getRepository(Sensor::class)->getBySensorId($sensorData['sensorId']);

        if (!$sensor) {
            $sensor = new Sensor();
            $sensor->setCreatedBy($currentUser);
            $sensor->setTeam($device->getTeam());
            $this->em->persist($sensor);
        } else {
            if ($sensor->getDeviceSensors() && $sensor->getLastDeviceSensor()) {
                if ($sensor->getLastDeviceSensor()->getTeamId() != $device->getTeamId()) {
                    throw new AccessDeniedHttpException(
                        $this->translator->trans('entities.device_sensor.sensor_has_device_from_another_team')
                    );
                }
            } else {
                $sensor->setTeam($device->getTeam());
            }
        }

        $sensor->setSensorId($sensorData['sensorId'])
            ->setLabel($sensorData['label'] ?? null);

        $sensorType = $this->em->getRepository(DeviceSensorType::class)->find($sensorData['type']);

        if (!$sensorType) {
            throw new NotFoundHttpException(
                $this->translator->trans('entities.device_sensor_type.id_does_not_exist')
            );
        }

        $sensor->setType($sensorType);

        return $sensor;
    }

    /**
     * @param Device $device
     * @param Sensor $sensor
     * @param User $currentUser
     * @return DeviceSensor
     * @throws \App\Exceptions\ValidationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addDeviceSensor(Device $device, Sensor $sensor, User $currentUser): DeviceSensor
    {
        $deviceSensor = $this->em->getRepository(DeviceSensor::class)->getBySensorAndDevice($sensor, $device);

        if ($deviceSensor && $deviceSensor->isDeleted()) {
            $deviceSensor->setAsNotDeleted();
            $deviceSensor->setUpdatedAt(new \DateTime());
            $deviceSensor->setUpdatedBy($currentUser);
        } else {
            $deviceSensor = new DeviceSensor();
            $deviceSensor->setDevice($device);
            $deviceSensor->setSensor($sensor);
            $deviceSensor->setCreatedBy($currentUser);
            $deviceSensor->setTeam($device->getTeam());
        }

        $this->validate($this->validator, $deviceSensor);

        $device->addTrackerSensor($deviceSensor);
        $device->setUpdatedAt(new \DateTime());
        $device->setUpdatedBy($currentUser);

        switch ($sensor->getTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                $this->deviceCommandService->updateDeviceWithNewTempAndHumiditySensorId(
                    $device, $currentUser, $sensor->getSensorId()
                );
                break;
            default:
                break;
        }

        $this->em->persist($deviceSensor);

        return $deviceSensor;
    }

    /**
     * @param Device $device
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @return array|null
     * @throws \Exception
     */
    public function getSensorsHistoryByDeviceAndRange(Device $device, $startDate = null, $endDate = null): ?array
    {
        $startDate = $startDate ? self::parseDateToUTC($startDate) : Carbon::now();
        $endDate = $endDate ? self::parseDateToUTC($endDate) : (new Carbon())->subHours(24);
        $deviceSensorsCollection = $device->getTrackerSensors();
        $data = [];

        /** @var DeviceSensor $deviceSensor */
        foreach ($deviceSensorsCollection as $deviceSensor) {
            $sensorHistoriesCollection = $deviceSensor->getTrackerSensorsHistoriesCollectionByRange($startDate,
                $endDate);
            $deviceSensor->setTrackerHistoriesSensor($sensorHistoriesCollection);
            $deviceSensorArray = $deviceSensor
                ->toArray(array_merge(DeviceSensor::SENSOR_HISTORY_DISPLAY_VALUES, ['trackerHistoriesSensor']));
            $data[] = $deviceSensorArray;
        }

        return $data;
    }

    /**
     * @param array $sensorHistories
     * @return array
     */
    public function updateLastPositionForSensorHistories(array $sensorHistories): array
    {
        return array_map(function (TrackerHistorySensor $sensorHistory) {
            $lastRoute = $this->em->getRepository(Route::class)
                ->getLastRouteStartedFromDate($sensorHistory->getDeviceId(), $sensorHistory->getOccurredAt());

            if ($lastRoute) {
                $sensorHistory->setLastPositionFromRoute($lastRoute, $this->translator);
            }

            return $sensorHistory;
        }, $sensorHistories);
    }

    /**
     * @return array
     */
    public function getAvailableDeviceSensorTypes()
    {
        $sensorTypes = $this->em->getRepository(DeviceSensorType::class)->findBy(['isAvailable' => true]);

        return array_map(
            function (DeviceSensorType $deviceSensorType) {
                return $deviceSensorType->toArray();
            },
            $sensorTypes
        );
    }

    /**
     * @param DeviceSensor $deviceSensor
     * @param string $sort
     * @param string $order
     * @param null $startDate
     * @param null $endDate
     * @return \Doctrine\ORM\Query
     */
    public function getDeviceSensorHistoryByRangeQuery(
        DeviceSensor $deviceSensor,
        string $sort,
        string $order,
        $startDate = null,
        $endDate = null
    ) {
        $startDate = $startDate ? self::parseDateToUTC($startDate) : Carbon::now();
        $endDate = $endDate ? self::parseDateToUTC($endDate) : (new Carbon())->subHours(24);

        return $this->em->getRepository(TrackerHistorySensor::class)
            ->getByDeviceSensorIdQuery($deviceSensor->getId(), $sort, $order, $startDate, $endDate);
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param DeviceSensor $deviceSensor
     * @return PaginationInterface
     */
    public function getHistoryJSON(
        Request $request,
        DeviceSensor $deviceSensor
    ): PaginationInterface {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $params = $request->query->all();
        $sort = isset($params['sort']) ? ltrim($params['sort'], ' -') : 'occurredAt';
        $order = isset($params['sort']) && strpos($params['sort'], '-') === 0 ? Criteria::DESC : Criteria::ASC;
        $sensorHistoryQuery = $this
            ->getDeviceSensorHistoryByRangeQuery($deviceSensor, $sort, $order, $startDate, $endDate);
        $pagination = $this->paginator->paginate(
            $sensorHistoryQuery,
            $page,
            ($limit == 0) ? 1 : $limit,
            ['sortFieldParameterName' => '~']
        );

        if ($limit == 0) {
            $pagination = $this->paginator->paginate(
                $sensorHistoryQuery,
                1,
                $pagination->getTotalItemCount(),
                ['sortFieldParameterName' => '~']
            );
        }

        $pagination->setItems($this->formatNestedItemsToArray(
            $this->updateLastPositionForSensorHistories($pagination->getItems()),
            array_merge(TrackerHistorySensor::DEFAULT_DISPLAY_VALUES, ['lastPosition'])
        ));

        return $pagination;
    }

    /**
     * @param Request $request
     * @param DeviceSensor $deviceSensor
     * @param User $user
     * @return array
     * @throws \ReflectionException
     */
    public function getHistoryCSV(Request $request, DeviceSensor $deviceSensor, User $user): array
    {
        $sensorTypeId = $request->query->get('sensorType', null);
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $params = $request->query->all();
        $sort = isset($params['sort']) ? ltrim($params['sort'], ' -') : 'occurredAt';
        $order = isset($params['sort']) && strpos($params['sort'], '-') === 0 ? Criteria::DESC : Criteria::ASC;

        if (!$sensorTypeId) {
            throw new \InvalidArgumentException(
                $this->translator->trans('entities.device_sensor_type.required_sensor_type')
            );
        }

        $sensorType = $this->em->getRepository(DeviceSensorType::class)->find($sensorTypeId);

        if (!$sensorType) {
            throw new NotFoundHttpException(
                $this->translator->trans('entities.device_sensor_type.id_does_not_exist', ['%id%' => $sensorTypeId])
            );
        }

        $sensorHistoriesQuery = $this
            ->getDeviceSensorHistoryByRangeQuery($deviceSensor, $sort, $order, $startDate, $endDate);
        $sensorsHistories = $this->updateLastPositionForSensorHistories($sensorHistoriesQuery->getResult());

        return $this->translateEntityArrayForExport(
            $sensorsHistories, $params['fields'] ?? [], TrackerHistorySensor::class
        );
    }

    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public function getParamsForVehicleTempAndHumiditySensorListReport(array $params, User $user): array
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        $params['status'] = BaseEntity::STATUS_ALL;
        $vehicleIds = $this->getTempAndHumiditySensorListReportVehicleIds($params, $user);
        unset($params['groups']);
        unset($params['depot']);

        return array_merge($params, ['id' => $vehicleIds]);
    }

    public function getTempAndHumiditySensorListReportVehicleIds(array $params, User $user): array
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        $params['status'] = BaseEntity::STATUS_ALL;
        return array_column(
            $this->emSlave->getRepository(DeviceSensor::class)
                ->getDeviceTempAndHumiditySensorsSummaryByVehicle(
                    SensorReportHelper::prepareFieldsForReportByVehicle($params), true
                )
                ->execute()->fetchAll()
            ,
            'id'
        );
    }


    public function getDeviceTempAndHumiditySensorReportQueryByVehiclePdf(array $params, User $user)
    {
        $vehicleIds = $this->getTempAndHumiditySensorListReportVehicleIds($params, $user);
        $result = [];
        unset($params['vehicleIds']);

        foreach ($vehicleIds as $vehicleId) {
            /** @var Vehicle $vehicle */
            $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
            if (!$vehicle) {
                continue;
            }
            $query = TempByVehicleReportBuilder::generateDate(
                array_merge($params, ['vehicleId' => $vehicleId]), $user, $this->emSlave
            );

            $result[] = [
                'vehicle' => $vehicle->toArray(Vehicle::REPORT_VALUES),
                'data' => $this->prepareExportData($query, $params, $user)
            ];
        }

        return $result;
    }

    public function getDeviceTempAndHumiditySensorReportQueryBySensorPdf(array $params, User $user)
    {
        $sensorIds = $this->getParamsForTempAndHumiditySensorListReport($params, $user)['id'];
        $result = [];
        unset($params['vehicleIds']);
        foreach ($sensorIds as $sensorId) {
            /** @var DeviceSensor $deviceSensor */
            $sensor = $this->em->getRepository(Sensor::class)->find($sensorId);
            if (!$sensor) {
                continue;
            }
            $query = $this->getDeviceTempAndHumiditySensorReportQueryBySensor(
                array_merge($params, ['sensorId' => $sensor]), $user
            );

            $result[] = [
                'sensor' => $sensor->toArray(Sensor::DEFAULT_EXPORT_VALUES),
                'data' => $this->prepareExportData($query, $params, $user)
            ];
        }

        return $result;
    }

    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public function getParamsForTempAndHumiditySensorListReport(array $params, User $user): array
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        if (isset($params['sensorBLEId'])) {
            $params['sensorBLEId'] = StringHelper::macToString($params['sensorBLEId']);
        }

        $params['showDeleted'] = true;
        $deviceSensorIds = array_column(
            $this->emSlave->getRepository(DeviceSensor::class)
                ->getDeviceTempAndHumiditySensorsSummaryBySensor(SensorReportHelper::prepareFieldsForReportBySensor($params),
                    true)
                ->execute()->fetchAll()
            ,
            'id'
        );
        unset($params['groups']);
        unset($params['depot']);

        return array_merge($params, ['id' => $deviceSensorIds]);
    }

    /**
     * @param array $params
     * @param User $user
     * @return QueryBuilder
     */
    public function getDeviceTempAndHumiditySensorReportQueryBySensor(array $params, User $user): QueryBuilder
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);

        return $this->emSlave->getRepository(DeviceSensor::class)
            ->getDeviceTempAndHumiditySensorsSummaryBySensor(SensorReportHelper::prepareFieldsForReportBySensor($params));
    }

    /**
     * @param array $data
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function formatReportDates(array $data, User $user): array
    {
        $timeZone = $user->getTimezone();
        $dateFormat = $user->getDateFormatSettingConverted();

        $dates = ['startedAt', 'finishedAt', 'occurredAt'];

//        foreach ($dates as $date) {
//            if (isset($data[$date])) {
//                $data[$date] = DateHelper::formatDate(
//                    $data[$date],
//                    BaseEntity::EXPORT_DATE_FORMAT,
//                    $timeZone
//                );
//            }
//        }

        if (isset($data[EntityFields::OCCURRED_AT])) {
            $date = DateHelper::formatDate($data[EntityFields::OCCURRED_AT], $dateFormat, $timeZone);
            $time = DateHelper::formatDate($data[EntityFields::OCCURRED_AT], BaseEntity::EXPORT_TIME_FORMAT, $timeZone);
            $data = ArrayHelper::arraySpliceAfterKey($data, EntityFields::OCCURRED_AT,
                [EntityFields::OCCURRED_AT_DATE => $date, EntityFields::OCCURRED_AT_TIME => $time]);
        }

        return $data;
    }

    public function prepareExportData($query, $params, User $user)
    {
        $results = [];
        $sensors = $this->replaceNestedArrayKeysToCamelCase($query->execute()->fetchAll());
        $fields = $params['fields'] ?? [];

        foreach ($sensors as $sensor) {
            $sensor = $this->formatReportDates($sensor, $user);
            if (in_array(EntityFields::OCCURRED_AT, $fields)) {
                $fields[] = EntityFields::OCCURRED_AT_DATE;
                $fields[] = EntityFields::OCCURRED_AT_TIME;
                $fields = ArrayHelper::removeFromArrayByValue(EntityFields::OCCURRED_AT, $fields);
            }

            $results[] = $sensor;
        }

        return $this->translateEntityArrayForExport($results, $fields, TrackerHistorySensor::class);
    }

    public function getTemperatureBySensorExportData($params, User $user)
    {
        return $this->prepareExportData(
            $this->getDeviceTempAndHumiditySensorReportQueryBySensor($params, $user), $params, $user);
    }

    public function getDeviceSensorListExportData($params, User $user, $paginated = false)
    {
        $deviceSensors = $this->listDeviceSensor($user, $params, $paginated);

        return $this->translateEntityArrayForExport(
            $deviceSensors, $params['fields'] ?? [], DeviceSensor::class, $user
        );
    }

    /**
     * @param array $params
     * @return array
     */
    public static function handleStatusParams(array $params)
    {
        if (!isset($params['showDeleted'])) {
            $params['isDeleted'] = false;
        }

        return $params;
    }
}
