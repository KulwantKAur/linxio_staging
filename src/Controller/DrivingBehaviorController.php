<?php

namespace App\Controller;

use App\Entity\DrivingBehavior;
use App\Entity\Permission;
use App\Entity\TimeZone;
use App\Service\DrivingBehavior\DrivingBehaviorService;
use App\Service\PdfService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\User\UserService;
use App\Service\Vehicle\VehicleService;
use App\Util\DateHelper;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DrivingBehaviorController extends BaseController
{
    private DrivingBehaviorService $drivingBehaviorService;
    private VehicleService $vehicleService;
    private UserService $userService;
    private PdfService $pdfService;
    private ReportService $reportService;

    public function __construct(
        DrivingBehaviorService $drivingBehaviorService,
        VehicleService $vehicleService,
        UserService $userService,
        PdfService $pdfService,
        ReportService $reportService
    ) {
        $this->drivingBehaviorService = $drivingBehaviorService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->pdfService = $pdfService;
        $this->reportService = $reportService;
    }

    #[Route('/driving-behavior/summary/vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getSummaryDrivingBehaviorVehicle(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::DRIVING_BEHAVIOUR_VEHICLES, DrivingBehavior::class);

        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_DRIVING_BEHAVIOR_VEHICLE)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/driving-behavior/details/{id}/{type}', requirements: ['type' => 'harsh-acceleration|harsh-braking|harsh-cornering'], methods: ['GET'])]
    public function getDetailsHarshVehicle($id, $type, Request $request)
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());

            $report = $this->drivingBehaviorService->getVehicleHarshDetails($type, $vehicle, $request);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($report);
    }

    #[Route('/driving-behavior/details/{id}/eco-speed', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDetailsEcoSpeedVehicle(Request $request, $id)
    {
        try {
            if (!$vehicle = $this->vehicleService->getById($id, $this->getUser())) {
                return $this->viewItem([]);
            }

            $report = $this->drivingBehaviorService->getVehicleEcoSpeedDetails($vehicle, $request);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($report);
    }

    #[Route('/driving-behavior/details/{id}/idling', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDetailsIdlingVehicle($id, Request $request)
    {
        try {
            if (!$vehicle = $this->vehicleService->getById($id, $this->getUser())) {
                return $this->viewItem([]);
            }

            $report = $this->drivingBehaviorService->getVehicleIdlingDetails($vehicle, $request);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($report);
    }

    #[Route('/driving-behavior/vehicle/{id}/total', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getTotalDrivingBehaviorVehicle($id, Request $request)
    {
        try {
            $params = array_merge(['vehicleId' => $id], $request->query->all());
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $data = [];

            if ($vehicle) {
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
                $data = $this->drivingBehaviorService->getVehicleScores($vehicle, $params);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/driving-behavior/vehicle/{id}/scores', requirements: ['id' => '\d+', 'type' => '(day|week|month)'], methods: ['GET'])]
    public function getVehicleScores(Request $request, $id): JsonResponse
    {
        try {
            $params = array_merge(['vehicleId' => $id], $request->query->all());

            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $data = [];

            if ($vehicle) {
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());

                if ($params['groupType'] && $params['groupCount']) {
                    $timezone = $this->getUser() && $this->getUser()->getTimezone()
                        ? $this->getUser()->getTimezone()
                        : TimeZone::DEFAULT_TIMEZONE['name'];
                    $groupDate = $params['groupDate']
                        ? new Carbon($params['groupDate'])
                        : Carbon::now($timezone)->setTimezone('UTC');
                    $dateRanges = DateHelper::getRanges($params['groupType'], $params['groupCount'], $groupDate);

                    foreach ($dateRanges as $dateRange) {
                        $params['startDate'] = $dateRange['start'];
                        $params['endDate'] = $dateRange['end'];
                        $data[] = array_merge(
                            $dateRange,
                            $this->drivingBehaviorService->getVehicleEventsCount($this->getUser(), $vehicle, $params)
                        );
                    }
                } else {
                    $data = $this->drivingBehaviorService->getVehicleEventsCount($this->getUser(), $vehicle, $params);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/driving-behavior/vehicle/{id}/speeding', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleSpeeding($id, Request $request)
    {
        try {
            $idling = $this->drivingBehaviorService->getVehicleSpeeding($this->getUser(), $id);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($idling);
    }

    #[Route('/driving-behavior/driver/summary/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getSummaryDrivingBehaviorDriver(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::DRIVING_BEHAVIOUR_DRIVERS, DrivingBehavior::class);
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_DRIVING_BEHAVIOR_DRIVER)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/driving-behavior/driver/{id}/total', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getTotalDrivingBehaviorDriver($id, Request $request)
    {
        try {
            $params = array_merge(['driverId' => $id], $request->query->all());
            $driver = $this->userService->getUserById($id);
            $data = [];

            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());
                $data = $this->drivingBehaviorService->getDriverScores($driver, $params);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/driving-behavior/driver/{id}/scores', requirements: ['id' => '\d+', 'type' => '(day|week|month)'], methods: ['GET'])]
    public function getDriverScores(Request $request, $id): JsonResponse
    {
        try {
            $params = array_merge(['driverId' => $id], $request->query->all());
            $driver = $this->userService->getUserById($id);
            $data = [];

            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());

                if ($params['groupType'] && $params['groupCount']) {
                    $timezone = $this->getUser() && $this->getUser()->getTimezone()
                        ? $this->getUser()->getTimezone()
                        : TimeZone::DEFAULT_TIMEZONE['name'];
                    $groupDate = $params['groupDate']
                        ? new Carbon($params['groupDate'])
                        : Carbon::now($timezone)->setTimezone('UTC');
                    $dateRanges = DateHelper::getRanges($params['groupType'], $params['groupCount'], $groupDate);

                    foreach ($dateRanges as $dateRange) {
                        $params['startDate'] = $dateRange['start'];
                        $params['endDate'] = $dateRange['end'];
                        $data[] = array_merge(
                            $dateRange,
                            $this->drivingBehaviorService->getDriverEventsCount($driver, $params)
                        );
                    }
                } else {
                    $data = $this->drivingBehaviorService->getDriverEventsCount($driver, $params);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/driving-behavior/driver/details/{id}/{type}', requirements: ['id' => '\d+', 'type' => '(harsh-acceleration|harsh-braking|harsh-cornering)'], methods: ['GET'])]
    public function getDriverDetailsHarshVehicle($id, $type, Request $request)
    {
        try {
            $driver = $this->userService->getUserById($id);
            $report = [];

            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());
                $report = $this->drivingBehaviorService->getDriverHarshDetails($type, $driver, $request);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($report);
    }

    #[Route('/driving-behavior/driver/details/{id}/eco-speed', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDriverEcoSpeedDetails($id, Request $request)
    {
        try {
            $driver = $this->userService->getUserById($id);
            $report = [];

            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());
                $report = $this->drivingBehaviorService->getDriverEcoSpeedDetails($driver, $request);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($report);
    }

    #[Route('/driving-behavior/driver/details/{id}/idling', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDriverIdlingDetails($id, Request $request)
    {
        try {
            $driver = $this->userService->getUserById($id);
            $result = [];

            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());
                $result = $this->drivingBehaviorService->getDriverIdlingDetails($driver, $request);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($result);
    }

    #[Route('/dashboard/vehicles/distance', methods: ['GET'])]
    public function getDashboardVehiclesDistanceStat(Request $request): JsonResponse
    {
        try {
            $days = $request->query->get('days');

            $data = $this->drivingBehaviorService->getTeamVehiclesTotalDistance($this->getUser(), $days);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/dashboard/drivers/distance', methods: ['GET'])]
    public function getDashboardDriversDistanceStat(Request $request): JsonResponse
    {
        try {
            $days = $request->query->get('days');
            $data = $this->drivingBehaviorService->getTeamDriversTotalDistance($this->getUser(), $days);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/dashboard/driving-behavior', methods: ['GET'])]
    public function getDashboardBehaviorStat(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::DRIVING_BEHAVIOUR_DASHBOARD, DrivingBehavior::class);

        try {
            $days = $request->query->get('days');
            $data = $this->drivingBehaviorService->getScoreStatistic($this->getUser(), $days);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/dashboard/driving-behavior/range', methods: ['GET'])]
    public function getDashboardBehaviorStatByDate(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::DRIVING_BEHAVIOUR_DASHBOARD, DrivingBehavior::class);
        try {
            $params = $request->query->all();
            $data = $this->drivingBehaviorService->getScoreStatisticByRange($this->getUser(), $params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }
}
