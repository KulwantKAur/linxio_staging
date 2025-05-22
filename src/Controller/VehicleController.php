<?php

namespace App\Controller;

use App\Entity\DriverHistory;
use App\Entity\Note;
use App\Entity\Permission;
use App\Entity\Route as VehicleRoute;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\Response\CsvResponse;
use App\Response\PdfResponse;
use App\Service\Device\DeviceSensorService;
use App\Service\Note\NoteService;
use App\Service\PdfService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\Route\RouteService;
use App\Service\Vehicle\VehicleService;
use App\Util\PaginationHelper;
use Doctrine\ORM\EntityManager;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class VehicleController extends BaseController
{
    private $vehicleService;
    private $translator;
    private $pdfService;
    private $paginator;
    private $noteService;
    private $routeService;
    private $deviceSensorService;
    private ReportService $reportService;
    private EntityManager $em;

    public function __construct(
        VehicleService $vehicleService,
        TranslatorInterface $translator,
        PdfService $pdfService,
        PaginatorInterface $paginator,
        NoteService $noteService,
        RouteService $routeService,
        DeviceSensorService $deviceSensorService,
        ReportService $reportService,
        EntityManager $em
    ) {
        $this->vehicleService = $vehicleService;
        $this->translator = $translator;
        $this->pdfService = $pdfService;
        $this->paginator = $paginator;
        $this->noteService = $noteService;
        $this->routeService = $routeService;
        $this->deviceSensorService = $deviceSensorService;
        $this->reportService = $reportService;
        $this->em = $em;
    }

    #[Route('/vehicles', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::FLEET_SECTION_ADD_VEHICLE, Vehicle::class);
        try {
            $picture = $request->files->get('picture') ?? null;
            $vehicle = $this->vehicleService->create(
                array_merge(
                    $request->request->all(),
                    ['createdBy' => $this->getUser(), 'picture' => $picture]
                ),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/vehicles/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function vehiclesList(Request $request, $type)
    {
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $vehicles = $this->vehicleService->vehicleList($params, $this->getUser());

                    return $this->viewItem($vehicles);
                case 'csv':
                    $vehicles = $this->vehicleService->getVehicleListExportData($params, $this->getUser(), false);

                    return new CsvResponse($vehicles);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/fields/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function vehiclesListWithFields(Request $request, $type)
    {
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $vehicles = $this->vehicleService->vehicleList($params, $this->getUser(), true, []);

                    return $this->viewItem($vehicles);
                case 'csv':
                    $vehicles = $this->vehicleService->getVehicleListExportData($params, $this->getUser(), false);

                    return new CsvResponse($vehicles);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/fields/dropdown', methods: ['GET'])]
    public function vehiclesListWithFieldsDropdown(Request $request)
    {
        try {
            $params = $request->query->all();
            $vehicles = $this->vehicleService->vehicleList($params, $this->getUser(), true, []);

            return $this->viewItem($vehicles);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/check-vehicle-vin', methods: ['GET'])]
    public function checkVehicleVin(Request $request)
    {
        $vin = $request->query->get('vin');
        $teamId = $request->query->get('teamId');
        $vehicleId = $request->query->get('vehicleId') ?? null;

        $team = $this->em->getRepository(Team::class)->find($teamId);
        $vehicle = $this->em->getRepository(Vehicle::class)
            ->getVehicleIdByVinExcludeCurrent($team, $vehicleId, $vin);

        return $this->viewItem(['isUnique' => !(bool)$vehicle]);
    }

    #[Route('/vehicles/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleById(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        $params = $request->query->all();
        $include = array_merge(Vehicle::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);

        return $this->viewItem($vehicle, $include);
    }

    #[Route('/vehicles/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            if ($vehicle) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_EDIT, $vehicle);
                $picture = $request->files->get('picture') ?? null;
                $vehicle = $this->vehicleService->edit(
                    array_merge($request->request->all(), ['picture' => $picture]),
                    $this->getUser(),
                    $vehicle
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/vehicles/{id}/restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            if ($vehicle) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_ARCHIVE, $vehicle);
                $vehicle = $this->vehicleService->restore($vehicle);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/vehicles/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            if ($vehicle) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DELETE, $vehicle);
                $this->vehicleService->removeVehicle($vehicle, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/vehicles/{id}/undelete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function undelete(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            if ($vehicle) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DELETE, $vehicle);
                $this->vehicleService->undelete($vehicle);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/vehicles/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            if ($vehicle) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_ARCHIVE, $vehicle);
                $this->vehicleService->archive($vehicle, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/vehicles/{id}/set-driver/{driverId}', requirements: ['id' => '\d+', 'driverId' => '\d+'], methods: ['POST'])]
    public function setVehicleDriver(Request $request, $id, $driverId): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            /** @var User $driver */
            $driver = $this->em->getRepository(User::class)->find($driverId);
            $startDate = $request->request->get('startDate');

            if ($this->getUser()->isDriverClient() && $this->getUser()->getId() !== $driverId) {
                throw new AccessDeniedException();
            }

            if ($vehicle && $driver) {
                $this->vehicleService->setVehicleDriver($vehicle, $driver, $startDate);
                $this->em->refresh($vehicle);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/vehicles/{id}/unset-driver/{driverId}', requirements: ['id' => '\d+', 'driverId' => '\d+'], methods: ['POST'])]
    public function unsetVehicleDriver(Request $request, $id, $driverId): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            /** @var User $driver */
            $driver = $this->em->getRepository(User::class)->find($driverId);
            if ($this->getUser()->isDriverClient() && $this->getUser()->getId() !== $driverId) {
                throw new AccessDeniedException();
            }

            if ($vehicle && $driver) {
                $vehicle = $this->vehicleService->unsetVehicleDriver($vehicle, $driver);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($vehicle, Vehicle::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/history/vehicles', methods: ['GET'])]
    public function getVehiclesHistory(Request $request): JsonResponse
    {
        try {
            $count = $request->query->get('count');
            $driver = null;
            if ($request->query->get('driverId')) {
                $driver = $this->em->getRepository(User::class)
                    ->find($request->query->get('driverId'));
                $this->denyAccessUnlessGranted(null, $driver->getTeam());
            }

            $history = $this->em->getRepository(DriverHistory::class)
                ->findByParams($driver, $this->getUser(), $count);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($history, ['vehicle', 'startDate', 'finishDate']);
    }

    #[Route('/vehicles-drivers/history', methods: ['GET'])]
    public function driverVehicleHistory(Request $request): JsonResponse
    {
        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');
        $driverId = $request->query->get('driverId');
        $vehicleId = $request->query->get('vehicleId');
        $scope = $request->query->get('scope');

        try {
            $params = $request->query->all();
            $include = array_merge(VehicleRoute::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []);

            if ($vehicleId || $driverId) {
                if ($driverId) {
                    $vehiclesWithRoutes = $this->routeService->getVehiclesWithRoutesForDriver(
                        $driverId,
                        $vehicleId,
                        $dateFrom,
                        $dateTo,
                        $this->getUser(),
                        $include,
                        $scope
                    );
                } else {
                    $vehiclesWithRoutes = $this->routeService->getVehicleWithRoutes(
                        $driverId,
                        $vehicleId,
                        $dateFrom,
                        $dateTo,
                        $this->getUser(),
                        $include,
                        $scope
                    );
                }
            } else {
                $vehiclesWithRoutes = $this->routeService->getVehiclesWithRoutes(
                    $driverId,
                    $this->getUser()->getTeam(),
                    $dateFrom,
                    $dateTo,
                    $this->getUser(),
                    $include,
                    $scope
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehiclesWithRoutes);
    }

    #[Route('/vehicle-notes/{id}/{type}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function vehicleNotes($id, $type)
    {
        $vehicle = $this->vehicleService->getById($id, $this->getUser());

        try {
            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            if ($this->getUser()->isInAdminTeam() || $this->getUser()->isInResellerTeam()) {
                $notesList = $this->noteService->list($vehicle, $type);
            } elseif (
                $this->getUser()->isInClientTeam()
                && ($this->noteService->prepareNoteType($type) === Note::TYPE_CLIENT)) {
                $notesList = $this->noteService->list($vehicle, $type);
            } else {
                throw new AccessDeniedException($this->translator->trans('general.access_denied'));
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($notesList);
    }

    #[Route('/reports/vehicle/summary/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getVehiclesSummary(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_VEHICLE_SUMMARY)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/{id}/devices/sensors/history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleSensorsHistory(Request $request, $id): JsonResponse
    {
        try {
            $sensorsHistory = [];
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $vehicle = $this->vehicleService->getById($id, $this->getUser());

            if ($vehicle) {
                $device = $vehicle->getDevice();

                if ($device) {
                    $sensorsHistory = $this->deviceSensorService
                        ->getSensorsHistoryByDeviceAndRange($device, $startDate, $endDate);
                }
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensorsHistory);
    }

    #[Route('/vehicles/report/sensors/temp-and-humidity', methods: ['GET'])]
    public function getSensorTempAndHumidityVehicleListForReport(
        Request $request,
        DeviceSensorService $deviceSensorService
    ) {
        try {
            $user = $this->getUser();
            $vehicleSensorParams = $deviceSensorService
                ->getParamsForVehicleTempAndHumiditySensorListReport($request->query->all(), $user);
            $vehicles = $this->vehicleService->vehicleList($vehicleSensorParams, $user, true, Vehicle::REPORT_VALUES);

            return $this->viewItem($vehicles, Vehicle::DISPLAYED_VALUES);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/report/io/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getVehiclesIOReport(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_DIGITAL_IO)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types', methods: ['GET'])]
    public function getVehicleTypes(Request $request)
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $fields = $request->query->all()['fields'] ?? [];

            $vehicleTypes = $this->vehicleService->getVehicleTypes($this->getUser(), $request->query->all());

            $pagination = $this->paginator->paginate($vehicleTypes, $page, $limit,
                [PaginatorInterface::SORT_FIELD_PARAMETER_NAME => '~']);
            $pagination = PaginationHelper::paginationToEntityArray(
                $pagination, array_merge($fields, VehicleType::DEFAULT_DISPLAY_VALUES)
            );

            return $this->viewItem($pagination, [], 200);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleTypeById(Request $request, $id)
    {
        try {
            $type = $this->em->getRepository(VehicleType::class)
                ->getVehiclesTypeById($id, $this->getUser());

            return $this->viewItem($type);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types', methods: ['POST'])]
    public function createVehicleType(Request $request)
    {
        try {
            $files = $request->files->all();
            $data = $request->request->all();
            $type = $this->vehicleService->createVehicleType($data, $files, $this->getUser());

            return $this->viewItem($type);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function editVehicleType(Request $request, $id)
    {
        try {
            $files = $request->files->all();
            $data = $request->request->all();
            $type = $this->vehicleService->editVehicleType($id, $data, $files, $this->getUser());

            return $this->viewItem($type);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteVehicleType(Request $request, $id)
    {
        try {
            $type = $this->vehicleService->deleteVehicleType($id, $this->getUser());

            return $this->viewItem($type);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types/{id}/restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restoreVehicleType(Request $request, $id)
    {
        try {
            $type = $this->vehicleService->restoreVehicleType($id, $this->getUser());

            return $this->viewItem($type);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/types/order', methods: ['PATCH'])]
    public function changeCategoryOrder(Request $request)
    {
        try {
            $this->vehicleService->changeOrder($request->request->get('order'), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem([], [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/vehicles/count', methods: ['GET'])]
    public function getCurrentClientVehiclesCount(Request $request)
    {
        try {
            $count = $this->em->getRepository(Vehicle::class)
                ->getVehicleCountByTeam($this->getUser()->getTeam());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(['count' => $count]);
    }

    #[Route('/vehicles/{id}/notes', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createVehicleNotes(Request $request, $id)
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            if ($vehicle) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_EDIT, $vehicle);
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
                $this->vehicleService->handleNotesFields($vehicle, $this->getUser(), $request->request->all());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicle);
    }
}
