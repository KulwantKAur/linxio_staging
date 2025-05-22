<?php

namespace App\Controller;

use App\Entity\Route;
use App\Entity\Permission;
use App\Entity\TimeZone;
use App\Entity\User;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Exceptions\Route\AssignRouteDriverException;
use App\Report\Builder\Summary\SummaryReportHelper;
use App\Response\CsvResponse;
use App\Response\PdfResponse;
use App\Service\PdfService;
use App\Service\Report\ReportMapper;
use App\Service\Asset\AssetService;
use App\Service\Report\ReportService;
use App\Service\Route\RouteService;
use App\Service\User\UserService;
use App\Service\Vehicle\VehicleService;
use App\Util\DateHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as URLRoute;

class RouteController extends BaseController
{
    private $vehicleService;
    private $routeService;
    private $pdfService;
    private $assetService;
    private $paginator;
    private $userService;
    private ReportService $reportService;
    private SlaveEntityManager $emSlave;

    public function __construct(
        VehicleService $vehicleService,
        RouteService $routeService,
        AssetService $assetService,
        PdfService $pdfService,
        PaginatorInterface $paginator,
        UserService $userService,
        ReportService $reportService,
        SlaveEntityManager $emSlave
    ) {
        $this->vehicleService = $vehicleService;
        $this->routeService = $routeService;
        $this->pdfService = $pdfService;
        $this->assetService = $assetService;
        $this->paginator = $paginator;
        $this->userService = $userService;
        $this->reportService = $reportService;
        $this->emSlave = $emSlave;
    }

