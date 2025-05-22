<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\ScheduledReport;
use App\Service\ScheduledReport\Report;
use App\Service\ScheduledReport\ScheduledReportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScheduledReportController extends BaseController
{
    private $scheduledReportService;

    /**
     * @param ScheduledReportService $scheduledReportService
     */
    public function __construct(ScheduledReportService $scheduledReportService)
    {
        $this->scheduledReportService = $scheduledReportService;
    }

    #[Route('/scheduled-report', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::SCHEDULED_REPORT_CREATE, ScheduledReport::class);
        try {
            $scheduledReport = $this->scheduledReportService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($scheduledReport, ScheduledReport::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/scheduled-report/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $scheduledReport = $this->scheduledReportService->getById($id, $this->getUser());

        if ($scheduledReport) {
            $this->denyAccessUnlessGranted(Permission::SCHEDULED_REPORT_EDIT, $scheduledReport);
            try {
                $scheduledReport = $this->scheduledReportService
                    ->edit($scheduledReport, $request->request->all(), $this->getUser());
            } catch (\Exception $ex) {
                return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->viewItem($scheduledReport, ScheduledReport::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/scheduled-report/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        $scheduledReport = $this->scheduledReportService->getById($id, $this->getUser());

        if ($scheduledReport) {
            $this->denyAccessUnlessGranted(Permission::SCHEDULED_REPORT_DELETE, $scheduledReport);
            try {
                $this->scheduledReportService->delete($scheduledReport);
            } catch (\Exception $ex) {
                return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/scheduled-report/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        $scheduledReport = $this->scheduledReportService->getById($id, $this->getUser());

        if ($scheduledReport) {
            $this->denyAccessUnlessGranted(Permission::SCHEDULED_REPORT_EDIT, $scheduledReport);
            try {
                $this->scheduledReportService->restore($scheduledReport);
            } catch (\Exception $ex) {
                return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->viewItem($scheduledReport, ScheduledReport::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/scheduled-report/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getById(Request $request, $id): JsonResponse
    {
        $scheduledReport = $this->scheduledReportService->getById($id, $this->getUser());

        if ($scheduledReport) {
            $this->denyAccessUnlessGranted(Permission::SCHEDULED_REPORT_LIST, $scheduledReport);
            try {
                return $this->viewItem($scheduledReport, ScheduledReport::DEFAULT_DISPLAY_VALUES);
            } catch (\Exception $ex) {
                return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->viewItem(null, ScheduledReport::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/scheduled-report/template', methods: ['GET'])]
    public function template(Request $request)
    {
        return $this->viewItem(Report::REPORT_UI_SCHEME);
    }

    #[Route('/scheduled-report/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function scheduledReportList(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::SCHEDULED_REPORT_LIST, ScheduledReport::class);
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $vehicles = $this->scheduledReportService->list($params, $this->getUser());

                    return $this->viewItem($vehicles);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }
}