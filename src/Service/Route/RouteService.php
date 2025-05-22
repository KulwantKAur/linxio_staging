<?php

namespace App\Service\Route;

use App\Controller\BaseController;
use App\Entity\Area;
use App\Entity\Asset;
use App\Entity\BaseEntity;
use App\Entity\Device;
use App\Entity\DeviceSensor;
use App\Entity\DigitalForm;
use App\Entity\DriverHistory;
use App\Entity\EventLog\EventLog;
use App\Entity\Idling;
use App\Entity\Notification\Event;
use App\Entity\Notification\Notification;
use App\Entity\Route;
use App\Entity\RouteTemp;
use App\Entity\Sensor;
use App\Entity\Setting;
use App\Entity\Speeding;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\Vehicle;
use App\Entity\VehicleOdometer;
use App\EntityManager\SlaveEntityManager;
use App\Exceptions\Route\AssignRouteDriverException;
use App\Report\Builder\Route\RouteByVehicleReportBuilder;
use App\Report\Builder\Route\RouteReportHelper;
use App\Report\Builder\Route\StopByVehicleReportBuilder;
use App\Report\Core\DTO\FbtReportDTO;
use App\Report\Builder\Summary\VehicleDaySummaryReportBuilder;
use App\Service\BaseService;
use App\Service\Device\DeviceService;
use App\Service\DigitalForm\DigitalFormService;
use App\Service\MapService\MapServiceResolver;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Route\RouteArea\RouteAreaMessage;
use App\Service\Route\RoutePostHandle\RoutePostHandleConsumer;
use App\Service\Route\RoutePostHandle\RoutePostHandleProducer;
use App\Service\Route\RoutePostHandle\RoutePostHandleMessage;
use App\Service\Setting\SettingService;
use App\Service\User\UserServiceHelper;
use App\Service\Vehicle\VehicleService;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RouteService extends BaseService
{
    use RouteServiceFieldsTrait;
    use RouteHelperTrait;

    public const CALCULATE_ROUTES_BATCH_SIZE = 20;
    public const ORIGINAL_ROUTE_NAME = 'originalRoute';

    private $mapService;
    private bool $isLocationChecked = false;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManager $em,
        private readonly DeviceService $deviceService,
        private readonly ValidatorInterface $validator,
        private readonly SettingService $settingService,
        private readonly MapServiceResolver $mapServiceResolver,
        private readonly VehicleService $vehicleService,
        private readonly NotificationEventDispatcher $notificationDispatcher,
        private readonly DigitalFormService $digitalFormService,
        private readonly SlaveEntityManager $emSlave,
        private readonly RoutePostHandleProducer $routePostHandleProducer,
        private readonly Producer $routeAreaProducer,
        private readonly LoggerInterface $logger
    ) {
        $this->mapService = $mapServiceResolver->getInstance();
    }

    /**
     * @param Route $route
     * @return bool
     * @todo remove in future when finish fix routes with wrong points
     */
    private function isRouteValidToTriggerEvent(Route $route): bool
    {
        $routeLastDT = $route->getFinishedAt() ?: $route->getStartedAt();
        $tsDiff = $route->getCreatedAt()->getTimestamp() - $routeLastDT->getTimestamp();

        return boolval($tsDiff < 60 * 60 * 24);
    }

    /**
     * @param array $sensorDataByDevice
     * @param $limit
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getRoutesByDeviceSensorData(
        array $sensorDataByDevice,
        $limit
    ): array {
        foreach ($sensorDataByDevice as &$sensorDatumByDevice) {
            $dateFrom = $sensorDatumByDevice['dateFrom'];
            $dateTo = $sensorDatumByDevice['dateTo'];
            $device = $sensorDatumByDevice['device'];
            $routesByDevice = $this->em->getRepository(Route::class)->getByDevice(
                $device,
                $dateFrom,
                $dateTo,
                $limit
            );

            foreach ($routesByDevice as $key => &$routeByDevice) {
                $routeByDevice = $this->makeRoutePartiallyByDeviceAndDateRange($routeByDevice, $dateFrom, $dateTo);

                if (!$routeByDevice) {
                    unset($routesByDevice[$key]);
                }
            }

            $sensorDatumByDevice['routes'] = $routesByDevice;
        }

        return $sensorDataByDevice;
    }

    /**
     * @param Route $route
     * @param \DateTimeInterface $dateFrom
     * @param \DateTimeInterface $dateTo
     * @return Route
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function makeRoutePartiallyByDeviceAndDateRange(
        Route $route,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): ?Route {
        if ($route->getStartedAt() < $dateFrom || $route->getStartedAt() > $dateTo) {
            $futureTH = $this->em->getRepository(TrackerHistory::class)
                ->getFutureTrackerHistoryInRange($route->getDevice(), $dateFrom, $dateTo);
            $route->setStartedAt($dateFrom);

            if ($futureTH) {
                $route->setPointStart($futureTH);
                $route->setStartedAt($futureTH->getTs());
                $route->setDistance($route->getPointFinish()
                    ? $route->getPointFinish()->getOdometer() - $futureTH->getOdometer()
                    : null
                );
            } else {
                return null;
            }
        }
        if ($route->getFinishedAt() > $dateTo || $route->getFinishedAt() < $dateFrom) {
            $pastTH = $this->em->getRepository(TrackerHistory::class)
                ->getPastTrackerHistoryInRange($route->getDevice(), $dateFrom, $dateTo);
            $route->setFinishedAt($dateTo);

            if ($pastTH) {
                $route->setPointFinish($pastTH);
                $route->setFinishedAt($pastTH->getTs());
                $route->setDistance($pastTH->getOdometer() - $route->getPointStart()->getOdometer());
            }
        }

        return $route;
    }

    /**
     * @param Sensor $sensor
     * @param \DateTimeInterface $dateFrom
     * @param \DateTimeInterface $dateTo
     * @return array
     */
    private function getSensorDataRangeByDevice(
        Sensor $sensor,
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo
    ): array {
        $data = [];
        $prevDeviceId = null;
        $sensorTrackerHistories = $this->em->getRepository(DeviceSensor::class)
            ->getTrackerHistoriesSensorBySensor($sensor, $dateFrom, $dateTo);

        foreach ($sensorTrackerHistories as $key => $sensorTrackerHistory) {
            $deviceId = $sensorTrackerHistory['deviceId'];
            $occurredAt = $sensorTrackerHistory['occurredAt'];

            if ($deviceId != $prevDeviceId) {
                $data[$deviceId]['device'] = $this->em->getRepository(Device::class)->find($deviceId);
                $data[$deviceId]['deviceId'] = $deviceId;
                $data[$deviceId]['dateFrom'] = $occurredAt;
            }

            $data[$deviceId]['dateTo'] = $occurredAt;
            $prevDeviceId = $deviceId;
        }

        return $data;
    }

    /**
     * @param array $routes
     * @return array
     */
    private function clearRoutesEmptyData(array $routes): array
    {
        if (isset($routes[0]) && isset($routes[0][self::ORIGINAL_ROUTE_NAME]) && !array_key_exists('id', $routes[0])) {
            array_shift($routes);
        }

        return $routes;
    }

    /**
     * @param Route $route
     * @return Route
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function updateRoutePostponedData(Route $route): Route
    {
        $this->updateRouteSpeedStats($route);
        $this->updateRouteTotalStats($route);
        $this->updateRouteDriver($route);

        return $route;
    }

    /**
     * @param int $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @return void
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @todo postponed handle to avoid calc repeats for the same route via extra selects to db?
     * But with postponed process if it crashes - we lost these results in queue/post-handle
     *
     */
    public function updateRoutesPostponedData(int $deviceId, $dateFrom, $dateTo): void
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            return;
        }

        $routes = $this->em->getRepository(Route::class)->getRoutesByDateRangeAndDevice($device, $dateFrom, $dateTo);

        foreach ($routes as $route) {
            $this->updateRoutePostponedData($route);
        }

        $this->em->flush();
    }

    /**
     * @param int $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @return void
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateRoutesWithWrongFinishPoints(int $deviceId, $dateFrom, $dateTo): void
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        if (!$device) {
            return;
        }

        $routes = $this->em->getRepository(Route::class)->getRoutesByDateRangeAndDevice($device, $dateFrom, $dateTo);

        /** @var Route $route */
        foreach ($routes as $key => $route) {
            $nextRoute = $this->em->getRepository(Route::class)->getNextRoute($route);

            if (!$nextRoute) {
                $nextRoute = $this->em->getRepository(Route::class)->getNextRouteByClosestTime($route);
            }

            if ($nextRoute &&
                ($route->getPointFinish()
                    && $route->getPointFinish()->getId() != $nextRoute->getPointStart()->getId())
            ) {
                $this->extendLastRoute($route, $nextRoute->getPointStart());
            }

            if ($route->getFinishedAt()
                && $route->getFinishedAt()->getTimestamp() < $route->getStartedAt()->getTimestamp()
            ) {
                $nextRoute = $this->em->getRepository(Route::class)->getNextRouteByClosestTime($route);

                if ($nextRoute) {
                    $this->extendLastRoute($route, $nextRoute->getPointStart());
                }
            }
        }

        $this->em->flush();
    }

    /**
     * @param int $id
     * @param User $currentUser
     * @return Route|null
     */
    public function getById(int $id, User $currentUser)
    {
        return $this->em->getRepository(Route::class)->getRouteById($id, $currentUser);
    }

    public function getVehicleRoutes(
        Vehicle $vehicle,
        $dateFrom,
        $dateTo,
        $include = [],
        $byDriver = true,
        $limit = null,
        $offset = null,
        $order = Criteria::ASC,
        array $optimization = []
    ): ?array {
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);
        $addStartedRoute = !$offset;

        if (in_array('summary', $include)) {
            array_push($include, 'startOdometer', 'finishOdometer');
        }

        $routes = $this->em->getRepository(Route::class)
            ->getRoutesByVehicle($vehicle, $dateFrom, $dateTo, $limit, $offset, false, $order);
        $routesWithCoordinates = $this->collectRoutesAndCoordinates(
            $routes, $include, $dateFrom, $dateTo, true, $addStartedRoute, false, $order, $optimization
        );

        if ($routes && $addStartedRoute) {
            $startStopRoute = $this->getStartStopRoute($routes, $order, $include);

            if ($startStopRoute) {
                array_unshift($routesWithCoordinates, $startStopRoute);
            }

            $finishStopRoute = $this->getFinishStopRoute($routes, $order, $include);

            if ($finishStopRoute) {
                array_push($routesWithCoordinates, $finishStopRoute);
            }
        }

        if (!isset($optimization['type'])) {
            $routesWithCoordinates = $this->addAddressToRoutes($routesWithCoordinates);
        }

        if ($byDriver) {
            $routesWithCoordinates = $this->collectRoutesByDrivers($routesWithCoordinates);
            $routesWithCoordinates = array_map(
                function ($driver) {
                    $driver['routes'] = $this->handleEmptyRouteCoordinates($driver['routes']);
                    return $driver;
                },
                $routesWithCoordinates
            );
        } else {
            if (!isset($optimization['type']) || $optimization['type'] !== 'speed') {
                $routesWithCoordinates = $this->handleEmptyRouteCoordinates($routesWithCoordinates);
            }
        }

        if (in_array('summary', $include)) {
            $routesWithCoordinates = $this
                ->getRouteSummaryDataByVehicle($routesWithCoordinates, $vehicle, $dateFrom, $dateTo);
        }

        return $routesWithCoordinates;
    }

    /**
     * @param Asset $asset
     * @param $dateFrom
     * @param $dateTo
     * @param array $include
     * @param bool $byAsset
     * @param null $limit
     * @return array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAssetRoutes(
        Asset $asset,
        $dateFrom,
        $dateTo,
        $include = [],
        $byAsset = true,
        $limit = null
    ): ?array {
        $routesWithCoordinates = [];
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);
        $sensor = $asset->getSensor();
        $sensorDataByDevice = $this->getSensorDataRangeByDevice($sensor, $dateFrom, $dateTo);
        $routesDataByDevice = $this->getRoutesByDeviceSensorData($sensorDataByDevice, $limit);
        // @todo check if it's without first stopped route
//        $startStopRoute = $this->getStartStopRoute($routesDataByDevice);
//        if ($startStopRoute) {
//            array_unshift($routesWithCoordinates, $startStopRoute);
//        }

        foreach ($routesDataByDevice as $key => &$routesDatumByDevice) {
            $routesDatumByDevice['routes'] = $this->collectRoutesAndCoordinates(
                $routesDatumByDevice['routes'],
                $include,
                $routesDatumByDevice['dateFrom'],
                $routesDatumByDevice['dateTo'],
                true,
                false,
                false,
            );

            $routesWithCoordinates = array_merge($routesWithCoordinates, $routesDatumByDevice['routes']);
        }

        $routesWithCoordinates = $this->addAddressToRoutes($routesWithCoordinates);

        if ($byAsset) {
            $routesWithCoordinates = $this->collectRoutesByAsset($asset, $routesWithCoordinates);
            $routesWithCoordinates = array_map(
                function ($asset) {
                    $asset['routes'] = $this->handleEmptyRouteCoordinates($asset['routes']);
                    return $asset;
                },
                $routesWithCoordinates
            );
        } else {
            $routesWithCoordinates = $this->handleEmptyRouteCoordinates($routesWithCoordinates);
        }

        return $routesWithCoordinates;
    }

    /**
     * @param array $routesWithCoordinates
     * @param Vehicle $vehicle
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getRouteSummaryDataByVehicle(
        array $routesWithCoordinates,
        Vehicle $vehicle,
        string $dateFrom,
        string $dateTo
    ) {
        $summary = [
            'distance' => null,
            'driving' => null,
            'stopped' => null,
            'idling' => null,
            'startOdometer' => null,
            'endOdometer' => null
        ];
        $summary['idling'] =
            $this->em->getRepository(Idling::class)->getDurationByDateAndVehicle($vehicle, $dateFrom, $dateTo);

        $lastVehicleOdometer = $this->em->getRepository(VehicleOdometer::class)
            ->lastByVehicleAndOccurredAt($vehicle, $dateTo);

        return $this->handleRouteSummaryData($routesWithCoordinates, $summary, $lastVehicleOdometer);
    }

    protected function handleRouteSummaryData(
        array $routesWithCoordinates,
        $summary,
        ?VehicleOdometer $vehicleOdometer = null
    ): array {
        foreach ($routesWithCoordinates as $route) {
            if ($route['type'] === Route::TYPE_DRIVING) {
                $summary['driving'] += $route['duration'];
                $summary['distance'] += $route['distance'];
            }
            if ($route['type'] === Route::TYPE_STOP) {
                $summary['stopped'] += $route['duration'];
            }
            if (
                is_numeric($route['startOdometer'])
                && (is_null($summary['startOdometer'])
                    || $summary['startOdometer'] > $route['startOdometer'])
            ) {
                $summary['startOdometer'] = $route['startOdometer'];
            }
            if ($summary['endOdometer'] < $route['finishOdometer']) {
                $summary['endOdometer'] = $route['finishOdometer'];
            }
        }

        if ($vehicleOdometer) {
            $summary['endOdometer'] += $vehicleOdometer->getAccuracy();
            $summary['startOdometer'] += $vehicleOdometer->getAccuracy();
        }

        return ['routes' => $routesWithCoordinates, 'summary' => $summary];
    }

    /**
     * @param array $routes
     * @return array
     */
    public function handleEmptyRouteCoordinates(array $routes)
    {
        /** @var Route $route */
        foreach ($routes as &$route) {
            if (!isset($route['coordinates']) || !$route['coordinates']) {
                continue;
            }

            $coordinates = array_map(
                function ($key, $coordinate) use ($route) {
                    if (is_null($coordinate['lat']) || is_null($coordinate['lng'])
                        || ((double)$coordinate['lat'] === 0.0 && (double)$coordinate['lng'] === 0.0)
                    ) {
                        $notNullCoordinate = $this->getFirstNotNullCoordinateValue($route['coordinates'], $key);
                        if ($notNullCoordinate) {
                            $coordinate['lat'] = $notNullCoordinate['lat'];
                            $coordinate['lng'] = $notNullCoordinate['lng'];
                        }
                    }

                    return $coordinate;
                },
                array_keys($route['coordinates']),
                $route['coordinates']
            );
            $route['coordinates'] = $coordinates;
        }

        return $this->clearRoutesEmptyData($routes);
    }

    /**
     * @param array $coordinates
     * @param $key
     * @return mixed|null
     */
    public function getFirstNotNullCoordinateValue(array $coordinates, $key)
    {
        $value = null;
        for ($i = $key; $i < count($coordinates); $i++) {
            if (!is_null($coordinates[$i]['lat']) && !is_null($coordinates[$i]['lng'])
                && (double)$coordinates[$i]['lng'] !== 0.0 && (double)$coordinates[$i]['lat'] !== 0.0) {
                $value = $coordinates[$i];
                break;
            }
        }

        if (!$value) {
            for ($i = $key; $i >= 0; $i--) {
                if (!is_null($coordinates[$i]['lat']) && !is_null($coordinates[$i]['lng'])
                    && (double)$coordinates[$i]['lng'] !== 0.0 && (double)$coordinates[$i]['lat'] !== 0.0) {
                    $value = $coordinates[$i];
                    break;
                }
            }
        }

        return $value;
    }

    public function getVehicleRoutesPaginated(
        Vehicle $vehicle,
        $dateFrom,
        $dateTo,
        $include = [],
        $byDriver = true,
        $limit = null,
        $offset = null
    ): ?array {
        $routes =
            $this->getVehicleRoutes($vehicle, $dateFrom, $dateTo, $include, $byDriver, $limit, $offset, Criteria::DESC);

        $routesCount = $this->em->getRepository(Route::class)
            ->getRoutesByVehicle($vehicle, $dateFrom, $dateTo, null, null, true);

        return ['data' => $routes, 'count' => $routesCount];
    }

    /**
     * @param array $routesWithCoordinates
     * @return array
     */
    private function collectRoutesByDrivers(array $routesWithCoordinates): array
    {
        $driverIds = array_column($routesWithCoordinates, 'driverId');
        $vehicleIds = array_column($routesWithCoordinates, 'vehicleId');
        $routesWithCoordinatesByDriver = [];

        foreach ($driverIds as $key => $driverId) {
            $routesWithCoordinatesByDriver[$driverId]['driverId'] = $driverId;
            $routesWithCoordinatesByDriver[$driverId]['vehicleId'] = $vehicleIds[$key];
            $routesWithCoordinatesByDriver[$driverId]['routes'][] = $routesWithCoordinates[$key];
        }

        $routesWithCoordinates = array_values($routesWithCoordinatesByDriver);

        return $routesWithCoordinates;
    }

    /**
     * @param Asset $asset
     * @param array $routesWithCoordinates
     * @return array
     */
    private function collectRoutesByAsset(Asset $asset, array $routesWithCoordinates): array
    {
        $driverIds = array_column($routesWithCoordinates, 'driverId');
        $vehicleIds = array_column($routesWithCoordinates, 'vehicleId');
        $routesWithCoordinatesByDriver = [];

        foreach ($driverIds as $key => $driverId) {
            $routesWithCoordinatesByDriver[$driverId]['driverId'] = intval($driverId);
            $routesWithCoordinatesByDriver[$driverId]['vehicleId'] = $vehicleIds[$key];
            $routesWithCoordinatesByDriver[$driverId]['assetId'] = $asset->getId();
            $routesWithCoordinatesByDriver[$driverId]['routes'][] = $routesWithCoordinates[$key];
        }

        $routesWithCoordinates = array_values($routesWithCoordinatesByDriver);

        return $routesWithCoordinates;
    }

    /**
     * @param User $driver
     * @param $dateFrom
     * @param $dateTo
     * @param User $currentUser
     * @param array $include
     * @param bool $byVehicle
     * @return array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDriverRoutes(
        User $driver,
        $dateFrom,
        $dateTo,
        User $currentUser,
        $include = [],
        $byVehicle = true
    ): ?array {
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);
        $routes = $this->em->getRepository(Route::class)->getRoutesByDriver($driver, $dateFrom, $dateTo, $currentUser);

        $routesWithCoordinates = $this->collectRoutesAndCoordinates($routes, $include, $dateFrom, $dateTo);
        if ($routes) {
            $startStopRoute = $this->getStartStopRoute($routes, Criteria::ASC, $include);
            if ($startStopRoute) {
                array_unshift($routesWithCoordinates, $startStopRoute);
            }
            $finishStopRoute = $this->getFinishStopRoute($routes, Criteria::ASC, $include);
            if ($finishStopRoute) {
                array_push($routesWithCoordinates, $finishStopRoute);
            }
        }

        $routesWithCoordinates = $this->collectRoutesAndCoordinates($routes, $include, $dateFrom, $dateTo);
        $routesWithCoordinates = $this->addAddressToRoutes($routesWithCoordinates);

        if ($byVehicle) {
            $routesWithCoordinates = $this->collectRoutesByVehicles($routesWithCoordinates);
        }

        return $routesWithCoordinates;
    }

    /**
     * @param array $routesWithCoordinates
     * @return array
     */
    private function collectRoutesByVehicles(array $routesWithCoordinates): array
    {
        $vehicleIds = array_column($routesWithCoordinates, 'vehicleId');
        $driverIds = array_column($routesWithCoordinates, 'driverId');
        $routesWithCoordinatesByVehicle = [];

        foreach ($vehicleIds as $key => $vehicleId) {
            $routesWithCoordinatesByVehicle[$vehicleId]['vehicleId'] = $vehicleId;
            $routesWithCoordinatesByVehicle[$vehicleId]['driverId'] = $driverIds[$key];
            $routesWithCoordinatesByVehicle[$vehicleId]['routes'][] = $routesWithCoordinates[$key];
        }

        $routesWithCoordinates = array_values($routesWithCoordinatesByVehicle);

        return $routesWithCoordinates;
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @param User $currentUser
     * @param User|null $driver
     * @param Vehicle|null $vehicle
     * @return array|null
     */
    public function getDriverOrVehicleRoutes(
        $dateFrom,
        $dateTo,
        ?User $currentUser = null,
        User $driver = null,
        Vehicle $vehicle = null
    ): ?array {
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);
        $routes = [];
        $movement = 0;
        $duration = 0;
        $avgSpeed = 0;
        $avgSpeedPointCounter = 0;

        if ($driver) {
            $routes = $this->em->getRepository(Route::class)
                ->getRoutesByDriverOrVehicleAndDateRange($driver->getId(), null, $dateFrom, $dateTo, $currentUser);
        } elseif ($vehicle) {
            $routes = $this->em->getRepository(Route::class)
                ->getRoutesByDriverOrVehicleAndDateRange(null, $vehicle->getId(), $dateFrom, $dateTo, $currentUser);
        }

        $borderRoute = false;
        /** @var Route $route */
        foreach ($routes as $route) {
            // check Is route = border route (date range divides into parts)
            if (
                !Carbon::createFromTimestamp($route->getStartedAt()->getTimestamp())->between($dateFrom, $dateTo)
                || ($route->getFinishedAt() && !Carbon::createFromTimestamp($route->getFinishedAt()->getTimestamp())
                        ->between($dateFrom, $dateTo))
            ) {
                $borderRoute = true;
            }
            if ($borderRoute) {
                $routeTrackerHistories = $this->em->getRepository(TrackerHistory::class)
                    ->getHistoriesDataByRoute($route);

                //filter points of route which enter in date range
                $routeTHFiltered = array_filter(
                    $routeTrackerHistories,
                    function ($th) use ($dateFrom, $dateTo) {
                        return (Carbon::createFromTimestamp($th['ts']->getTimestamp()))->between($dateFrom, $dateTo);
                    }
                );

                //get last and first points of route part and calculate movement, duration
                if (count($routeTHFiltered)) {
                    $last = reset($routeTHFiltered);
                    $first = end($routeTHFiltered);
                    $movement += $last['odometer'] - $first['odometer'];
                    $duration += $last['ts']->getTimestamp() - $first['ts']->getTimestamp();

                    foreach ($routeTHFiltered as $th) {
                        $avgSpeed += $th['speed'];
                        $avgSpeedPointCounter++;
                    }
                }
            } else {
                $movement += $route->getDistance();
                $duration += $route->getDuration();
                $avgSpeed += $route->getAvgSpeed();
                $avgSpeedPointCounter++;
            }

            $borderRoute = false;
        }

        return [
            'distance' => $movement > 0 ? $movement : 0,
            'duration' => $duration,
            'avgSpeed' => ($avgSpeed != 0 && $avgSpeedPointCounter != 0) ? $avgSpeed / $avgSpeedPointCounter : 0,
        ];
    }

    /**
     * @param RouteTemp $routeTemp
     * @param TrackerHistory $th
     */
    public function extendLastRouteTemp(RouteTemp $routeTemp, TrackerHistory $th)
    {
        $routeTemp->setFinishedAt($th->getTs());
        $routeTemp->setPointFinish($th);

        if ($routeTemp->getPointFinish()->getOdometer() && $routeTemp->getPointStart()->getOdometer()) {
            $routeTemp->setDistance(
                $routeTemp->getPointFinish()->getOdometer() - $routeTemp->getPointStart()->getOdometer()
            );
        }

        $routeTemp->setDuration(
            $routeTemp->getFinishedAt()->getTimestamp() - $routeTemp->getStartedAt()->getTimestamp()
        );
    }

    /**
     * @param Route $route
     * @param TrackerHistory $th
     * @return Route
     */
    public function extendLastRoute(Route $route, TrackerHistory $th)
    {
        $route->setFinishedAt($th->getTs());
        $route->setPointFinish($th);

        if ($route->getPointFinish()->getOdometer() && $route->getPointStart()->getOdometer()) {
            $route->setDistance(
                $route->getPointFinish()->getOdometer() - $route->getPointStart()->getOdometer()
            );
        }

        return $route;
    }

    public function getDriverVehicleRoutes(
        ?int $driverId,
        ?int $vehicleId,
        $dateFrom,
        $dateTo,
        User $currentUser,
        $include = [],
        ?string $scope = null
    ): array {
        $dateFrom = $dateFrom ? self::parseDateToUTC($dateFrom) : Carbon::now();
        $dateTo = $dateTo ? self::parseDateToUTC($dateTo) : (new Carbon())->subHours(24);
        $availableVehicleIds = $this->em->getRepository(UserGroup::class)->getUserVehiclesIdFromUserGroup($currentUser);
        $routes = $this->em->getRepository(Route::class)
            ->getRoutesByDriverOrVehicle($driverId, $vehicleId, $dateFrom, $dateTo, $availableVehicleIds, $scope);

        return $this->collectRoutesAndCoordinates($routes, $include, $dateFrom, $dateTo);
    }

    private function collectRoutesAndCoordinates(
        $routes,
        $include,
        $dateFrom,
        $dateTo,
        $withLastCoordinates = false,
        $addStartedRoute = true,
        $addNextRoute = false,
        $order = Criteria::ASC,
        array $optimization = []
    ): array {
        $routesWithCoordinates = [];

        if ($routes) {
            /** @var Route $route */
            foreach ($routes as $key => $route) {
                $this->setCoordinatesToRoute($route, $include, $withLastCoordinates, $optimization);
                $routesWithCoordinates[$key] = $route->toArray($include);
            }
            if (!$addStartedRoute) {
                return $routesWithCoordinates;
            }

            $startedRoute = $this->getStartedRouteWithPartialRoute(
                $routes[0], $dateFrom, $include, $withLastCoordinates, $order, $optimization
            );

            if ($startedRoute) {
                array_unshift($routesWithCoordinates, $startedRoute);
            } else {
                //make partial from first route
                $startTs = new Carbon($routesWithCoordinates[0]['pointStart']['lastCoordinates']['ts']);

                if ($startTs < $dateFrom) {
                    $partialStartRoute = $this->makePartialStartRoute(
                        $routes[0], $dateFrom, $include, $withLastCoordinates, $optimization
                    );
                    if ($partialStartRoute) {
                        $routesWithCoordinates[0] = $partialStartRoute;
                    } else {
                        array_shift($routesWithCoordinates);
                    }
                }
            }

            $finishedRoute = $this->getFinishedRouteWithPartialRoute(
                end($routes), $dateTo, $include, $withLastCoordinates, $order, $optimization
            );

            if ($finishedRoute) {
                $routesWithCoordinates[] = $finishedRoute;
            } else {
                //make partial from last route
                $finishTs = new Carbon(end($routesWithCoordinates)['pointFinish']['lastCoordinates']['ts']);

                if ($finishTs > $dateTo && $routesWithCoordinates) {
                    $routesWithCoordinates[count($routesWithCoordinates) - 1] =
                        $this->makePartialFinishRouteFromArray(end($routesWithCoordinates), $dateTo);
                }
            }
            if ($addNextRoute) {
                $nextPartialRoute = $this->getNextRouteAsPartialRoute(
                    end($routes),
                    $dateTo,
                    $include,
                    $withLastCoordinates,
                    $optimization
                );

                if ($nextPartialRoute) {
                    array_push($routesWithCoordinates, $nextPartialRoute);
                }
            }
        }

        return $routesWithCoordinates;
    }

    /**
     * @param Route $route
     * @param array|null $include
     * @param bool $withLastCoordinates
     * @throws \Exception
     */
    public function setCoordinatesToRoute(
        Route $route,
        $include,
        $withLastCoordinates = false,
        array $optimization = []
    ) {
        if ($include && in_array('coordinates', $include, true)) {
            $vehicle = $route->getVehicle();
            $isLast = false;

            if ($vehicle && $withLastCoordinates) {
                $lastVehicleRoute = $this->em->getRepository(Route::class)
                    ->getLastVehicleRoute($vehicle->getId());

                if ($lastVehicleRoute && ($lastVehicleRoute->getId() == $route->getId())) {
                    $isLast = true;
                }
            }

            $additionalSelect = $this->getAdditionalSelectForTrackerHistoryData($include);
            if ($optimization) {
                $coordinates = $this->em->getRepository(TrackerHistory::class)
                    ->getCoordinatesByRouteWithOptimization($route, $additionalSelect, $isLast, $optimization);
            } else {
                $coordinates = $this->em->getRepository(TrackerHistory::class)
                    ->getCoordinatesByDeviceRoute($route, $additionalSelect, $isLast);
            }
            $route->setCoordinates($coordinates);
        }
    }

    /**
     * @param array $include
     * @return array
     * @throws \ReflectionException
     */
    private function getAdditionalSelectForTrackerHistoryData(array $include): array
    {
        $select = [];

        if ($this->getSubstringsInArray('coordinates.', $include)) {
            $rc = new \ReflectionClass(TrackerHistory::class);
            $historyProperties = array_column($rc->getProperties(), 'name');
            $selectValues = $this->getSubstringsValuesInArray('coordinates.', $include);

            foreach ($selectValues as $value) {
                if (in_array($value, $historyProperties)) {
                    $select[] = $value;
                }
            }
        }

        return $select;
    }

    /**
     * @param Route $route
     * @param array $data
     * @return Route
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editRoute(Route $route, array $data): Route
    {
        $route->setAttributes($data);
        $this->validate($this->validator, $route, ['update']);

        $this->em->flush();

        return $route;
    }

    public function getVehiclesWithRoutes(
        ?int $driverId,
        ?Team $team,
        $dateFrom,
        $dateTo,
        User $currentUser,
        $include,
        ?string $scope
    ): array {
        $vehiclesWithRoutes = [];
        $vehicleIds = $this->em->getRepository(Vehicle::class)->getVehicleIdsByTeamWithoutStatuses($team);

        foreach ($vehicleIds as $vehicleId) {
            $vehicle = [];
            $vehicle['vehicleId'] = $vehicleId;
            $routes = $this->getDriverVehicleRoutes(
                $driverId,
                $vehicleId,
                $dateFrom,
                $dateTo,
                $currentUser,
                $include,
                $scope
            );

            if ($routes) {
                $vehicle['routes'] = $routes;
                $vehiclesWithRoutes[] = $vehicle;
            }
        }

        return $vehiclesWithRoutes;
    }

    public function getVehicleWithRoutes(
        ?int $driverId,
        ?int $vehicleId,
        $dateFrom,
        $dateTo,
        $currentUser,
        $include,
        ?string $scope
    ): array {
        $vehiclesWithRoutes = [];
        $routes =
            $this->getDriverVehicleRoutes($driverId, $vehicleId, $dateFrom, $dateTo, $currentUser, $include, $scope);

        if ($routes) {
            $vehicle = [];
            $vehicle['vehicleId'] = $vehicleId;
            $vehicle['routes'] = $routes;
            $vehiclesWithRoutes[] = $vehicle;
        }

        return $vehiclesWithRoutes;
    }

    public function getVehiclesWithRoutesForDriver(
        ?int $driverId,
        ?int $vehicleId,
        $dateFrom,
        $dateTo,
        User $currentUser,
        $include,
        ?string $scope
    ): array {
        $vehiclesWithRoutes = [];
        $routes =
            $this->getDriverVehicleRoutes($driverId, $vehicleId, $dateFrom, $dateTo, $currentUser, $include, $scope);
        $vehicleIds = array_column($routes, 'vehicleId');

        foreach ($vehicleIds as $key => $vehicleId) {
            $vehiclesWithRoutes[$vehicleId]['vehicleId'] = $vehicleId;
            $vehiclesWithRoutes[$vehicleId]['driverId'] = $driverId;
            $vehiclesWithRoutes[$vehicleId]['routes'][] = $routes[$key];
        }

        return array_values($vehiclesWithRoutes);
    }

    public function calculateRoutes(int $deviceId, $minTS, $maxTS): void
    {
        $lastRouteTempRoute = $this->em->getRepository(RouteTemp::class)
            ->getLastTempRouteStartedFromDate($deviceId, $maxTS);
        $newestSecondRouteTS = $lastRouteTempRoute
            ? $this->em->getRepository(RouteTemp::class)->getNewestSecondTempRouteTSWithTypeFromDate(
                $deviceId, $maxTS, $lastRouteTempRoute->getType()
            )
            : null;
        $newestRecords = $this->em->getRepository(TrackerHistory::class)
            ->getTrackerRecordsByDeviceQuery($deviceId, $minTS, $newestSecondRouteTS);
        $teamSettings = $this->settingService->getTeamSettings(
            $this->em->getRepository(Device::class)->getTeamIdByDeviceId($deviceId)
        );
        $device = $this->em->getReference(Device::class, $deviceId);
        $this->em->getRepository(RouteTemp::class)->removeNewestRoutesFromDate($deviceId, $minTS, $newestSecondRouteTS);
        $this->em->getRepository(Route::class)->removeNewestRoutesFromDate($deviceId, $minTS, $maxTS);
        $i = 1;
        $routeIds = [];

        /** @var TrackerHistory $newestTrackerHistory */
        foreach ($newestRecords->toIterable() as $newestTrackerHistory) {
//            if (!$this->em->getConnection()->isTransactionActive()) {
//                $this->em->getConnection()->beginTransaction();
//            }

            try {
                $routeTemp = $this->saveDeviceRouteTemp($device, $newestTrackerHistory);
                $newestTrackerHistory->setIsCalculated(true);
                $newestTrackerHistoryTemp = $this->em->getRepository(TrackerHistoryTemp::class)
                    ->getTHTempByTH($newestTrackerHistory);

                if ($newestTrackerHistoryTemp) {
                    $newestTrackerHistoryTemp->setIsCalculated(true);
                }

                $route = $this->saveDeviceRoute($device, $routeTemp, $teamSettings);

                if ($route) {
                    $routeIds[] = ['routeId' => $route->getId(), 'deviceId' => $route->getDevice()->getId()];
                }

                if (($i % self::CALCULATE_ROUTES_BATCH_SIZE) === 0) {
                    $this->em->flush(); // Executes all updates.
//                    $this->em->getConnection()->commit();
                    $this->em->clear(); // Detaches all objects from Doctrine!
                    $device = $this->em->getReference(Device::class, $deviceId);
                    $this->triggerRouteAreaProducer($routeIds);
                    $routeIds = [];
                }

                ++$i;
            } catch (\Exception $e) {
                $this->logger->error('Unable to process route: ' . $e->getMessage(), [
                    'deviceId' => $deviceId,
                    'thId' => $newestTrackerHistory->getId(),
                ]);
                continue;
//                $this->em->getConnection()->rollBack();
//                throw $e;
            }
        }

        $this->em->flush();

