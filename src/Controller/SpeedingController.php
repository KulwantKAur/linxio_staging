<?php

namespace App\Controller;

use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\Speeding\SpeedingService;
use App\Service\User\UserService;
use App\Service\Vehicle\VehicleService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpeedingController extends BaseController
{
    private $speedingService;
    private $vehicleService;
    private $userService;
    private ReportService $reportService;

    public function __construct(
        SpeedingService $speedingService,
        VehicleService $vehicleService,
        UserService $userService,
        ReportService $reportService
    ) {
        $this->speedingService = $speedingService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->reportService = $reportService;
    }

    #[Route('/reports/speeding/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getSpeedingReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_SPEEDING)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reports/speeding-by-vehicle', methods: ['GET'])]
    public function getSpeedingsGroupedByVehicle(Request $request): JsonResponse
    {
        try {
            $pagerfanta = $this->speedingService->getPaginatedGroupedVehicles($request, $this->getUser());

            return new JsonResponse(
                [
                    'page' => $pagerfanta->getCurrentPage(),
                    'limit' => $pagerfanta->getMaxPerPage(),
                    'total' => $pagerfanta->getNbResults(),
                    'data' => $pagerfanta->getCurrentPageResults(),
                ]
            );
        } catch (Exception $ex) {
        }

        return $this->viewError('An error occurred. Please contact you administrator.');
    }

    #[Route('/reports/speeding/vehicle/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getSpeedingByVehicle(Request $request, $id): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getById($id, $this->getUser());
            $pagerfanta = $this->speedingService->getPaginatedSpeedingByVehicle($request, $vehicle);

            return new JsonResponse(
                [
                    'page' => $pagerfanta->getCurrentPage(),
                    'limit' => $pagerfanta->getMaxPerPage(),
                    'total' => $pagerfanta->getNbResults(),
                    'data' => $pagerfanta->getCurrentPageResults(),
                ]
            );
        } catch (Exception $ex) {
        }

        return $this->viewError('An error occurred. Please contact you administrator.');
    }

    #[Route('/reports/speeding-by-driver', methods: ['GET'])]
    public function getSpeedingsGroupedByDriver(Request $request): JsonResponse
    {
        try {
            $pagerfanta = $this->speedingService->getPaginatedSpeedingsGroupedByDriver($request, $this->getUser());

            return new JsonResponse(
                [
                    'page' => $pagerfanta->getCurrentPage(),
                    'limit' => $pagerfanta->getMaxPerPage(),
                    'total' => $pagerfanta->getNbResults(),
                    'data' => $pagerfanta->getCurrentPageResults(),
                ]
            );
        } catch (Exception $ex) {
            print_r($ex->getMessage());
        }

        return $this->viewError('An error occurred. Please contact you administrator.');
    }

    #[Route('/reports/speeding/driver/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getSpeedingByDriver(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            $pagerfanta = $this->speedingService->getPaginatedSpeedingByDriver($request, $user, $this->getUser());

            return new JsonResponse(
                [
                    'page' => $pagerfanta->getCurrentPage(),
                    'limit' => $pagerfanta->getMaxPerPage(),
                    'total' => $pagerfanta->getNbResults(),
                    'data' => $pagerfanta->getCurrentPageResults(),
                ]
            );
        } catch (Exception $ex) {
        }

        return $this->viewError('An error occurred. Please contact you administrator.');
    }
}