    #[URLRoute('/vehicles/{id}/routes', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleRoutes(Request $request, $id): JsonResponse
    {
        try {
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $routes = [];

            if ($vehicle) {
                $params = $request->query->all();
                $isNoGroup = $params['isNoGroup'] ?? false;
                $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
                $routes = $this->routeService->getVehicleRoutes($vehicle, $dateFrom, $dateTo, $include, !$isNoGroup);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($routes);
    }

    #[URLRoute('/vehicles/{id}/routes/optimization', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleRoutesWithOptimization(Request $request, $id): JsonResponse
    {
        try {
            $dateFrom = $request->get('dateFrom');
            $dateTo = $request->get('dateTo');
            $type = $request->get('type', 'angle');
            $count = $request->get('count', 1000);
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $routes = [];

            if ($vehicle) {
                $params = $request->query->all();
                $isNoGroup = $params['isNoGroup'] ?? false;
                if ($type === 'speed') {
                    $include = array_merge(Route::OPT_ROUTE_DISPLAY_VALUES, $params['fields'] ?? []);
                } else {
                    $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
                }
                $routes = $this->routeService->getVehicleRoutes(
                    $vehicle, $dateFrom, $dateTo, $include, !$isNoGroup, null, null, Criteria::ASC,
                    ['type' => $type, 'count' => $count]
                );
                if ($type === 'speed') {
                    $routes = array_map(function ($route) {
                        unset($route['pointStart']);
                        unset($route['pointFinish']);
                        return $route;
                    }, $routes);
                }

            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($routes);
    }

    #[URLRoute('/vehicles/{id}/th', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleThWithOptimization(Request $request, $id): JsonResponse
    {
        try {
            $params = $request->query->all();
            $dateFrom = $request->get('dateFrom');
            $dateTo = $request->get('dateTo');
            $params['type'] = $request->get('type', 'speed');
            $params['count'] = $request->get('count', 1000);
            $vehicle = $this->vehicleService->getById($id, $this->getUser());

            if ($vehicle) {
                $ths = $this->vehicleService
                    ->getVehicleThByDate($vehicle, $dateFrom, $dateTo, $params, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($ths ?? []);
    }

    #[URLRoute('/assets/{id}/routes', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getAssetRoutes(Request $request, $id): JsonResponse
    {
        try {
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $asset = $this->assetService->getById($id, $this->getUser());
            $routes = [];

            if ($asset) {
                $this->denyAccessUnlessGranted(Permission::ASSET_LIST, $asset);
                $params = $request->query->all();
                $isNoGroup = $params['isNoGroup'] ?? false;
                $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);

                if ($asset->getDevice()) {
                    $routes = $this->routeService->getAssetRoutes($asset, $dateFrom, $dateTo, $include, !$isNoGroup);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($routes);
    }

    #[URLRoute('/vehicles/{id}/routes/paginated', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleRoutesPaginated(Request $request, $id): JsonResponse
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $offset = $page > 1 ? ($page - 1) * $limit : 0;

            if ($vehicle) {
                $params = $request->query->all();
                $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
                $routesData = $this->routeService->getVehicleRoutesPaginated(
                    $vehicle,
                    $dateFrom,
                    $dateTo,
                    $include,
                    false,
                    $limit,
                    $offset
                );

                return new JsonResponse(
                    [
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'total' => $routesData['count'],
                        'data' => $routesData['data'],
                    ]
                );
            } else {
                return $this->viewItem(null, [], 200);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/drivers/{id}/routes', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDriverRoutes(Request $request, $id): JsonResponse
    {
        try {
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $driver = $this->userService->get($id);
            $data = [];

            if ($driver) {
                $params = $request->query->all();
                $isNoGroup = $params['isNoGroup'] ?? false;
                $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
                // todo what's access?
                $data = $this->routeService
                    ->getDriverRoutes($driver, $dateFrom, $dateTo, $this->getUser(), $include, !$isNoGroup);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[URLRoute('/drivers/{id}/routes/paginated', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDriverRoutesPaginated(Request $request, $id): JsonResponse
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $driver = $this->userService->get($id);
            $routes = [];

            if ($driver) {
                $params = $request->query->all();
                $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);
                // todo what's access?
                $routes = $this->routeService
                    ->getDriverRoutes($driver, $dateFrom, $dateTo, $this->getUser(), $include, false);
            }

            $pagination = $this->paginator->paginate($routes, $page, $limit);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination, [], 200);
    }

    #[URLRoute('/drivers/{id}/routes-info', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDriverRouteInfo(Request $request, $id): JsonResponse
    {
        try {
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');

            $groupType = $request->query->get('groupType');
            $groupCount = $request->query->get('groupCount');
            $groupDate = $request->query->get('groupDate');

            $driver = $this->userService->get($id);
            $data = [];

            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());

                if ($groupType && $groupCount) {
                    $timezone = $this->getUser() && $this->getUser()->getTimezone()
                        ? $this->getUser()->getTimezone()
                        : TimeZone::DEFAULT_TIMEZONE['name'];
                    $groupDate = $groupDate ? new Carbon($groupDate) : Carbon::now($timezone)->setTimezone('UTC');
                    $dateRanges = DateHelper::getRanges($groupType, $groupCount, $groupDate);

                    foreach ($dateRanges as $dateRange) {
                        $data[] = array_merge(
                            $dateRange,
                            $this->routeService->getDriverOrVehicleRoutes(
                                $dateRange['start'],
                                $dateRange['end'],
                                $this->getUser(),
                                $driver
                            )
                        );
                    }
                } else {
                    $data = $this->routeService
                        ->getDriverOrVehicleRoutes($dateFrom, $dateTo, $this->getUser(), $driver);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[URLRoute('/vehicles/{id}/routes-info', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleRouteInfo(Request $request, $id): JsonResponse
    {
        try {
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');

            $groupType = $request->query->get('groupType');
            $groupCount = $request->query->get('groupCount');
            $groupDate = $request->query->get('groupDate');

            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $data = [];

            if ($vehicle) {
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
                if ($groupType && $groupCount) {
                    $timezone = $this->getUser() && $this->getUser()->getTimezone()
                        ? $this->getUser()->getTimezone()
                        : TimeZone::DEFAULT_TIMEZONE['name'];
                    $groupDate = $groupDate ? new Carbon($groupDate) : Carbon::now($timezone)->setTimezone('UTC');
                    $dateRanges = DateHelper::getRanges($groupType, $groupCount, $groupDate);

                    foreach ($dateRanges as $dateRange) {
                        $data[] = array_merge(
                            $dateRange,
                            $this->routeService->getDriverOrVehicleRoutes(
                                $dateRange['start'],
                                $dateRange['end'],
                                $this->getUser(),
                                null,
                                $vehicle
                            )
                        );
                    }
                } else {
                    $data = $this->routeService
                        ->getDriverOrVehicleRoutes($dateFrom, $dateTo, $this->getUser(), null, $vehicle);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[URLRoute('/routes-report/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function routeReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_ROUTES)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/routes-report-by-vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function routeReportByVehicle(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_ROUTES_BY_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/stops-report-by-vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function stopsReportByVehicle(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_STOPS_BY_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/fbt-report/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function fbtReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_FBT)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/fbt-report/vehicles', methods: ['POST'])]
    public function fbtReportVehicles(Request $request)
    {
        try {
            $vehicles = $this->routeService->fbtReportVehicles($request->request->all(), $this->getUser());

            return $this->viewItem($vehicles, Vehicle::DISPLAYED_VALUES);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/routes-report/vehicles', methods: ['POST'])]
    public function routeReportVehicles(Request $request)
    {
        try {
            $vehicles = $this->routeService->routeReportVehicles($request->request->all(), $this->getUser());

            return $this->viewItem($vehicles, Vehicle::DISPLAYED_VALUES);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/stops-report/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function stopsReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_STOPS)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/stops-report/vehicles', methods: ['POST'])]
    public function stopsReportVehicles(Request $request)
    {
        try {
            $vehicles = $this->routeService->stopsReportVehicles($request->request->all(), $this->getUser());

            return $this->viewItem($vehicles, Vehicle::DISPLAYED_VALUES);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/routes/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $route = $this->routeService->getById($id, $this->getUser());

            if ($route) {
                $params = $request->request->all();
                $route = $this->routeService->editRoute($route, $params);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($route);
    }

    #[URLRoute('/routes/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getById(Request $request, $id): JsonResponse
    {
        try {
            $route = $this->routeService->getById($id, $this->getUser());
            $params = $request->query->all();
            $include = array_merge(Route::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);

            if ($route) {
                $this->denyAccessUnlessGranted(null, $route->getDevice()->getTeam());
                $this->routeService->setCoordinatesToRoute($route, $include);
                $route = $this->routeService->addAddressToRoutes([$route->toArray($include)])[0];
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($route, $include);
    }

    #[URLRoute('/driver-summary-report/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function driverSummaryReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_DRIVER_SUMMARY)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/driver-summary-drivers/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function driverSummaryDriversList(Request $request, $type)
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $requestData = $request->query->all();
            //TODO refactoring (as report call?)
            $routes = SummaryReportHelper::driverSummaryDriversList($requestData, $this->getUser(), $this->emSlave);

            switch ($type) {
                case 'json':
                default:
                    $pagination = $this->paginator->paginate($routes, $page, $limit);

                    return $this->viewItem($pagination, [], 200);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/route/{id}/driver/{driverId}', requirements: ['id' => '\d+', 'driverId' => '\d+'], methods: ['POST'])]
    public function setRouteDriver(Request $request, $id, $driverId, EntityManager $em, RouteService $routeService)
    {
        try {
            /** @var Route $route */
            $route = $em->getRepository(Route::class)->find($id);
            $driver = $em->getRepository(User::class)->find($driverId);
            $unassign = $request->request->get('unassign', false);
            if ($route && $driver) {
                $this->denyAccessUnlessGranted(null, $route->getDevice()->getTeam());
                $this->denyAccessUnlessGranted(null, $driver->getTeam());

                try {
                    $route = $routeService->setRouteDriver($route, $driver, $unassign);
                } catch (AssignRouteDriverException $exception) {
                    return $this->viewItem([$exception->getContext()], [], 400);
                }
            }

            return $this->viewItem($route);

        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/route/driver/{driverId}', requirements: ['driverId' => '\d+'], methods: ['POST'])]
    public function setRoutesDriver(Request $request, $driverId, EntityManager $em, RouteService $routeService)
    {
        try {
            /** @var Route $route */
            $driver = $em->getRepository(User::class)->find($driverId);
            $routeIds = $request->request->all('route_id');
            $unassign = $request->request->get('unassign', false);
            $errors = [];
            if ($driver) {
                $this->denyAccessUnlessGranted(null, $driver->getTeam());

                foreach ($routeIds as $routeId) {
                    $route = $em->getRepository(Route::class)->find($routeId);
                    $this->denyAccessUnlessGranted(null, $route->getDevice()->getTeam());

                    try {
                        $routeService->setRouteDriver($route, $driver, $unassign);
                    } catch (AssignRouteDriverException $exception) {
                        $errors[] = $exception->getContext();
                    }
                }
            }
            if ($errors) {
                return $this->viewItem($errors, [], 400);
            } else {
                return $this->viewItem(null);
            }

        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[URLRoute('/route/driver/unset', methods: ['POST'])]
    public function unsetRouteDriver(Request $request, EntityManager $em, RouteService $routeService)
    {
        try {
            /** @var Route[] $routes */
            $routes = $em->getRepository(Route::class)
                ->findBy(['id' => $request->request->all('route_id')]);

            foreach ($routes as $route) {
                $this->denyAccessUnlessGranted(null, $route->getDevice()->getTeam());
                $routeService->unsetRouteDriver($route);
            }

            return $this->viewItemsArray($routes);

        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }
}