//        if ($this->em->getConnection()->isTransactionActive()) {
//            $this->em->getConnection()->commit();
//        }

        $this->triggerRouteAreaProducer($routeIds);
        $this->em->clear();
    }

    public function recalculateRoutes(int $deviceId, $minTS, $maxTS): void
    {
        $this->calculateRoutes($deviceId, $minTS, $maxTS);
    }

    public function saveDeviceRouteTemp(
        Device $device,
        TrackerHistory $trackerHistory
    ): ?RouteTemp {
        $routeTypeByTrackerHistory = $trackerHistory->getRouteTempTypeByIgnitionAndMovement(
            $trackerHistory->getIgnition(),
            $trackerHistory->getMovement()
        );
        $lastRouteTemp = $this->em->getRepository(RouteTemp::class)
            ->getLastTempRouteStartedFromDate($device->getId(), $trackerHistory->getTs());

        if (!$lastRouteTemp) {
            $routeTemp = $this->saveRouteTempEntity($device, $trackerHistory, $routeTypeByTrackerHistory);
        } else {
            $routeTemp = $this
                ->handleLastAndNewTempRoute($lastRouteTemp, $trackerHistory, $routeTypeByTrackerHistory, $device);
        }

        return $routeTemp;
    }

    public function saveDeviceRoute(
        Device $device,
        RouteTemp $routeTemp,
        array $teamSettings
    ): ?Route {
        $tempRouteTypeByClientSettings = $this->getRouteTypeByClientSettings($routeTemp, $teamSettings);
        $lastRoute = $this->deviceService->getLastRouteStartedFromDate(
            $device->getId(),
            $routeTemp->getPointStart()->getTs()
        );

        if (!$lastRoute) {
            $route = $this->saveRouteEntity(
                $device,
                $routeTemp,
                $tempRouteTypeByClientSettings
            );
        } else {
            $route = $this->handleLastAndNewRoute(
                $lastRoute,
                $routeTemp,
                $tempRouteTypeByClientSettings,
                $device,
                $teamSettings
            );
        }

        if ($this->isLocationChecked()) {
            $route->setIsLocationChecked($this->isLocationChecked());
        }

        $route->setTotalValuesFromRouteTemp($routeTemp);

        return $route;
    }

    protected function saveRouteEntity(
        Device $device,
        RouteTemp $routeTemp,
        string $tempRouteTypeByClientSettings
    ): Route {
        $route = new Route();
        $route->fromRouteTemp($device, $routeTemp, $tempRouteTypeByClientSettings);
        $route->setScope($this->getDriverRouteScopeByDevice($device));
        if ($route->getScope() == Route::SCOPE_WORK) {
            $route->setDriverComment($device->getVehicleDriver()?->getDriverRouteComment());
        }
        $firstPoint = $device->getValidPreviousPoint($route->getPointStart());

        if ($firstPoint) {
            $route->setStartCoordinates(['lat' => $firstPoint->getLat(), 'lng' => $firstPoint->getLng()]);
        }

        $this->em->persist($route);
        $this->em->flush();

        return $route;
    }

    protected function updateRouteEntity(
        Route $route,
        Device $device,
        RouteTemp $routeTemp,
        string $tempRouteTypeByClientSettings
    ): Route {
        $route->fromRouteTemp($device, $routeTemp, $tempRouteTypeByClientSettings);
        $route->setScope($this->getDriverRouteScopeByDevice($device));
        if ($route->getScope() == Route::SCOPE_WORK) {
            $route->setDriverComment($device->getVehicleDriver()?->getDriverRouteComment());
        }

        if (!$route->getStartCoordinates()) {
            $firstPoint = $device->getValidPreviousPoint($route->getPointStart());

            if ($firstPoint) {
                $route->setStartCoordinates(['lat' => $firstPoint->getLat(), 'lng' => $firstPoint->getLng()]);
            }
        }

        $this->em->flush();

        return $route;
    }

