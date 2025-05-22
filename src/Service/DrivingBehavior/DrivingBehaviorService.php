<?php

namespace App\Service\DrivingBehavior;

use App\Entity\DeviceModel;
use App\Entity\DrivingBehavior;
use App\Entity\Idling;
use App\Entity\Setting;
use App\Entity\Speeding;
use App\Entity\TimeZone;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\User;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Report\Builder\DrivingBehaviour\DrivingBehaviourReportHelper;
use App\Service\BaseService;
use App\Service\Idling\IdlingService;
use App\Service\Redis\RedisService;
use App\Service\Setting\SettingService;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\FM3001;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\FM36M1;
use App\Service\Tracker\Parser\Teltonika\SensorEventTypes\FMB920;
use App\Service\User\UserService;
use App\Service\Vehicle\VehicleService;
use App\Util\DateHelper;
use App\Util\GeoHelper;
use App\Util\RequestFilterResolver\RequestFilterResolver;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class DrivingBehaviorService extends BaseService
{
    public const DEFAULT_ECO_SPEED = Setting::ECO_SPEED_VALUE['value'];
    public const SENSOR_EVENT_TO_BEHAVIOR_PARAM = [
        DeviceModel::TELTONIKA_FM3001 => [
            FM3001::GREEN_DRIVING_TYPE_ID => [
                1 => 'harsh_acceleration',
                2 => 'harsh_braking',
                3 => 'harsh_cornering',
            ],
        ],
        DeviceModel::TELTONIKA_FM36M1 => [
            FM36M1::GREEN_DRIVING_TYPE_ID => [
                1 => 'harsh_acceleration',
                2 => 'harsh_braking',
                3 => 'harsh_cornering',
            ],
        ],
        DeviceModel::TELTONIKA_FMB920 => [
            FMB920::GREEN_DRIVING_TYPE_ID => [
                1 => 'harsh_acceleration',
                2 => 'harsh_braking',
                3 => 'harsh_cornering',
            ],
        ],
    ];
    public const CALCULATION_TYPE_ROUTE = 'route';
    public const CALCULATION_TYPE_TRACKER_HISTORY = 'trackerHistory';

    private $em;
    private $vehicleService;
    private $settingService;
    private $idlingService;
    private $userService;
    protected $translator;
    protected $dashboardCache;
    protected $emSlave;

    /**
     * DrivingBehaviorService constructor.
     * @param EntityManager $em
     * @param VehicleService $vehicleService
     * @param SettingService $settingService
     * @param IdlingService $idlingService
     * @param UserService $userService
     * @param TranslatorInterface $translator
     * @param RedisService $dashboardCache
     * @param SlaveEntityManager $emSlave
     */
    public function __construct(
        EntityManager $em,
        VehicleService $vehicleService,
        SettingService $settingService,
        IdlingService $idlingService,
        UserService $userService,
        TranslatorInterface $translator,
        RedisService $dashboardCache,
        SlaveEntityManager $emSlave
    ) {
        $this->em = $em;
        $this->vehicleService = $vehicleService;
        $this->settingService = $settingService;
        $this->idlingService = $idlingService;
        $this->userService = $userService;
        $this->translator = $translator;
        $this->dashboardCache = $dashboardCache;
        $this->emSlave = $emSlave;
    }

    /**
     * @param TrackerHistory $trackerHistory
     * @return array
     */
    public function getInsertParamsFromHistory(TrackerHistory $trackerHistory)
    {
        $device = $trackerHistory->getDevice();

        return [
            'tracker_history_id' => $trackerHistory->getId(),
            'device_id' => $device->getId(),
            'vehicle_id' => $device->getVehicle() ? $device->getVehicle()->getId() : null,
            'driver_id' => $device->getVehicle() && $device->getVehicle()->getDriver()
                ? $device->getVehicle()->getDriver()->getId()
                : null,
            'ts' => $trackerHistory->getTs()->format('Y-m-d H:i:s'),
            'speed' => $trackerHistory->getSpeed(),
            'odometer' => $trackerHistory->getOdometer(),
            'lng' => $trackerHistory->getLng(),
            'lat' => $trackerHistory->getLat(),
        ];
    }

    /**
     * @param $modelName
     * @param $remoteId
     * @param $value
     * @param $params
     */
    public function setParamBySensorEvent($modelName, $remoteId, $value, &$params)
    {
        $map = self::SENSOR_EVENT_TO_BEHAVIOR_PARAM;

        if (isset($map[$modelName][$remoteId])) {
            $mapping = $map[$modelName][$remoteId];

            if (is_string($mapping)) {
                $params[$mapping] = 1;
            } elseif (is_array($mapping)) {
                $params[$mapping[$value]] = 1;
            }
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param array $params
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function getVehicleScores(Vehicle $vehicle, array $params): array
    {
        $params = DrivingBehaviourReportHelper::convertDatesToUTC($params);
        $totalDistance = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometer($vehicle, $params['startDate'], $params['endDate']);
        $drivingTotalTime = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalDrivingTime($vehicle, $params['startDate'], $params['endDate']);
        $totalAvgSpeed = $drivingTotalTime && $totalDistance
            ? (($totalDistance / 1000) / ($drivingTotalTime / 3600))
            : null;

        $params = array_merge(
            $params,
            [
                'totalDistance' => $totalDistance
            ]
        );

        $data = $this->emSlave->getRepository(DrivingBehavior::class)->getVehicleScores($params);

        $result = [
            'vehicleId' => $vehicle->getId(),
            'harshAccelerationScore' => DrivingBehaviourReportHelper::roundFloatScore($data['harshaccelerationscore']),
            'harshBrakingScore' => DrivingBehaviourReportHelper::roundFloatScore($data['harshbrakingscore']),
            'harshCorneringScore' => DrivingBehaviourReportHelper::roundFloatScore($data['harshcorneringscore']),
            'idlingScore' => DrivingBehaviourReportHelper::roundFloatScore($data['excessiveidlingscore']),
            'speedingScore' => DrivingBehaviourReportHelper::roundFloatScore($data['speeding']),
            'drivingTotalTime' => $drivingTotalTime,
            'totalDistance' => $totalDistance,
            'totalAvgSpeed' => $totalAvgSpeed,
        ];
        $result['totalScore'] = DrivingBehaviourReportHelper::roundFloatScore(
            DrivingBehaviourReportHelper::calcTotalScore(
                $result,
                [
                    'harshAccelerationScore',
                    'harshBrakingScore',
                    'harshCorneringScore',
                    'idlingScore',
                    'speedingScore',
                ]
            )
        );

        return $result;
    }

    /**
     * @param User $driver
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getDriverScores(User $driver, array $params): array
    {
        $params = DrivingBehaviourReportHelper::convertDatesToUTC($params);
        $totalDistance = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalOdometerByDriver($driver, $params['startDate'], $params['endDate']);
        $drivingTotalTime = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalDrivingTimeByDriver($driver, $params['startDate'], $params['endDate']);
        $totalAvgSpeed = $drivingTotalTime && $totalDistance
            ? (($totalDistance / 1000) / ($drivingTotalTime / 3600))
            : null;
        /** @var Setting $excessiveIdling */
        $excessiveIdling = $this->settingService->getExcessiveIdlingValueForTeam($driver->getTeam());

        $params = array_merge(
            $params,
            [
                'totalDistance' => $totalDistance,
                'excessiveIdling' => $excessiveIdling,
            ]
        );

        $data = $this->emSlave->getRepository(DrivingBehavior::class)->getDriverScores($params);

        $result = [
            'driverId' => $driver->getId(),
            'harshAccelerationScore' => DrivingBehaviourReportHelper::roundFloatScore($data['harshaccelerationscore']),
            'harshBrakingScore' => DrivingBehaviourReportHelper::roundFloatScore($data['harshbrakingscore']),
            'harshCorneringScore' => DrivingBehaviourReportHelper::roundFloatScore($data['harshcorneringscore']),
            'idlingScore' => DrivingBehaviourReportHelper::roundFloatScore($data['excessiveidlingscore']),
            'speedingScore' => DrivingBehaviourReportHelper::roundFloatScore($data['speeding']),
            'drivingTotalTime' => $drivingTotalTime,
            'totalDistance' => $totalDistance,
            'totalAvgSpeed' => $totalAvgSpeed,
        ];
        $result['totalScore'] = DrivingBehaviourReportHelper::roundFloatScore(
            DrivingBehaviourReportHelper::calcTotalScore(
                $result,
                [
                    'harshAccelerationScore',
                    'harshBrakingScore',
                    'harshCorneringScore',
                    'idlingScore',
                    'speedingScore',
                ]
            )
        );

        return $result;
    }

    /**
     * @param User $user
     * @param Vehicle $vehicle
     * @param array $params
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function getVehicleEventsCount(User $user, Vehicle $vehicle, array $params): array
    {
        $totalDistance = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometer($vehicle, $params['startDate'], $params['endDate']);
        $drivingTotalTime = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalDrivingTime($vehicle, $params['startDate'], $params['endDate']);
        /** @var Setting $excessiveIdling */
        $excessiveIdling = $this->settingService->getExcessiveIdlingValue($vehicle);
        $params = array_merge(
            $params,
            [
                'totalDistance' => $totalDistance,
                'drivingTotalTime' => $drivingTotalTime,
                'excessiveIdling' => $excessiveIdling,
            ]
        );

        $data = $this->emSlave->getRepository(DrivingBehavior::class)->getVehicleEventsCountWithScores($params);

        $result = [
            'vehicleId' => $vehicle->getId(),
            'harshAccelerationCount' => DrivingBehaviourReportHelper::formatEventCount($data['harshaccelerationcount']),
            'harshBrakingCount' => DrivingBehaviourReportHelper::formatEventCount($data['harshbrakingcount']),
            'harshCorneringCount' => DrivingBehaviourReportHelper::formatEventCount($data['harshcorneringcount']),
            'totalDistance' => $totalDistance,
            'idlingCount' => DrivingBehaviourReportHelper::formatEventCount($data['idlingcount']),
            'ecoSpeedEventCount' => DrivingBehaviourReportHelper::formatEventCount($data['ecospeedeventcount']),
            'drivingTotalTime' => $drivingTotalTime,
        ];
        $overallScore = DrivingBehaviourReportHelper::roundFloatScore(
            DrivingBehaviourReportHelper::calcTotalScore(
                $data,
                [
                    'harshaccelerationscore',
                    'harshbrakingscore',
                    'harshcorneringscore',
                    'excessiveidlingscore',
                    'ecospeedscore',
                ]
            )
        );
        $result['overallScore'] = ($overallScore == 0) ? null : $overallScore;

        return $result;
    }

    /**
     * @param Vehicle $vehicle
     * @param Request $request
     *
     * @return array
     */
    public function getVehicleEcoSpeedDetails(Vehicle $vehicle, Request $request): array
    {
        $params = RequestFilterResolver::resolve($request->query->all());
        $params['vehicleId'] = $vehicle->getId();
        $result = $this->emSlave->getRepository(DrivingBehavior::class)->getVehicleEcoSpeedDetails($params);

        foreach ($result as &$item) {
            $item['startDate'] = DateHelper::formatDate($item['startDate']);
            $item['endDate'] = DateHelper::formatDate($item['endDate']);

            if ($item['coordinates']) {
                $item['coordinates'] = $this->explodeCoordinates($item['coordinates']);
            }
        }

        $totalDistance = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometer($vehicle, $params['startDate'], $params['endDate']);
        $speedingCount = $this->emSlave->getRepository(DrivingBehavior::class)->getSpeedingCountByVehicle($vehicle);
        $score = $this->emSlave->getRepository(DrivingBehavior::class)->getScoreByDistanceAndCount(
            $totalDistance,
            $speedingCount
        );

        return [
            'data' => $result,
            'score' => DrivingBehaviourReportHelper::roundFloatScore($score),
        ];
    }

    /**
     * @param string $type
     * @param Vehicle $vehicle
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getVehicleHarshDetails(string $type, Vehicle $vehicle, Request $request)
    {
        $params = $request->query->all();
        $params = DrivingBehaviourReportHelper::convertDatesToUTC($params);
        $result = $this->emSlave->getRepository(DrivingBehavior::class)->getVehicleHarshDetails(
            $type,
            array_merge(
                [
                    'vehicleId' => $vehicle->getId(),
                ],
                DrivingBehaviourReportHelper::prepareDatePeriod($params)
            )
        );
        $totalDistance = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometer($vehicle, $params['startDate'], $params['endDate']);
        $score = $this->emSlave->getRepository(DrivingBehavior::class)->getScoreByDistanceAndCount(
            $totalDistance,
            count($result)
        );

        $result = array_map(
            function ($item) {
                return GeoHelper::convertCoordinatesForResponse($item);
            },
            $result
        );

        return [
            'data' => $result,
            'score' => DrivingBehaviourReportHelper::roundFloatScore($score)
        ];
    }

    /**
     * @param User $user
     * @param Vehicle $vehicle
     * @param Request $request
     *
     * @return array
     */
    public function getVehicleIdlingDetails(Vehicle $vehicle, Request $request)
    {
        $params = RequestFilterResolver::resolve($request->query->all());
        $params['vehicleId'] = $vehicle->getId();
        $params['excessiveIdling'] = $this->settingService->getExcessiveIdlingValue($vehicle);
        $result = $this->emSlave->getRepository(DrivingBehavior::class)->getVehicleIdlingDetails($params);

        foreach ($result as &$item) {
            $item['startDate'] = DateHelper::formatDate($item['startDate']);
            $item['endDate'] = DateHelper::formatDate($item['endDate']);
        }

        $totalDistance = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometer($vehicle, $params['startDate'], $params['endDate']);
        $idlingCount = $this->emSlave->getRepository(DrivingBehavior::class)->getIdlingCountByVehicle($vehicle);
        $score = $this->emSlave->getRepository(DrivingBehavior::class)->getScoreByDistanceAndCount(
            $totalDistance,
            $idlingCount
        );

        return [
            'data' => $result,
            'score' => DrivingBehaviourReportHelper::roundFloatScore($score),
        ];
    }

    /**
     * @param User $user
     * @param $vehicleId
     * @return array
     * @throws \Exception
     */
    public function getVehicleSpeeding(User $user, $vehicleId)
    {
        $vehicle = $this->vehicleService->getById($vehicleId, $user);
        if (!$vehicle) {
            return [];
        }

        $vehicleSpeeding = $this->em->getRepository(Speeding::class)->getVehicleSpeeding($vehicleId);

        return array_map(
            function (Speeding $speeding) {
                return $speeding->toArray();
            },
            $vehicleSpeeding
        );
    }

    /**
     * @param string $coordinatesAsString
     * @param string|null $coordinatesDelimiter
     * @param string|null $latLngDelimeter
     *
     * @return array
     */
    protected function explodeCoordinates(
        string $coordinatesAsString,
        ?string $coordinatesDelimiter = ', ',
        ?string $latLngDelimeter = ' '
    ): array {
        $coordinates = [];
        foreach (explode($coordinatesDelimiter, $coordinatesAsString) as $coordinate) {
            $coordinate = explode($latLngDelimeter, $coordinate);
            $coordinates[] = [
                'lat' => $coordinate[0],
                'lng' => $coordinate[1],
            ];
        }

        return $coordinates;
    }

    /**
     * @param User $driver
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getDriverEventsCount(User $driver, array $params): array
    {
        $totalDistance = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalOdometerByDriver($driver, $params['startDate'], $params['endDate']);
        $drivingTotalTime = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalDrivingTimeByDriver($driver, $params['startDate'], $params['endDate']);
        /** @var Setting $excessiveIdling */
        $excessiveIdling = $this->settingService->getExcessiveIdlingValueForTeam($driver->getTeam());
        $params = array_merge(
            $params,
            [
                'totalDistance' => $totalDistance,
                'drivingTotalTime' => $drivingTotalTime,
                'excessiveIdling' => $excessiveIdling,
            ]
        );

        $data = $this->emSlave->getRepository(DrivingBehavior::class)->getDriverEventsCountWithScores($params);

        $result = [
            'vehicleId' => $driver->getId(),
            'harshAccelerationCount' => DrivingBehaviourReportHelper::formatEventCount($data['harshaccelerationcount']),
            'harshBrakingCount' => DrivingBehaviourReportHelper::formatEventCount($data['harshbrakingcount']),
            'harshCorneringCount' => DrivingBehaviourReportHelper::formatEventCount($data['harshcorneringcount']),
            'totalDistance' => $totalDistance,
            'idlingCount' => DrivingBehaviourReportHelper::formatEventCount($data['idlingcount']),
            'ecoSpeedEventCount' => DrivingBehaviourReportHelper::formatEventCount($data['ecospeedeventcount']),
            'drivingTotalTime' => $drivingTotalTime,
        ];
        $overallScore = DrivingBehaviourReportHelper::roundFloatScore(
            DrivingBehaviourReportHelper::calcTotalScore(
                $data,
                [
                    'harshaccelerationscore',
                    'harshbrakingscore',
                    'harshcorneringscore',
                    'excessiveidlingscore',
                    'ecospeedscore',
                ]
            )
        );
        $result['overallScore'] = ($overallScore == 0) ? null : $overallScore;

        return $result;
    }

    /**
     * @param string $type
     * @param User $driver
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getDriverHarshDetails(string $type, User $driver, Request $request)
    {
        $params = $request->query->all();
        $params = DrivingBehaviourReportHelper::convertDatesToUTC($params);
        $totalDistance = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalOdometerByDriver($driver, $params['startDate'], $params['endDate']);
        $params = array_merge(
            [
                'driverId' => $driver->getId(),
                'totalDistance' => $totalDistance
            ],
            DrivingBehaviourReportHelper::prepareDatePeriod($params)
        );
        $result = $this->emSlave->getRepository(DrivingBehavior::class)->getDriverHarshDetails(
            $type,
            $params
        );
        $score = $this->emSlave->getRepository(DrivingBehavior::class)->getScoreByDistanceAndCount(
            $totalDistance,
            count($result)
        );

        $result = array_map(
            function ($item) {
                $item['ts'] = DateHelper::formatDate($item['ts']);

                return $item;
            },
            $result
        );

        return [
            'data' => $result,
            'score' => DrivingBehaviourReportHelper::roundFloatScore($score)
        ];
    }

    /**
     * @param User $driver
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getDriverEcoSpeedDetails(User $driver, Request $request)
    {
        $params = RequestFilterResolver::resolve($request->query->all());
        $params['driverId'] = $driver->getId();

        $result = $this->emSlave->getRepository(Speeding::class)->getSpeedingSetByDriver($params);

        foreach ($result as &$item) {
            $item['startDate'] = DateHelper::formatDate($item['startDate']);
            $item['endDate'] = DateHelper::formatDate($item['endDate']);

            if ($item['coordinates']) {
                $item['coordinates'] = $this->explodeCoordinates($item['coordinates']);
            }
        }

        $totalDistance = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalOdometerByDriver($driver, $params['startDate'], $params['endDate']);
        $score = $this->emSlave->getRepository(DrivingBehavior::class)->getScoreByDistanceAndCount(
            $totalDistance,
            count($result)
        );

        return [
            'data' => $result,
            'score' => DrivingBehaviourReportHelper::roundFloatScore($score),
        ];
    }

    /**
     * @param User $driver
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getDriverIdlingDetails(User $driver, Request $request)
    {
        $params = RequestFilterResolver::resolve($request->query->all());
        $params['driverId'] = $driver->getId();
        $params['excessiveIdling'] = $this->settingService->getExcessiveIdlingValueForTeam($driver->getTeam());
        $result = $this->emSlave->getRepository(Idling::class)->getIdlingByDriver($params);

        foreach ($result as &$item) {
            $item['startDate'] = DateHelper::formatDate($item['startDate']);
            $item['endDate'] = DateHelper::formatDate($item['endDate']);
            $item['lat'] = null;
            $item['lng'] = null;

            if ($item['coordinates']) {
                $item['coordinates'] = $this->explodeCoordinates($item['coordinates']);
                $firstCoordinates = $item['coordinates'][0] ?? null;

                if ($firstCoordinates) {
                    $item['lat'] = $firstCoordinates['lat'];
                    $item['lng'] = $firstCoordinates['lng'];
                }
            }
        }

        $totalDistance = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalOdometerByDriver($driver, $params['startDate'], $params['endDate']);
        $score = $this->emSlave->getRepository(DrivingBehavior::class)->getScoreByDistanceAndCount(
            $totalDistance,
            count($result)
        );

        return [
            'data' => $result,
            'score' => DrivingBehaviourReportHelper::roundFloatScore($score),
        ];
    }

    /**
     * @param User $user
     * @param int $days
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTeamVehiclesTotalDistance(User $user, int $days = 7)
    {
        if ($user->needToCheckUserGroup()) {
            $redisKey = 'teamVehiclesTotalDistance-' . $user->getTeam()->getId() . 'user-' . $user->getId() . 'days-' . $days;
        } else {
            $redisKey = 'teamVehiclesTotalDistance-' . $user->getTeam()->getId() . 'days-' . $days;
        }
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        $timezone = $user->getTimezone() ? $user->getTimezone() : TimeZone::DEFAULT_TIMEZONE['name'];
        $startDate = (new Carbon(null, $timezone))->subRealDays($days - 1)->startOfDay()->timezone('UTC');
        $endDate = (new Carbon(null, $timezone))->endOfDay()->timezone('UTC');

        $prevStartDate = (new Carbon(null, $timezone))->subRealDays(2 * $days - 1)->startOfDay()->timezone('UTC');
        $prevEndDate = (new Carbon(null, $timezone))->subRealDays($days - 1)->endOfDay()->timezone('UTC');

        $data = [
            'distance' => [
                'current' => [
                    'startDate' => DateHelper::formatDate($startDate),
                    'endDate' => DateHelper::formatDate($endDate),
                    'total_distance' => $this->emSlave->getRepository(TrackerHistory::class)
                        ->getTotalOdometerByTeam($user, $startDate, $endDate)

                ],
                'prev' => [
                    'startDate' => DateHelper::formatDate($prevStartDate),
                    'endDate' => DateHelper::formatDate($prevEndDate),
                    'total_distance' => $this->emSlave->getRepository(TrackerHistory::class)
                        ->getTotalOdometerByTeam($user, $prevStartDate, $prevEndDate)
                ]
            ],
            'shortest' => [
                'startDate' => DateHelper::formatDate($startDate),
                'endDate' => DateHelper::formatDate($endDate),
                'vehicles' => $this->emSlave->getRepository(TrackerHistory::class)
                    ->getTopVehiclesByTotalOdometer($user, $startDate, $endDate, 'ASC')

            ],
            'longest' => [
                'startDate' => DateHelper::formatDate($startDate),
                'endDate' => DateHelper::formatDate($endDate),
                'vehicles' => $this->emSlave->getRepository(TrackerHistory::class)
                    ->getTopVehiclesByTotalOdometer($user, $startDate, $endDate, 'DESC')
            ]
        ];

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 300);

        return $data;
    }

    /**
     * @param User $user
     * @param int $days
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTeamDriversTotalDistance(User $user, int $days = 7)
    {
        if ($user->needToCheckUserGroup()) {
            $redisKey = 'teamDriversTotalDistance-' . $user->getTeam()->getId() . 'user-' . $user->getId() . 'days-' . $days;
        } else {
            $redisKey = 'teamDriversTotalDistance-' . $user->getTeam()->getId() . 'days-' . $days;
        }
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        $timezone = $user->getTimezone() ? $user->getTimezone() : TimeZone::DEFAULT_TIMEZONE['name'];
        $startDate = (new Carbon(null, $timezone))->subRealDays($days - 1)->startOfDay()->timezone('UTC');
        $endDate = (new Carbon(null, $timezone))->endOfDay()->timezone('UTC');

        $data = [
            'shortest' => [
                'startDate' => DateHelper::formatDate($startDate),
                'endDate' => DateHelper::formatDate($endDate),
                'drivers' => $this->emSlave->getRepository(TrackerHistory::class)
                    ->getTopDriversByTotalOdometer($user, $startDate, $endDate, 'ASC')

            ],
            'longest' => [
                'startDate' => DateHelper::formatDate($startDate),
                'endDate' => DateHelper::formatDate($endDate),
                'drivers' => $this->emSlave->getRepository(TrackerHistory::class)
                    ->getTopDriversByTotalOdometer($user, $startDate, $endDate, 'DESC')
            ]
        ];

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 300);

        return $data;
    }

    /**
     * @param User $user
     * @param int $days
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getScoreStatistic(User $user, int $days = 7)
    {
        if ($user->needToCheckUserGroup()) {
            $redisKey = 'scoreStatistic-' . $user->getTeam()->getId() . 'user-' . $user->getId() . 'days-' . $days;
        } else {
            $redisKey = 'scoreStatistic-' . $user->getTeam()->getId() . 'days-' . $days;
        }
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        $count = 3;
        $startDate = (new Carbon())->subDays($days);
        $endDate = new Carbon();

        $prevStartDate = (new Carbon())->subDays(2 * $days);
        $prevEndDate = (new Carbon())->subDays($days);

        $currentVehicles = $this->getVehiclesWithScore($user, $startDate, $endDate);
        $currentDrivers = $this->getDriversWithScore($user, $startDate, $endDate);

        $prevVehicles = $this->getVehiclesWithScore($user, $prevStartDate, $prevEndDate);
        $prevDrivers = $this->getDriversWithScore($user, $prevStartDate, $prevEndDate);

        $data = [
            'currentStartDate' => DateHelper::formatDate($startDate),
            'currentEndDate' => DateHelper::formatDate($endDate),
            'prevStartDate' => DateHelper::formatDate($prevStartDate),
            'prevEndDate' => DateHelper::formatDate($prevEndDate),
            'drivers' => [
                'currentAverageScore' => $this->getArrayAverageValue($currentDrivers, 'totalScore'),
                'prevAverageScore' => $this->getArrayAverageValue($prevDrivers, 'totalScore'),
                'best' => $this->sortAndLimitValues($currentDrivers, '-totalScore', $count),
                'worst' => $this->sortAndLimitValues($currentDrivers, 'totalScore', $count)
            ],
            'vehicles' => [
                'average' => $this->getArrayAverageValue($currentVehicles, 'totalScore'),
                'prevAverageScore' => $this->getArrayAverageValue($prevVehicles, 'totalScore'),
                'best' => $this->sortAndLimitValues($currentVehicles, '-totalScore', $count),
                'worst' => $this->sortAndLimitValues($currentVehicles, 'totalScore', $count)
            ]
        ];

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 86400);

        return $data;
    }

    /**
     * @param User $user
     * @param $params
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getScoreStatisticByRange(User $user, $params)
    {
        $data = ['data' => []];
        $vehiclesTotalAverageScore = 0;
        $driversTotalAverageScore = 0;

        if ($user->needToCheckUserGroup()) {
            $redisKey = 'scoreStatistic-' . $user->getTeam()->getId()
                . 'user-' . $user->getId()
                . 'groupCount-' . $params['groupCount'] . 'groupCount-' . $params['groupType'];
        } else {
            $redisKey = 'scoreStatistic-' . $user->getTeam()->getId()
                . 'groupCount-' . $params['groupCount'] . 'groupCount-' . $params['groupType'];
        }
        $redisData = $this->dashboardCache->getFromJson($redisKey);
        if ($redisData) {
            return $redisData;
        }

        if ($params['groupType'] && $params['groupCount']) {
            $timezone = $user->getTimezone() ? $user->getTimezone() : TimeZone::DEFAULT_TIMEZONE['name'];
            $groupDate = $params['groupDate']
                ? new Carbon($params['groupDate'])
                : Carbon::now($timezone)->setTimezone('UTC');
            $dateRanges = DateHelper::getRanges($params['groupType'], $params['groupCount'], $groupDate);

            foreach ($dateRanges as $dateRange) {
                $params['startDate'] = $dateRange['start'];
                $params['endDate'] = $dateRange['end'];
                $currentVehicles = $this->getVehiclesWithScore($user, $dateRange['start'], $dateRange['end'], false);
                $currentDrivers = $this->getDriversWithScore($user, $dateRange['start'], $dateRange['end'], false);
                $data['data'][] = [
                    'startDate' => DateHelper::formatDate($dateRange['start']),
                    'endDate' => DateHelper::formatDate($dateRange['end']),
                    'driversAverageScore' => $this->getArrayAverageValue($currentDrivers, 'totalScore'),
                    'vehiclesAverageScore' => $this->getArrayAverageValue($currentVehicles, 'totalScore')
                ];
            }
            if (isset($data['data']) && count($data['data'])) {
                foreach ($data['data'] as $item) {
                    $vehiclesTotalAverageScore += $item['vehiclesAverageScore'];
                    $driversTotalAverageScore += $item['driversAverageScore'];
                }
                $vehiclesTotalAverageScore /= count($data['data']);
                $driversTotalAverageScore /= count($data['data']);
            }
        }

        $data['vehiclesTotalAverageScore'] = $vehiclesTotalAverageScore;
        $data['driversTotalAverageScore'] = $driversTotalAverageScore;

        $this->dashboardCache->setToJsonTtl($redisKey, $data, 86400);

        return $data;
    }

    /**
     * @param User $user
     * @param $startDate
     * @param $endDate
     * @param bool $driverData
     * @return array|null
     * @throws \Exception
     */
    public function getDriversWithScore(User $user, $startDate, $endDate, $driverData = true)
    {
        $params['startDate'] = $startDate;
        $params['endDate'] = $endDate;

        $driversMap = DrivingBehaviourReportHelper::getMapBy(
            $this->userService->getDrivers(
                DrivingBehaviourReportHelper::prepareDriverSummaryElasticaParams($params),
                $user,
                false
            ),
            'id'
        );

        $params = DrivingBehaviourReportHelper::prepareDriversSummaryReportParams(
            array_merge(
                $params,
                [
                    'excSpeedMap' => DrivingBehaviourReportHelper::buildExcessiveSpeedMapForDrivers($driversMap),
                ]
            )
        );

        $rawReport = [];

        $totalDistanceArray = $this->emSlave->getRepository(TrackerHistory::class)
            ->getTotalOdometerByDriverArray(array_keys($driversMap), $params['startDate'], $params['endDate']);
        foreach ($driversMap as $key => $driver) {
            $totalDistance = (int)($totalDistanceArray[$key] ?? 0);
            $drivingTotalTime = $this->emSlave->getRepository(TrackerHistory::class)
                ->getTotalDrivingTimeByDriver($driver, $params['startDate'], $params['endDate']);
            $totalAvgSpeed = $drivingTotalTime && $totalDistance
                ? (($totalDistance / 1000) / ($drivingTotalTime / 3600))
                : null;
            /** @var Setting $excessiveIdling */
            $excessiveIdling = $this->settingService->getExcessiveIdlingValueForTeam($driver->getTeam());

            $params['totalDistance'][$key] = $totalDistance;
            $params['drivingTotalTime'][$key] = $drivingTotalTime;
            $params['totalAvgSpeed'][$key] = $totalAvgSpeed;
            $params['excessiveIdling'][$key] = $excessiveIdling;

            $result = $this
                ->em
                ->getRepository(DrivingBehavior::class)
                ->getSummaryDriverReport($params, $key);
            $result['totalDistance'] = $params['totalDistance'][$key];
            $result['excessiveIdling'] = $params['excessiveIdling'][$key];
            $rawReport[] = $result;
        }

        $preparedReport = [];

        foreach ($rawReport as $item) {
            $preparedItem = [
                'totalScore' => DrivingBehaviourReportHelper::roundFloatScore(
                    DrivingBehaviourReportHelper::calcTotalScore(
                        $item,
                        [
                            'harshaccelerationscore',
                            'harshbrakingscore',
                            'harshcorneringscore',
                            'excessiveidling',
                            'speeding',
                        ]
                    )
                ),
            ];
            if ($driverData) {
                $preparedItem['driver'] = $driversMap[$item['driverid']]->toArray(
                    ['email', 'name', 'surname', 'fullName']
                );
            }

            if ($preparedItem['totalScore'] > 0) {
                $preparedReport[] = $preparedItem;
            }
        }

        return $preparedReport;
    }

    /**
     * @param User $user
     * @param $startDate
     * @param $endDate
     * @param bool $vehicleData
     * @return array
     * @throws \Exception
     */
    public function getVehiclesWithScore(User $user, $startDate, $endDate, $vehicleData = true)
    {
        $params['startDate'] = $startDate;
        $params['endDate'] = $endDate;

        $vehiclesMap = DrivingBehaviourReportHelper::getMapBy(
            $this->vehicleService->vehicleList(
                DrivingBehaviourReportHelper::prepareVehicleSummaryElasticaParams($params),
                $user,
                false
            ),
            'id'
        );

        $params = DrivingBehaviourReportHelper::prepareVehiclesSummaryReportParams(
            array_merge(
                $params,
                [
                    'excSpeedMap' => DrivingBehaviourReportHelper::buildExcessiveSpeedMap($vehiclesMap),
                ]
            )
        );

        $rawReport = [];

        $totalDistanceArray = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalOdometerArray(array_keys($vehiclesMap), $params['startDate'], $params['endDate']);
        $vehiclesDrivingTotalTime = $drivingTotalTime = $this->emSlave->getRepository(Vehicle::class)
            ->getTotalDrivingTimeForArray($vehiclesMap, $params['startDate'], $params['endDate']);

        foreach ($vehiclesMap as $key => $vehicle) {
            $totalDistance = (int)($totalDistanceArray[$key] ?? 0);
            $drivingTotalTime = $drivingTotalTime = $vehiclesDrivingTotalTime[$vehicle->getId()] ?? 0;
            $totalAvgSpeed = $drivingTotalTime && $totalDistance
                ? (($totalDistance / 1000) / ($drivingTotalTime / 3600))
                : null;
            /** @var Setting $excessiveIdling */
            $excessiveIdling = $this->settingService->getExcessiveIdlingValue($vehicle);

            $params['totalDistance'][$key] = $totalDistance;
            $params['drivingTotalTime'][$key] = $drivingTotalTime;
            $params['totalAvgSpeed'][$key] = $totalAvgSpeed;
            $params['excessiveIdling'][$key] = $excessiveIdling;

            $result = $this->em->getRepository(DrivingBehavior::class)->getSummaryVehicleReport($params, $key);
            $result['totalDistance'] = $params['totalDistance'][$key];
            $result['drivingTotalTime'] = $params['drivingTotalTime'][$key];
            $result['totalAvgSpeed'] = $params['totalAvgSpeed'][$key];
            $result['excessiveIdling'] = $params['excessiveIdling'][$key];
            $rawReport[] = $result;
        }

        $preparedReport = [];

        foreach ($rawReport as $item) {
            $preparedItem = [
                'totalScore' => DrivingBehaviourReportHelper::roundFloatScore(
                    DrivingBehaviourReportHelper::calcTotalScore(
                        $item,
                        [
                            'harshaccelerationscore',
                            'harshbrakingscore',
                            'harshcorneringscore',
                            'excessiveidling',
                            'speeding',
                        ]
                    )
                ),
            ];
            if ($vehicleData) {
                $preparedItem['vehicle'] = $vehiclesMap[$item['vehicleid']]->toArray(['id', 'regNo', 'defaultLabel']);
            }

            if ($preparedItem['totalScore'] > 0) {
                $preparedReport[] = $preparedItem;
            }
        }

        return $preparedReport;
    }

    /**
     * @param array $data
     * @param string $field
     * @return float|int
     */
    public function getArrayAverageValue(array $data, string $field)
    {
        return count($data) ? array_sum(array_column($data, $field)) / count($data) : 0;
    }

    /**
     * @param $data
     * @param $sort
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function sortAndLimitValues($data, $sort, $limit = 3)
    {
        $params = DrivingBehaviourReportHelper::prepareDriversSummaryReportParams(['sort' => $sort]);

        return array_slice(
            DrivingBehaviourReportHelper::sortMultidimensionalArray($data, ...array_shift($params['order'])),
            0,
            $limit,
            true
        );
    }
}
