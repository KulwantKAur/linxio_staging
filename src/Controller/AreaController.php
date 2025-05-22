<?php

namespace App\Controller;

use App\Entity\Area;
use App\Entity\Permission;
use App\Report\Builder\Area\AreaVisitedReportBuilder;
use App\Service\Area\AreaService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\Vehicle\VehicleService;
use App\Util\ArrayHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AreaController extends BaseController
{
    private AreaService $areaService;
    private PaginatorInterface $paginator;
    private ReportService $reportService;
    private VehicleService $vehicleService;
    private EntityManagerInterface $em;

    public function __construct(
        AreaService $areaService,
        PaginatorInterface $paginator,
        ReportService $reportService,
        VehicleService $vehicleService,
        EntityManagerInterface $em
    ) {
        $this->areaService = $areaService;
        $this->paginator = $paginator;
        $this->reportService = $reportService;
        $this->vehicleService = $vehicleService;
        $this->em = $em;
    }

    #[Route('/areas', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::AREA_NEW, Area::class);
        try {
            $area = $this->areaService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($area, Area::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/areas/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getAreaById(Request $request, $id): JsonResponse
    {
        try {
            $area = $this->areaService->getById($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($area, Area::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/areas', methods: ['GET'])]
    public function areasList(Request $request): JsonResponse
    {
        try {
            $areas = $this->areaService->areaList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areas);
    }

    #[Route('/areas/dropdown', methods: ['GET'])]
    public function areasListDropdown(Request $request): JsonResponse
    {
        try {
            $areas = $this->areaService->areaList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areas);
    }

    #[Route('/areas/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $area = $this->areaService->getById($id, $this->getUser());
            if ($area) {
                $this->denyAccessUnlessGranted(Permission::AREA_EDIT, $area);
                $area = $this->areaService->edit($request->request->all(), $this->getUser(), $area);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($area, Area::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/areas/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $area = $this->areaService->getById($id, $this->getUser());
            if ($area) {
                $this->denyAccessUnlessGranted(Permission::AREA_DELETE, $area);
                $this->areaService->remove($area, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/areas/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            $area = $this->areaService->getById($id, $this->getUser());
            if ($area) {
                $this->denyAccessUnlessGranted(Permission::AREA_ARCHIVE, $area);
                $this->areaService->restore($area, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($area, Area::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/areas/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $area = $this->areaService->getById($id, $this->getUser());
            if ($area) {
                $this->denyAccessUnlessGranted(Permission::AREA_ARCHIVE, $area);
                $this->areaService->archive($area, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($area, Area::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/areas/check-point', methods: ['POST'])]
    public function checkPointInArea(Request $request): JsonResponse
    {
        try {
            $areas = $this->areaService->checkPointInArea($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($areas, Area::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/reports/areas/visited/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getVisitedAreas(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_AREA_VISITED)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reports/areas/visited/vehicles/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getVisitedAreasVehicles(Request $request, $type)
    {
        $params = $request->request->all();
        $page = $request->request->get('page', 1);
        $limit = $request->request->get('limit', 10);

        //TODO refactoring (as report call?)
        $data = AreaVisitedReportBuilder::generateData(
            $params, $this->getUser(), $this->vehicleService, $this->em, true);

        $pagination = $this->paginator->paginate($data, $page, $limit);
        $pagination->setItems(ArrayHelper::keysToCamelCase($pagination->getItems()));

        return $this->viewItem($pagination, [], 200);
    }

    #[Route('/reports/areas/not-visited/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getNotVisitedAreas(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_AREA_NOT_VISITED)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reports/areas/summary/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getAreaSummary(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_AREA_SUMMARY)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }
    
    #[Route('/areas/users/import/', methods: ['POST'])]
    public function areaImport(Request $request) {
        try {
            $cid = (int)$request->request->get('clientID');
            $data = $request->files->get('csv_file');
            $destination = $this->getParameter('kernel.project_dir') . '/web/uploads/';

            $insertData = $this->areaService->areaCustomImport($cid, $data, $destination);
            
            return $this->viewItem($insertData, Area::DEFAULT_DISPLAY_VALUES);
        } catch (Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }
}