//    /**
//     * @param Route $lastRoute
//     * @param RouteTemp $routeTemp
//     * @param string $tempRouteTypeByClientSettings
//     * @param Device $device
//     * @return Route|null
//     * @throws \Doctrine\ORM\Exception\ORMException
//     * @throws \Doctrine\ORM\NonUniqueResultException
//     * @throws \Doctrine\ORM\OptimisticLockException
//     * @todo remove if handleLastAndNewRoute() is ok
//     *
//     */
//    protected function handleLastAndNewRouteOld(
//        Route $lastRoute,
//        RouteTemp $routeTemp,
//        string $tempRouteTypeByClientSettings,
//        Device $device
//    ): ?Route {
//        switch ($routeTemp->getType()) {
//            case RouteTemp::TYPE_IDLING:
//                if ($lastRoute->getType() === $tempRouteTypeByClientSettings) {
//                    $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getLastPoint());
//                } else {
//                    $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getPointStart());
//
//                    // to avoid routes with the same seconds
//                    if ($lastRoute->getPointStart() === $lastRoute->getPointFinish()) {
//                        $this->em->remove($lastRoute);
//                        $lastRoute = $this->deviceService->getLastRouteStartedFromDate(
//                            $device->getId(),
//                            $routeTemp->getPointStart()->getTs()
//                        );
//                        $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getLastPoint());
//                    } else {
//                        $route = $this->saveRouteEntity(
//                            $device,
//                            $routeTemp,
//                            $tempRouteTypeByClientSettings
//                        );
//                    }
//
//                }
//                break;
//            default:
//                if (Route::getRouteTypeByRouteTemp($routeTemp->getType()) === $lastRoute->getType()
//                    || $tempRouteTypeByClientSettings === $lastRoute->getType()
//                ) {
//                    $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getLastPoint());
//                } else {
//                    $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getPointStart());
//                    $route = $this->saveRouteEntity(
//                        $device,
//                        $routeTemp,
//                        $tempRouteTypeByClientSettings
//                    );
//                }
//                break;
//        }
//
//        return $lastRoute;
//    }

    protected function handleIdlingTempRoute(
        Route $lastRoute,
        RouteTemp $routeTemp,
        string $tempRouteTypeByClientSettings,
        Device $device
    ): Route {
        if ($lastRoute->getType() === $tempRouteTypeByClientSettings) {
            $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getLastPoint());
        } else {
            $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getPointStart());

            // to avoid routes with the same seconds
            if ($lastRoute->getPointStart() === $lastRoute->getPointFinish()) {
                $this->em->remove($lastRoute);
                $lastRoute = $this->deviceService->getLastRouteStartedFromDate(
                    $device->getId(),
                    $routeTemp->getPointStart()->getTs()
                );
                $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getLastPoint());
            } else {
                $route = $this->saveRouteEntity(
                    $device,
                    $routeTemp,
                    $tempRouteTypeByClientSettings
                );
            }
        }

        return $lastRoute;
    }

    protected function handleStopAndDrivingTempRoute(
        Route $lastRoute,
        RouteTemp $routeTemp,
        string $tempRouteTypeByClientSettings,
        Device $device,
        array $teamSettings
    ): Route {
        if (Route::getRouteTypeByRouteTemp($routeTemp->getType()) === $lastRoute->getType()
            || $tempRouteTypeByClientSettings === $lastRoute->getType()
        ) {
            $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getLastPoint());
        } else {
            $lastRoute = $this->extendLastRoute($lastRoute, $routeTemp->getPointStart());

            if (!$this->isRouteTypeCorrectBySettings($lastRoute, $teamSettings)) {
                $lastSecondRoute = $this->deviceService->getLastRouteStartedFromDate(
                    $device->getId(),
                    $lastRoute->getStartedAt(),
                    true
                );

                if ($lastSecondRoute) {
                    $this->em->remove($lastRoute);
                    $lastRoute = $this->extendLastRoute($lastSecondRoute, $routeTemp->getLastPoint());
                } else {
                    $lastRoute = $this->updateRouteEntity(
                        $lastRoute,
                        $device,
                        $routeTemp,
                        $tempRouteTypeByClientSettings
                    );
                }
            } else {
                if ($lastRoute->getStartedAt() == $routeTemp->getStartedAt()) {
                    $lastRoute = $this->updateRouteEntity(
                        $lastRoute,
                        $device,
                        $routeTemp,
                        $tempRouteTypeByClientSettings
                    );
                } else {
                    $route = $this->saveRouteEntity(
                        $device,
                        $routeTemp,
                        $tempRouteTypeByClientSettings
                    );
                }
            }
        }

        return $lastRoute;
    }

    protected function handleLastAndNewRoute(
        Route $lastRoute,
        RouteTemp $routeTemp,
        string $tempRouteTypeByClientSettings,
        Device $device,
        array $teamSettings
    ): ?Route {
        switch ($routeTemp->getType()) {
            case RouteTemp::TYPE_IDLING:
                $lastRoute = $this->handleIdlingTempRoute(
                    $lastRoute, $routeTemp, $tempRouteTypeByClientSettings, $device
                );
                break;
            case RouteTemp::TYPE_STOP:
            case RouteTemp::TYPE_DRIVING:
            default:
                $lastRoute = $this->handleStopAndDrivingTempRoute(
                    $lastRoute, $routeTemp, $tempRouteTypeByClientSettings, $device, $teamSettings
                );
                break;
        }

        return $lastRoute;
    }

    /**
     * @param RouteTemp $routeTemp
     * @param array $teamSettings
     * @return string
     */
    protected function getRouteTypeByClientSettings(
        RouteTemp $routeTemp,
        array $teamSettings
    ): string {
        $stopIgnoreSetting = $this->settingService->getSettingValueFromList($teamSettings, Setting::IGNORE_STOPS);
        $stopIdlingSetting = $this->settingService->getSettingValueFromList($teamSettings, Setting::IDLING);
        $movementIgnoreSetting = $this->settingService->getSettingValueFromList(
            $teamSettings,
            Setting::IGNORE_MOVEMENT
        );
        $routeType = Route::getRouteTypeByRouteTemp($routeTemp->getType());

        switch ($routeTemp->getType()) {
            case RouteTemp::TYPE_STOP:
                if ($stopIgnoreSetting['enable'] && $routeTemp->getDuration() < $stopIgnoreSetting['value']) {
                    $routeType = Route::TYPE_DRIVING;
                }

                break;
            case RouteTemp::TYPE_DRIVING:
                if (
                    $movementIgnoreSetting['enable'] &&
                    !is_null($routeTemp->getDistance()) &&
                    $routeTemp->getDistance() < $movementIgnoreSetting['value']
                ) {
                    $routeType = Route::TYPE_STOP;
                }

                break;
            case RouteTemp::TYPE_IDLING:
                if ($stopIdlingSetting['enable'] && $routeTemp->getDuration() > $stopIdlingSetting['value']) {
                    $routeType = Route::TYPE_STOP;
                }

                break;
            default:
                break;
        }

        return $routeType;
    }

    /**
     * @param Route $route
     * @param array $teamSettings
     * @return string
     */
    protected function isRouteTypeCorrectBySettings(
        Route $route,
        array $teamSettings
    ): string {
        $stopIgnoreSetting = $this->settingService->getSettingValueFromList($teamSettings, Setting::IGNORE_STOPS);
        $movementIgnoreSetting = $this->settingService->getSettingValueFromList(
            $teamSettings,
            Setting::IGNORE_MOVEMENT
        );

        switch ($route->getType()) {
            case Route::TYPE_STOP:
                if ($stopIgnoreSetting['enable'] && $route->getDuration() < $stopIgnoreSetting['value']) {
                    return false;
                }

                break;
            case Route::TYPE_DRIVING:
            default:
                if ($movementIgnoreSetting['enable']
                    && !is_null($route->getDistance())
                    && $route->getDistance() < $movementIgnoreSetting['value']
                ) {
                    return false;
                }

                break;
        }

        return true;
    }

    public function getLastPointWithTheSameTypeFromRouteTemp(
        RouteTemp $route,
        string $routeType
    ): ?TrackerHistory {
        $lastTrackerHistoryWithTheSameType = null;
        $trackerHistoriesSql = $this->em->getRepository(TrackerHistory::class)->getHistoriesByRouteTemp($route);

        /** @var TrackerHistory $trackerHistory */
        foreach ($trackerHistoriesSql->toIterable() as $trackerHistoryDatum) {
            if ($routeType == TrackerHistory::getRouteTempTypeByIgnitionAndMovement(
                    $trackerHistoryDatum['ignition'],
                    $trackerHistoryDatum['movement']
                )
            ) {
                $lastTrackerHistoryWithTheSameType = $trackerHistoryDatum;
            } else {
                break;
            }
        }

        return $lastTrackerHistoryWithTheSameType
            ? $this->em->find(TrackerHistory::class, $lastTrackerHistoryWithTheSameType['id'])
            : null;
    }

    public function getLastPointWithTheSameTypeFromRouteTempTHT(
        RouteTemp $route,
        string $routeType
    ): ?TrackerHistory {
        $lastTrackerHistoryWithTheSameType = null;
        $trackerHistoriesTempSql = $this->em->getRepository(TrackerHistoryTemp::class)
            ->getHistoriesDataByRouteTemp($route);

        foreach ($trackerHistoriesTempSql->toIterable() as $key => $trackerHistoryTempRow) {
            $trackerHistoryTemp = array_shift($trackerHistoryTempRow);

            if ($routeType == TrackerHistory::getRouteTempTypeByIgnitionAndMovement(
                    $trackerHistoryTemp['ignition'],
                    $trackerHistoryTemp['movement']
                )
            ) {
                $lastTrackerHistoryWithTheSameType = $trackerHistoryTemp;
            } else {
                break;
            }
        }

        return $lastTrackerHistoryWithTheSameType
            ? $this->em->find(TrackerHistory::class, $lastTrackerHistoryWithTheSameType['th_id'])
            : null;
    }

    protected function saveRouteTempEntity(
        Device $device,
        TrackerHistory $firstPoint,
        string $currentRouteType
    ): RouteTemp {
        $route = new RouteTemp();
        $route->setDevice($device);
        $route->setPointStart($firstPoint);
        $route->setStartedAt($firstPoint->getTs());
        $route->setType($currentRouteType);
        $route->setDriver($device->getVehicle() ? $device->getVehicle()->getDriver() : null);
        $route->setVehicle($device->getVehicle());
        $route->setMaxSpeed($firstPoint->getSpeed());
        $route->setAvgSpeed($firstPoint->getSpeed());

        $this->em->persist($route);
        $this->em->flush();

        return $route;
    }

    protected function handleLastAndNewTempRoute(
        RouteTemp $lastRoute,
        TrackerHistory $trackerHistory,
        string $routeTypeByTrackerHistory,
        Device $device
    ): ?RouteTemp {
        $this->extendLastRouteTemp($lastRoute, $trackerHistory);

        if ($routeTypeByTrackerHistory != $lastRoute->getType()) {
            $lastPointWithSameType = $this->getLastPointWithTheSameTypeFromRouteTemp(
                $lastRoute,
                $routeTypeByTrackerHistory
            );
            $startPointOfNewRoute = $trackerHistory;

            if ($lastPointWithSameType) {
                $this->extendLastRouteTemp($lastRoute, $lastPointWithSameType);
                $startPointOfNewRoute = $lastPointWithSameType;
            }

            $route = $this->saveRouteTempEntity(
                $device,
                $startPointOfNewRoute,
                $routeTypeByTrackerHistory
            );
        }

        return $lastRoute;
    }

    /**
     * @param Route $route
     * @return Route
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateRouteDriver(Route $route): Route
    {
        $vehicle = $route->getVehicle();
        $driver = $route->getDriver();

        if ($vehicle && $driver) {
            $startPoint = $route->getPointStart();
            $driverHistory = $this->em->getRepository(DriverHistory::class)
                ->findDriverByDateRange($vehicle, $startPoint->getTs());

            if ($driverHistory && ($driver->getId() != $driverHistory->getDriver()->getId())) {
                $route->setDriver($driverHistory->getDriver());
            }
        }

        return $route;
    }

    /**
     * @param Route $route
     * @param \DateTimeInterface $dateTo
     * @param array $include
     * @param bool $withLastCoordinates
     * @return array|null
     * @throws \Exception
     */
    public function getNextRouteAsPartialRoute(
        Route $route,
        \DateTimeInterface $dateTo,
        $include = [],
        $withLastCoordinates = false,
        $optimization = false
    ): ?array {
        $nextRoute = $this->em->getRepository(Route::class)->getNextRoute($route);
        $nextRoute = $nextRoute && ($nextRoute->getStartedAt() <= $dateTo)
            ? $nextRoute
            : null;

        if ($nextRoute) {
            $coordinatesNeeded = [];
            $this->setCoordinatesToRoute($nextRoute, $include, $withLastCoordinates, $optimization);

            if ($nextRoute->getCoordinates()) {
                /** @var TrackerHistory $coordinate */
                foreach ($nextRoute->getCoordinates() as $coordinate) {
                    if ($coordinate['ts'] <= $dateTo) {
                        $coordinatesNeeded[] = $coordinate;
                    }
                }
            }

            $finishedRoute = $nextRoute->toArray($include);
        }

        return $finishedRoute ?? null;
    }

    /**
     * @param array $params
     * @param User $user
     * @return mixed
     */
    public function fbtReportVehicles(array $params, User $user)
    {
        $params = UserServiceHelper::handleTeamParams($params, $user);
        $params['status'] = BaseEntity::STATUS_ALL;

        $vehicleIds = array_column(
            $this->emSlave->getRepository(Route::class)
                ->getRoutesFbt(new FbtReportDTO($params), true)->execute()->fetchAll()
            ,
            'id'
        );
        unset($params['groups']);
        unset($params['depot']);
        $listParams = array_merge($params, ['id' => $vehicleIds]);

        return $this->vehicleService->vehicleList($listParams, $user, true, Vehicle::REPORT_VALUES);
    }

    /**
     * @param array $params
     * @param User $user
     * @param bool $paginated
     * @return mixed
     */
    public function routeReportVehicles(array $params, User $user, $paginated = true)
    {
        $vehicleData = RouteByVehicleReportBuilder::getRouteSummaryVehiclesData($params, $user, $this->emSlave);
        $vehicleIds = array_column($vehicleData, 'id');
        $vehicleTotalData = RouteReportHelper::prepareRouteReportTotalData($vehicleData);

        unset($params['groups']);
        unset($params['depot']);
        unset($params['driverId']);
        $listParams = array_merge($params, ['id' => $vehicleIds]);
        $result = $this->vehicleService->vehicleList($listParams, $user, $paginated, Vehicle::REPORT_VALUES);

        return array_merge($result, [BaseController::ADDITIONAL_FIELDS => ['total' => $vehicleTotalData]]);
    }

    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public function stopsReportVehicles(array $params, User $user)
    {
        $vehicleData = StopByVehicleReportBuilder::getStopsVehiclesData($params, $user, $this->emSlave);

        $vehicleIds = array_column($vehicleData, 'id');
        $vehicleTotalData = RouteReportHelper::prepareStopRouteReportTotalData($vehicleData);

        unset($params['groups']);
        unset($params['depot']);
        $listParams = array_merge($params, ['id' => $vehicleIds]);
        $result = $this->vehicleService->vehicleList($listParams, $user, true, Vehicle::REPORT_VALUES);

        return array_merge($result, [BaseController::ADDITIONAL_FIELDS => ['total' => $vehicleTotalData]]);
    }

    /**
     * @param array $routes
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addAddressToRoutes(array $routes)
    {
        foreach ($routes as &$route) {
            if (isset($route['type']) && $route['type'] === Route::TYPE_DRIVING) {
                $prevStopRoute = $route['pointStart']['lastCoordinates']['ts']
                    ? $this->em->getRepository(Route::class)
                        ->getPreviousStopRoute($route['deviceId'], $route['pointStart']['lastCoordinates']['ts'])
                    : null;
                if ($prevStopRoute) {
                    $route['pointStart']['address'] = $prevStopRoute->getAddress();
                }
                $nextStopRoute = $route['pointFinish']['lastCoordinates']['ts']
                    ? $this->em->getRepository(Route::class)
                        ->getNextStopRoute($route['deviceId'], $route['pointFinish']['lastCoordinates']['ts'])
                    : null;
                if ($nextStopRoute) {
                    $route['pointFinish']['address'] = $nextStopRoute->getAddress();
                }
            }

            if (isset($route['type']) && $route['type'] === Route::TYPE_STOP) {
                $route['pointFinish']['address'] = $route['address'];
                $route['pointStart']['address'] = $route['address'];
            }
        }

        return $routes;
    }

    /**
     * @param $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @throws \Exception
     */
    public function vehicleDGFormEvent($deviceId, $dateFrom, $dateTo)
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);

        /** @var Event $event */
        $event = $this->em->getRepository(Event::class)->getEventByName(Event::DIGITAL_FORM_IS_NOT_COMPLETED);

        $routes = $this->em->getRepository(Route::class)
            ->getRoutesByDateRangeAndDevice($device, $dateFrom, $dateTo, null, Route::TYPE_DRIVING);

        foreach ($routes as $route) {
            if (!$this->isRouteValidToTriggerEvent($route)) {
                continue;
            }

            $vehicle = $route->getVehicle() ?? null;
            $driver = $vehicle?->getDriver() ?? null;

            if ($vehicle && $driver) {
                $formsNotCompleted = $this->digitalFormService
                    ->checkInspectionFormComplete($vehicle, $driver, $dateFrom);

                if (!is_null($formsNotCompleted)) {
                    $ts = clone $route->getCreatedAt();
                    $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailId(
                        $event,
                        $route->getId()
                    );

                    if (!count($eventLogs)) {
                        $formData[$formsNotCompleted->getId()] = $formsNotCompleted->getTitle();

                        $this->notificationDispatcher->dispatch(
                            Event::DIGITAL_FORM_IS_NOT_COMPLETED,
                            $route,
                            null,
                            ['form' => $formData]
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @throws \Exception
     */
    public function vehicleStandingEvent($deviceId, $dateFrom, $dateTo)
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);
        /** @var Event $event */
        $event = $this->em->getRepository(Event::class)->getEventByName(Event::VEHICLE_LONG_STANDING);

        $longStandingDuration = $this->em->getRepository(Notification::class)->getTeamNotificationsParamValue(
            $event, $device->getTeam(), new \DateTime(), Notification::TIME_DURATION
        );

        if (!$longStandingDuration) {
            return;
        }

        $routes = $this->em->getRepository(Route::class)
            ->getRoutesByDateRangeAndDevice($device, $dateFrom, $dateTo, $longStandingDuration, Route::TYPE_STOP);

        foreach ($routes as $route) {
            if (!$this->isRouteValidToTriggerEvent($route)) {
                continue;
            }

            $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailId($event, $route->getId());

            if (!count($eventLogs)) {
                $this->notificationDispatcher->dispatch(Event::VEHICLE_LONG_STANDING, $route);
            }
        }
    }

    /**
     * @param $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @throws \Exception
     */
    public function vehicleDrivingEvent($deviceId, $dateFrom, $dateTo)
    {
        try {
            $device = $this->em->getRepository(Device::class)->find($deviceId);

            /** @var Event $event */
            $event = $this->em->getRepository(Event::class)->getEventByName(Event::VEHICLE_LONG_DRIVING);

            $longDrivingDurationValue = $this->em->getRepository(Notification::class)
                ->getTeamNotificationsParamValue(
                    $event, $device->getTeam(), new \DateTime(), Notification::TIME_DURATION);

            if (!$longDrivingDurationValue) {
                return;
            }

            $longDrivingDistanceValue = $this->em->getRepository(Notification::class)
                ->getTeamNotificationsParamValue($event, $device->getTeam(), new \DateTime(), Notification::DISTANCE);

            $longDrivingDistanceValue = is_null($longDrivingDistanceValue) ? 0 : (int)$longDrivingDistanceValue;

            $routes = $this->em->getRepository(Route::class)->getRoutesByDateRangeAndDevice(
                $device, $dateFrom, $dateTo, $longDrivingDurationValue, Route::TYPE_DRIVING, $longDrivingDistanceValue
            );

            foreach ($routes as $route) {
                if (!$this->isRouteValidToTriggerEvent($route)) {
                    $this->logger->info('not valid route: ',
                        ['route id' => $route->getId()]);
                    continue;
                }

                $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailId($event, $route->getId());

                if (!count($eventLogs)) {
                    $this->notificationDispatcher->dispatch(Event::VEHICLE_LONG_DRIVING, $route);
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception));
        }
    }

    /**
     * @param $deviceId
     * @param $dateFrom
     * @param $dateTo
     * @throws \Exception
     */
    public function vehicleMovingEvent($deviceId, $dateFrom, $dateTo)
    {
        $device = $this->em->getRepository(Device::class)->find($deviceId);
        /** @var Event $event */
        $event = $this->em->getRepository(Event::class)->getEventByName(Event::VEHICLE_MOVING);

        $routes = $this->em->getRepository(Route::class)
            ->getRoutesByDateRangeAndDevice($device, $dateFrom, $dateTo, null, Route::TYPE_DRIVING);

        foreach ($routes as $route) {
            if (!$this->isRouteValidToTriggerEvent($route)) {
                continue;
            }

            $eventLogs = $this->em->getRepository(EventLog::class)->findEventLogByDetailId(
                $event,
                $route->getId()
            );

            if (!count($eventLogs)) {
                $this->notificationDispatcher->dispatch(Event::VEHICLE_MOVING, $route);
            }
        }
    }


    /**
     * @param Route $route
     * @return Route
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateRouteSpeedStats(Route $route): Route
    {
        if ($route->isStopType()) {
            return $route;
        }

        $startPointInHistoryTempTable = $this->em->getRepository(TrackerHistoryTemp::class)
            ->hasStartPointOfRoute($route);
        $finishPointInHistoryTempTable = $this->em->getRepository(TrackerHistoryTemp::class)
            ->hasFinishPointOfRoute($route);

//        $totalSpeed = $maxSpeed = 0;
//        $routePointsCount = 0;
        $routeSpeed = ($startPointInHistoryTempTable && $finishPointInHistoryTempTable)
            ? $this->em->getRepository(TrackerHistoryTemp::class)->getSpeedsForRouteStatsQuery($route)
            : $this->em->getRepository(TrackerHistory::class)->getSpeedsForRouteStatsQuery($route);

//        foreach ($routeSpeedPointsQuery->toIterable() as $row) {
//            $speed = array_shift($row);
//            $totalSpeed += $speed;
//
//            if ($speed > $maxSpeed) {
//                $route->setMaxSpeed($speed);
//                $maxSpeed = $speed;
//            }
//
//            $routePointsCount++;
//        }

        if ($routeSpeed) {
            $route->setMaxSpeed($routeSpeed['maxSpeed'] ?? 0);
            $route->setAvgSpeed($routeSpeed['avgSpeed'] ?? 0);
        }

//        if ($routePointsCount > 0) {
//            $route->setAvgSpeed($totalSpeed / $routePointsCount);
//        }

        return $route;
    }

    /**
     * @param int $deviceId
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function handleEventsForCalculatedRoutesOfDevice(int $deviceId, string $dateFrom, string $dateTo)
    {
        $message = new RoutePostHandleMessage($deviceId, $dateFrom, $dateTo);
        $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
            $deviceId,
            RoutePostHandleConsumer::QUEUES_NUMBER,
            RoutePostHandleConsumer::ROUTING_KEY_PREFIX
        );
        $this->routePostHandleProducer->publish($message, $routingKey);
    }

    /**
     * @param int $deviceId
     * @param string $dateFrom
     * @param string $dateTo
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function handleEventsForRecalculatedRoutesOfDevice(int $deviceId, string $dateFrom, string $dateTo)
    {
        $this->updateRoutesWithWrongFinishPoints($deviceId, $dateFrom, $dateTo);
        $this->updateRoutesPostponedData($deviceId, $dateFrom, $dateTo);
    }

    /**
     * @param Route $route
     * @return Route
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateRouteTotalStats(Route $route): Route
    {
        $totalIdleDuration = $this->em->getRepository(RouteTemp::class)
            ->getDurationByRouteAndTypeQuery($route, RouteTemp::TYPE_IDLING);
        $totalStopDuration = $this->em->getRepository(RouteTemp::class)
            ->getDurationByRouteAndTypeQuery($route, RouteTemp::TYPE_STOP);
        $totalMovementDuration = $this->em->getRepository(RouteTemp::class)
            ->getDurationByRouteAndTypeQuery($route, RouteTemp::TYPE_DRIVING);
        $route->setTotalIdleDuration($totalIdleDuration);
        $route->setTotalStopDuration($totalStopDuration);
        $route->setTotalMovementDuration($totalMovementDuration);

        return $route;
    }

    public function daySummaryReportVehicles(array $params, User $user, $paginated = true)
    {
        return VehicleDaySummaryReportBuilder::getRouteSummaryVehiclesData(
            $params, $user, $this->emSlave, $this->vehicleService, $paginated
        );
    }

    public function setRouteDriver(Route $route, User $driver, $unassign = false): Route
    {
        $connection = $this->em->getConnection();

        try {
            if (!$route->getVehicle()) {
                throw new \Exception('Route doesn\'t have vehicle');
            }

            $dhWithAnotherVehicle = $this->em->getRepository(DriverHistory::class)
                ->findAnotherDHByDateRange(
                    $route->getVehicle(), $driver, $route->getStartedAt(), $route->getFinishedAt()
                );

            if (count($dhWithAnotherVehicle)) {
                $routes = $this->em->getRepository(Route::class)->getRoutesByDriverForUnassign(
                    $driver, $route->getVehicle(), $route->getStartedAt(), $route->getFinishedAt());
//                $routes = array_filter($routes, fn(Route $r) => $r->getId() !== $route->getId());

                if ($unassign) {
                    foreach ($routes as $r) {
                        $this->unsetRouteDriver($r);
                    }
                } elseif ($routes) {
                    $fields = [
                        'id',
                        'type',
                        'pointStart',
                        'pointFinish',
                        'regNo',
                        'vehicleId',
                    ];
                    throw (new AssignRouteDriverException())->setContext([
                        'route' => $route->toArray($fields),
                        'unassignRoutes' => array_map(fn(Route $r) => $r->toArray($fields), $routes)
                    ]);
//                    /** @var DriverHistory $dh */
//                    $dh = reset($dhWithAnotherVehicle);
//                    $error = $this->translator->trans(
//                        'entities.vehicle.assign_another_vehicle',
//                        [
//                            '%driver%' => $driver->getFullName(),
//                            '%regno%' => $dh->getVehicle()->getRegNo(),
//                            '%selected_regno%' => $route->getVehicle()->getRegNo(),
//                            '%time%' => $dh->getStartDate()->setTimezone(new \DateTimeZone($driver->getTimezone()))
//                                ->format($driver->getDateFormatSettingConverted()),
//                        ]
//                    );
//                    throw new \Exception($error);
                }
            }

            $connection->beginTransaction();
            $route->setDriver($driver);
            $this->vehicleService
                ->updateDriver($route->getVehicle(), $driver, $route->getStartedAt(), $route->getFinishedAt());

            $this->em->getRepository(TrackerHistory::class)->updateTHsDriverByRoute($route);
            $this->em->getRepository(Speeding::class)->updateSpeedingDriverByRoute($route);
            $this->em->getRepository(Idling::class)->updateIdlingDriverByRoute($route);

            $this->em->flush();

            $connection->commit();

            return $route;
        } catch (\Throwable $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function getDriverRouteScopeByDevice(Device $device): ?string
    {
        $driverRouteScope = $device->getVehicleDriver()?->getDriverRouteScope();

        return $driverRouteScope
            ?: $this->settingService->getTeamSettingValueByKey($device->getTeam(), Setting::ROUTE_SCOPE);
    }

    public function unsetRouteDriver(Route $route)
    {
        $startDate = $route->getStartedAt();
        $finishDate = $route->getFinishedAt();

        $connection = $this->em->getConnection();

        try {
            if (!$route->getVehicle()) {
                throw new \Exception('Route doesn\'t have vehicle');
            }

            $dhs = $this->em->getRepository(DriverHistory::class)
                ->findDriverHistoryByDateRange($route->getVehicle(), $route->getStartedAt(), $route->getFinishedAt());

            $connection->beginTransaction();
            $route->setDriver(null);

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

            $this->em->getRepository(TrackerHistory::class)->updateTHsDriverByRoute($route);
            $this->em->getRepository(Speeding::class)->updateSpeedingDriverByRoute($route);
            $this->em->getRepository(Idling::class)->updateIdlingDriverByRoute($route);

            $this->em->flush();

            $connection->commit();

            return $route;
        } catch (\Throwable $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function isLocationChecked(): bool
    {
        return $this->isLocationChecked;
    }

    /**
     * @param bool $isLocationChecked
     */
    public function setIsLocationChecked(bool $isLocationChecked): void
    {
        $this->isLocationChecked = $isLocationChecked;
    }

    public function triggerRouteAreaProducer(array $routeIds)
    {
        /** @var Route $route */
        foreach ($routeIds as $routeItem) {
            if (!isset($routeItem['routeId']) || !isset($routeItem['deviceId'])) {
                continue;
            }

            $route = $this->em->getRepository(Route::class)->find($routeItem['routeId']);

            if (!$route) {
                continue;
            }

            $area = $this->em->getRepository(Area::class)->count([
                'team' => $route->getDevice()->getTeam(),
                'status' => Area::STATUS_ACTIVE
            ]);

            if (!$area) {
                continue;
            }

            $eventMessage = new RouteAreaMessage($route->getId());
            $routingKey = $this->deviceService->getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
                $routeItem['deviceId'],
                RouteAreaConsumer::QUEUES_NUMBER,
                RouteAreaConsumer::ROUTING_KEY_PREFIX
            );
            $this->routeAreaProducer->publish($eventMessage, $routingKey);
        }
    }
}