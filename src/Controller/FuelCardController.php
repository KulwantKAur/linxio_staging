<?php

namespace App\Controller;

use App\Entity\FuelCard\FuelCard;
use App\Entity\Permission;
use App\Entity\Team;
use App\Entity\Vehicle;
use App\Service\FuelCard\FuelCardService;
use App\Service\FuelCard\Report\FuelCardReportService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FuelCardController extends BaseController
{
    private $fuelCardService;
    private $fuelCardReportService;
    private ReportService $reportService;

    public function __construct(
        FuelCardService $fuelCardService,
        FuelCardReportService $fuelCardReportService,
        ReportService $reportService
    ) {
        $this->fuelCardService = $fuelCardService;
        $this->fuelCardReportService = $fuelCardReportService;
        $this->reportService = $reportService;
    }

    #[Route('/fuel-cards/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getFuelCard(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_RECORDS, FuelCard::class);
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_FUEL_RECORDS)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fuel-cards-by-vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getFuelCardByVehicle(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_RECORDS, FuelCard::class);
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_FUEL_RECORDS_BY_VEHICLE)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fuel-cards/vehicles', methods: ['GET'])]
    public function getFuelCardVehicles(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_RECORDS, FuelCard::class);
        try {
            $params = $request->query->all();
            $vehicles = $this->fuelCardReportService->getFuelCardVehicles($params, $this->getUser());

            return $this->viewItem($vehicles, Vehicle::DISPLAYED_VALUES);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fuel-cards/upload', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_IMPORT_DATA, FuelCard::class);

        try {
            $fileData = $this->fuelCardService->parseFiles(
                array_merge_recursive(
                    $request->request->all(),
                    ['files' => $request->files]
                ),
                $this->getUser()
            );

            $dataImport = [];
            foreach ($fileData['data'] as $entity) {
                $dataImport[] = $entity->toArray(FuelCard::DISPLAYED_VALUES_TEMPORARY);
            }

            return $this->viewItem([
                'file' => $fileData['file'],
                'data' => $dataImport,
            ]);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fuel-cards/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_FILE_DELETE, FuelCard::class);
        try {
            $this->fuelCardService->deleteByFileId($id);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem(null, [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/fuel-cards/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_IMPORT_DATA, FuelCard::class);
        try {
            $this->fuelCardService->updateByFileId($id);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem(null, [], 201);
    }

    #[Route('/fuel-cards/check-display-fields', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function checkDisplayAdditionalFields(Request $request)
    {
//        //TODO
//        return $this->viewItem(['isDisplayAdditionalFields' => true]);

        $this->denyAccessUnlessGranted(Permission::FUEL_RECORDS, FuelCard::class);
        try {
            $isDisplayAdditionalFields = $this->fuelCardService->checkDisplayAdditionalFields($this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem(['isDisplayAdditionalFields' => $isDisplayAdditionalFields]);
    }

    #[Route('/fuel-summary-report/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getFuelSummary(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_SUMMARY, FuelCard::class);
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_FUEL_SUMMARY)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/fuel-cards/record/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function editRecord(Request $request, $id, EntityManager $em)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_RECORD_UPDATE, FuelCard::class);
        try {
            $fuelCard = $em->getRepository(FuelCard::class)->find($id);
            if ($fuelCard) {
                $team = $em->getRepository(Team::class)->find($fuelCard->getTeamId());
                $this->denyAccessUnlessGranted(null, $team);
                $this->fuelCardService->updateRecord($fuelCard, $request->request->all(), $this->getUser());
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($fuelCard);
    }
}
