<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Response\PdfResponse;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\Route\RouteService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reports')]
class ReportController extends BaseController
{
    private ReportService $reportService;
    private RouteService $routeService;
    private PaginatorInterface $paginator;

    public function __construct(
        ReportService $reportService,
        RouteService $routeService,
        PaginatorInterface $paginator
    ) {
        $this->reportService = $reportService;
        $this->routeService = $routeService;
        $this->paginator = $paginator;
    }

    #[Route('/by-plan', methods: ['GET'])]
    public function getReportGroups()
    {
        if ($this->getUser()->isInClientTeam()) {
            $reports = ReportMapper::getReportsByPlan($this->getUser()->getPlan());
            $customReports = $this->getUser()->getTeam()->getSettingsByName(Setting::REPORTS);

            $reports = ReportMapper::mergeReports($reports, $customReports?->getValue() ?? []);
        } else {
            $reports = ReportMapper::REPORTS_BY_PLAN;
        }

        return self::viewItem($reports);
    }

    #[Route('/fbt-report/vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function fbtVehicleReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_FBT_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fbt-report/driver/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function fbtDriverReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_FBT_DRIVER)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fbt-business-report/driver/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function fbtBusinessDriverReport(Request $request, $type)
    {
        try {
            $reportsSetting = $this->getUser()->getTeam()->getSettingsByName(Setting::REPORTS)?->getValue() ?? [];
            if (!in_array(ReportMapper::TYPE_FBT_BUSINESS_DRIVER, $reportsSetting)) {
                throw new AccessDeniedException();
            }

            return $this->reportService
                ->init(ReportMapper::TYPE_FBT_BUSINESS_DRIVER)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fbt-business-report/vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function fbtBusinessVehicleReport(Request $request, $type)
    {
        try {
            $reportsSetting = $this->getUser()->getTeam()->getSettingsByName(Setting::REPORTS)?->getValue() ?? [];
            if (!in_array(ReportMapper::TYPE_FBT_BUSINESS_VEHICLE, $reportsSetting)) {
                throw new AccessDeniedException();
            }

            return $this->reportService
                ->init(ReportMapper::TYPE_FBT_BUSINESS_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicle/day-summary/vehicles', methods: ['POST'])]
    public function getVehicleDaySummaryVehicles(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $vehiclesQuery = $this->routeService->daySummaryReportVehicles($request->request->all(), $this->getUser());
            $pagination = $this->paginator->paginate($vehiclesQuery, $page, $limit);

            return $this->viewItem($pagination);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicle/day-summary/{type}', requirements: ['type' => 'json|csv|pdf|xlsx'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getVehicleDaySummary(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_VEHICLE_DAY_SUMMARY)
                ->getReport(
                    $type, array_merge($request->request->all(),
                    ['vehicleIds' => $request->request->all()['vehicleIds'] ?? []]),
                    $this->getUser()
                );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }
}