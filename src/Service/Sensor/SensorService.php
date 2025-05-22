<?php

namespace App\Service\Sensor;

use App\Entity\DeviceSensor;
use App\Entity\DeviceSensorType;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Sensor;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Events\DeviceSensor\DeviceSensorInstalledEvent;
use App\Events\Sensor\SensorCreatedEvent;
use App\Events\Sensor\SensorDeletedEvent;
use App\Report\Builder\Sensors\TempByVehicleReportBuilder;
use App\Repository\Tracker\TrackerHistoryRepository;
use App\Service\BaseService;
use App\Service\Client\ClientService;
use App\Service\Device\DeviceCommandService;
use App\Service\Device\DeviceSensorService;
use App\Service\Device\DeviceService;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\User\UserServiceHelper;
use App\Util\StringHelper;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SensorService extends BaseService
{
    public const ELASTIC_SIMPLE_FIELDS = [
        'id' => 'id',
        'sensorId' => 'sensorId',
        'sensorBLEId' => 'sensorId',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'type' => 'typeName',
        'label' => 'label',
        'typeId' => 'typeId',
        'typeIds' => 'typeId',
        'team' => 'team',
        'teamId' => 'teamId',
        'lastDataReceived' => 'lastDataReceived',
        'client' => 'client.name',
        'systemStatus' => 'systemStatus'
    ];

    public const ELASTIC_FULL_SEARCH_FIELDS = [
        'sensorId',
        'label'
    ];

    private $sensorFinder;
    private $deviceService;
    private $deviceSensorService;
    private $deviceCommandService;
    private $eventDispatcher;
    protected $validator;
    protected $translator;
    protected $em;

    /**
     * @param Sensor $sensor
     * @param User $currentUser
     * @throws \Exception
     */
    private function deleteSensorDependenciesByType(
        Sensor $sensor,
        User $currentUser
    ) {
        switch ($sensor->getTypeName()) {
            case DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE:
                $this->deleteTempAndHumiditySensorDependencies($sensor, $currentUser);
                break;
            case DeviceSensorType::TOPFLYTECH_TRACKING_BEACON_TYPE:
                $this->deleteTrackingBeaconSensorDependencies($sensor, $currentUser);
                break;
            case DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE:
                $this->deleteIButtonSensorDependencies($sensor);
                break;
            default:
                break;
        }
    }

    /**
     * @param Sensor $sensor
     * @param User $currentUser
     * @throws \Exception
     */
    private function deleteTempAndHumiditySensorDependencies(
        Sensor $sensor,
        User $currentUser
    ) {
        $deviceSensors = $sensor->getDeviceSensors();

        foreach ($deviceSensors as $deviceSensor) {
            $device = $deviceSensor->getDevice();
            $this->deviceCommandService
                ->removeDeviceWithNewTempAndHumiditySensorId($device, $currentUser, $sensor->getSensorId());
            $this->em->remove($deviceSensor);
        }
    }

    /**
     * @param Sensor $sensor
     * @throws \Exception
     */
    private function deleteIButtonSensorDependencies(
        Sensor $sensor
    ) {
        $deviceSensors = $sensor->getDeviceSensors();

        foreach ($deviceSensors as $deviceSensor) {
            $this->em->remove($deviceSensor);
        }
    }

    /**
     * @param Sensor $sensor
     * @param User $currentUser
     * @throws \Exception
     */
    private function deleteTrackingBeaconSensorDependencies(
        Sensor $sensor,
        User $currentUser
    ) {
        $deviceSensors = $sensor->getDeviceSensors();

        foreach ($deviceSensors as $deviceSensor) {
            $device = $deviceSensor->getDevice();
            $this->deviceCommandService
                ->removeDeviceWithNewTrackingBeaconSensorId($device, $currentUser, $sensor->getSensorId());
            $this->em->remove($deviceSensor);
        }
    }

    /**
     * @param array $params
     * @return array
     */
    private function prepareParamsForElasticSearch(array $params): array
    {
        if (array_key_exists('sensorId', $params)) {
            $params['sensorId'] = StringHelper::macToString($params['sensorId']);
        }

        if (array_key_exists('sensorBLEId', $params)) {
            $params['sensorId'] = StringHelper::macToString($params['sensorBLEId']);
            unset($params['sensorBLEId']);
        }

        return $params;
    }

    /**
     * DeviceSensorService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param ValidatorInterface $validator
     * @param TransformedFinder $sensorFinder
     * @param DeviceService $deviceService
     * @param DeviceSensorService $deviceSensorService
     * @param DeviceCommandService $deviceCommandService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        ValidatorInterface $validator,
        TransformedFinder $sensorFinder,
        DeviceService $deviceService,
        DeviceSensorService $deviceSensorService,
        DeviceCommandService $deviceCommandService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->sensorFinder = new ElasticSearch($sensorFinder);
        $this->deviceService = $deviceService;
        $this->deviceSensorService = $deviceSensorService;
        $this->deviceCommandService = $deviceCommandService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $id
     * @return object|Sensor|null
     */
    public function getSensorById(int $id)
    {
        return $this->em->getRepository(Sensor::class)->find($id);
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return array
     * @throws \Elastica\Exception\ElasticsearchException
     */
    public function listSensor(array $params, User $user, $paginated = true): array
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        $params = Sensor::handleStatusParams($params, 'systemStatus');
        $params['fields'] = array_merge(Sensor::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
        $params = $this->prepareParamsForElasticSearch($params);
        $fields = $this->prepareElasticFields($params);

        return $this->sensorFinder->find($fields, $fields['_source'] ?? [], $paginated);
    }

    /**
     * @param array $sensorData
     * @param User $currentUser
     * @return Sensor
     * @throws \Exception
     */
    public function createSensor(array $sensorData, User $currentUser): Sensor
    {
        try {
            $sensor = new Sensor();
            $sensor->setSensorId($sensorData['sensorId'])
                ->setLabel($sensorData['label'] ?? null)
                ->setCreatedBy($currentUser);
            $sensorType = $this->em->getRepository(DeviceSensorType::class)->find($sensorData['type']);

            if (!$sensorType) {
                throw new NotFoundHttpException(
                    $this->translator->trans('entities.device_sensor_type.id_does_not_exist')
                );
            }

            if (isset($sensorData['teamId'])) {
                if (!$currentUser->isInAdminTeam()) {
                    throw new AccessDeniedException($this->translator->trans('entities.sensor.deny_to_change_team'));
                }

                $team = $this->em->getRepository(Team::class)->find($sensorData['teamId']);

                if (!$team) {
                    throw new NotFoundHttpException(
                        $this->translator->trans('entities.sensor.team_id_does_not_exist')
                    );
                }

                $sensor->setTeam($team);
            } else {
                $sensor->setTeam($currentUser->getTeam());
            }

            $sensor->setType($sensorType);
            $this->validate($this->validator, $sensor);
            $this->em->persist($sensor);
            $this->em->flush();
            $this->eventDispatcher->dispatch(new SensorCreatedEvent($sensor), SensorCreatedEvent::NAME);
        } catch (\Exception $e) {
            throw $e;
        }

        return $sensor;
    }

    /**
     * @param Sensor $sensor
     * @return Sensor
     * @throws \Exception
     */
    public function deleteSensor(Sensor $sensor): Sensor
    {
        try {
            $sensor->getDriver()?->setDriverSensorId(null);
            $sensor->getAsset()?->setSensor(null);
            $sensor->setSystemStatus(Sensor::STATUS_ARCHIVE);
            $this->em->flush();
            $this->eventDispatcher->dispatch(new SensorDeletedEvent($sensor), SensorDeletedEvent::NAME);
        } catch (\Exception $e) {
            throw $e;
        }

        return $sensor;
    }

    public function restoreSensor(Sensor $sensor, User $user): Sensor
    {
        try {
            $sensor->setSystemStatus(Sensor::STATUS_ACTIVE);
            $sensor->setUpdatedBy($user);
            $sensor->setUpdatedAt(new \DateTime());
            $this->em->flush();
        } catch (\Exception $e) {
            throw $e;
        }

        return $sensor;
    }

    public function deleteSensorAndDependencies(Sensor $sensor, User $currentUser): Sensor
    {
//        $connection = $this->em->getConnection();

        try {
//            $connection->beginTransaction();
            $this->deleteSensorDependenciesByType($sensor, $currentUser);
            $this->deleteSensor($sensor);
            $this->em->flush();
//            $connection->commit();
        } catch (\Exception $e) {
//            if ($connection->isTransactionActive()) {
//                $connection->rollback();
//            }

            throw $e;
        }

        return $sensor;
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

    public function installOnDevice(Sensor $sensor, array $params, User $currentUser): DeviceSensor
    {
        $connection = $this->em->getConnection();

        try {
            $connection->beginTransaction();

            if (!isset($params['deviceId'])) {
                throw new \InvalidArgumentException(
                    $this->translator->trans('entities.device_sensor.required_device_id')
                );
            }

            $device = $this->deviceService->getById($params['deviceId'], $currentUser);

            if (!$device) {
                throw new NotFoundHttpException($this->translator->trans('services.tracker.device_not_found'));
            }
            if (!ClientService::checkTeamAccess($device->getTeam(), $currentUser)) {
                throw new AccessDeniedHttpException('');
            }
            if (!$sensor->getDeviceSensors()->isEmpty()) {
                if ($sensor->getLastDeviceSensorWithoutCondition()->getTeamId() != $device->getTeamId()) {
                    throw new AccessDeniedHttpException(
                        $this->translator->trans('entities.device_sensor.sensor_has_device_from_another_team')
                    );
                }
            } else {
                $sensor->setTeam($device->getTeam());
            }

            $deviceSensor = $this->deviceSensorService->addDeviceSensor($device, $sensor, $currentUser);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }

            throw $e;
        }

        $this->eventDispatcher
            ->dispatch(new DeviceSensorInstalledEvent($deviceSensor), DeviceSensorInstalledEvent::NAME);

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
        return $this->deviceSensorService->updateSensor($sensor, $data, $currentUser);
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
        if (
            isset($data['sensorId'])
            && ($sensor->getTypeName() == DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE)
        ) {
            $type = isset($data['type'])
                ? $this->em->getRepository(DeviceSensorType::class)->find($data['type'])
                : null;

            if (!$type || ($type && $type->getName() == DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE)) {
                throw new UnprocessableEntityHttpException(
                    $this->translator->trans('entities.device_sensor_type.sensor_id_is_denied_to_change')
                );
            }
        }

        return $this->deviceSensorService->updateSensorAndDependencies($sensor, $data, $currentUser);
    }

    /**
     * @param string $sensorId
     * @param int $sensorTypeId
     * @param User $currentUser
     * @return Sensor|null|void
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function createSensorIfNotExists(string $sensorId, int $sensorTypeId, User $currentUser)
    {
        $sensor = $this->em->getRepository(Sensor::class)->getBySensorId($sensorId);

        if ($sensor) {
            return $sensor;
        }

        return $this->createSensor(
            ['sensorId' => $sensorId, 'type' => $sensorTypeId],
            $currentUser
        );
    }

    /**
     * @param string $sensorId
     * @param User $currentUser
     * @return Sensor|null|void
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function createTopflytechDriverSensorIdIfNotExists(string $sensorId, User $currentUser)
    {
        $sensorType = $this->em->getRepository(DeviceSensorType::class)
            ->findOneBy(['name' => DeviceSensorType::TOPFLYTECH_IBUTTON_TYPE]);

        if (!$sensorType) {
            return;
        }

        return $this->createSensorIfNotExists($sensorId, $sensorType->getId(), $currentUser);
    }

    /**
     * @todo check if it's ok with transaction in `src/AppBundle/EventListener/User/UserListener.php`
     */
    public function updateVehicleDevicesWithDriverSensorId(
        User $driver,
        User $currentUser,
        array $vehicles,
        ?string $oldDriverSensorId = null
    ) {
        $connection = $this->em->getConnection();

        try {
//            $connection->beginTransaction();
            $this->removeDriverSensorIdFromVehicleDevices($oldDriverSensorId, $currentUser, $vehicles);
            $this->addDriverSensorIdToVehicleDevices($driver->getDriverSensorId(), $currentUser, $vehicles);
//            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
//            if ($connection->isTransactionActive()) {
//                $connection->rollback();
//            }
            throw $e;
        }
    }

    /**
     * @param string|null $driverSensorId
     * @param User $currentUser
     * @param array $vehicles
     * @throws \Exception
     */
    public function addDriverSensorIdToVehicleDevices(
        ?string $driverSensorId,
        User $currentUser,
        array $vehicles
    ) {
        if ($driverSensorId) {
            /** @var Vehicle $vehicle */
            foreach ($vehicles as $vehicle) {
                $device = $vehicle->getDevice();

                if ($device) {
                    $this->deviceCommandService
                        ->updateDeviceWithNewDriverSensorId($device, $currentUser, $driverSensorId);
                }
            }
        }
    }

    /**
     * @param string|null $driverSensorId
     * @param User $currentUser
     * @param array $vehicles
     * @throws \Exception
     */
    public function removeDriverSensorIdFromVehicleDevices(
        ?string $driverSensorId,
        User $currentUser,
        array $vehicles
    ) {
        if ($driverSensorId) {
            /** @var Vehicle $vehicle */
            foreach ($vehicles as $vehicle) {
                $device = $vehicle->getDevice();

                if ($device) {
                    $this->deviceCommandService
                        ->removeDriverSensorIdFromDevice($device, $currentUser, $driverSensorId);
                }
            }
        }
    }

    public static function getEventLogLimit(array $data)
    {
        $result = [];
        if (key_exists(Notification::TYPE, $data)) {
            switch ($data[Notification::TYPE]) {
                case Notification::SENSOR_TYPE_OUTSIDE:
                    $result['limit'] = $data[Notification::FROM] . ' - ' . $data[Notification::TO];
                    return $result;
                case Notification::SENSOR_TYPE_GREATER:
                    $result['limit'] = '> ' . self::getNtfValueByAdditionalParams($data);
                    return $result;
                case Notification::SENSOR_TYPE_LESS:
                    $result['limit'] = '< ' . self::getNtfValueByAdditionalParams($data);
                    return $result;
                default:
                    $result['limit'] = self::getNtfValueByAdditionalParams($data);
                    return $result;
            }
        } else {
            $result['limit'] = self::getNtfValueByAdditionalParams($data);
            return $result;
        }
    }

    public static function getNtfValueByAdditionalParams(array $data)
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case Event::ADDITIONAL_SETTING_IS_SENSOR_TEMPERATURE:
                    return $data[Notification::TEMPERATURE];
                    break;
                case Event::ADDITIONAL_SETTING_IS_SENSOR_HUMIDITY:
                    return $data[Notification::HUMIDITY];
                    break;
                case Event::ADDITIONAL_SETTING_IS_SENSOR_LIGHT:
                    return $data[Notification::LIGHT] ? Sensor::LIGHT_ON : Sensor::LIGHT_OFF;
                    break;
                case Event::ADDITIONAL_SETTING_IS_SENSOR_BATTERY_LEVEL:
                    return $data[Notification::BATTERY_LEVEL];
                    break;
                case Event::ADDITIONAL_SETTING_IS_SENSOR_STATUS:
                    return $data[Notification::STATUS] ? DeviceSensor::STATUS_ONLINE_TEXT : DeviceSensor::STATUS_OFFLINE_TEXT;
                    break;
            }
        }

        return null;
    }

    public function getSensorListExportData($params, User $user, $paginated = false)
    {
        $sensors = $this->listSensor($params, $user, $paginated);

        return $this->translateEntityArrayForExport($sensors, $params['fields'] ?? [], Sensor::class, $user);
    }

    public function getTempAndHumidityByVehiclesData(array $params, User $user): array
    {
        $paramsCount = $params['count'] ?? 1000;
        $params['sort'] = 'occurred_at';
        $sensors = TempByVehicleReportBuilder::generateDate(
            array_merge($params, ['sensorsList' => true]), $user, $this->em
        )->execute()->fetchAllAssociative();

        $data = [];
        foreach ($sensors as $sensor) {
            $data[] = [
                'device_sensor_ble_id' => $sensor['device_sensor_ble_id'],
                'device_sensor_label' => $sensor['device_sensor_label'],
                'data' => $this->optimizateSensorData($params, $paramsCount, $sensor['id'], $user)
            ];
        }

        return $data;
    }

    public function getTempAndHumidityBySensorsData(array $params, User $user): array
    {
        $paramsCount = $params['count'] ?? 1000;
        $params['sort'] = 'occurred_at';
        /** @var Sensor $sensor */
        $sensor = $this->em->getRepository(Sensor::class)->find($params['sensorId']);

        return [
            'device_sensor_ble_id' => $sensor->getSensorId(),
            'device_sensor_label' => $sensor->getLabel(),
            'data' => $this->optimizateSensorData($params, $paramsCount, $sensor->getId(), $user)
        ];
    }

    private function optimizateSensorData(array $params, int $paramsCount, $sensorId, User $user)
    {
        $params = array_merge($params, ['chart' => true, 'sensorId' => $sensorId]);
        $sensorData = TempByVehicleReportBuilder::generateDate($params, $user, $this->em)
            ->execute()->fetchAllAssociative();
        $count = count($sensorData);

        //return all if condition
        if ($count / $paramsCount < 2) {
            $data = BaseService::replaceNestedArrayKeysToCamelCase($sensorData);
        } else {
            $nn = 0;
            $n = round($count / $paramsCount, 0, PHP_ROUND_HALF_DOWN);
            $tempResult = [];
            $result = [];

            foreach ($sensorData as $item) {
                if (empty($result)) {
                    $result[] = $item;
                }

                if ($nn == $n) {
                    $result[] = TrackerHistoryRepository::getCoordinatesOptimisatedBySpeed(
                        $tempResult, $result, 'temperature'
                    );
                    $tempResult = [];
                    $nn = 0;
                }
                $tempResult[] = $item;
                $nn++;
            }

            $data = array_merge($result, $tempResult);
        }

        //get prevPoint && postPoint for beautiful chart
        $startDate = $params['startDate'];
        $endDate = $params['endDate'];
        $minStartDate = $params['minStartDate'] ? BaseService::parseDateToUTC($params['minStartDate']) : null;
        $maxEndDate = $params['maxEndDate'] ? BaseService::parseDateToUTC($params['maxEndDate']) : null;
        $params['endDate'] = BaseService::parseDateToUTC($startDate);
        $params['startDate'] = BaseService::parseDateToUTC($startDate)->subYear();
        $params['limit'] = 1;
        $params['sort'] = '-occurred_at';
        $prevPoint = TempByVehicleReportBuilder::generateDate($params, $user, $this->em)
            ->execute()->fetchAllAssociative()[0] ?? null;

        $params['endDate'] = BaseService::parseDateToUTC($endDate)->addYear();
        $params['startDate'] = BaseService::parseDateToUTC($endDate);
        $params['sort'] = 'occurred_at';
        $postPoint = TempByVehicleReportBuilder::generateDate($params, $user, $this->em)
            ->execute()->fetchAllAssociative()[0] ?? null;

        if ($prevPoint && $minStartDate && (new \DateTime($prevPoint['occurred_at'])) > $minStartDate) {
            array_unshift($data, $prevPoint);
        }
        if ($postPoint && $maxEndDate && (new \DateTime($postPoint['occurred_at'])) < $maxEndDate) {
            $data[] = $postPoint;
        }

        return BaseService::replaceNestedArrayKeysToCamelCase($data);
    }
}
