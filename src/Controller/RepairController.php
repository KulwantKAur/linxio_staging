<?php

namespace App\Controller;


use App\Entity\Asset;
use App\Entity\Permission;
use App\Entity\ServiceRecord;
use App\Entity\Vehicle;
use App\Response\CsvResponse;
use App\Service\Reminder\ReminderService;
use App\Service\ServiceRecord\ServiceRecordService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RepairController extends BaseController
{
    private $serviceRecordService;
    private $reminderService;
    private EntityManager $em;

    public function __construct(
        ServiceRecordService $serviceRecordService,
        ReminderService $reminderService,
        EntityManager $em
    ) {
        $this->serviceRecordService = $serviceRecordService;
        $this->reminderService = $reminderService;
        $this->em = $em;
    }

    #[Route('/repairs', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::REPAIR_COST_NEW, ServiceRecord::class);

        try {
            $serviceRecord = $this->serviceRecordService->createRepair(
                array_merge($request->request->all(), ['files' => $request->files]),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecord);
    }

    #[Route('/repairs/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::REPAIR_COST_EDIT, ServiceRecord::class);
        try {
            $serviceRecord = $this->serviceRecordService->getById($id, $this->getUser());

            if ($serviceRecord) {
                $this->denyAccessUnlessGranted(null, $serviceRecord->getTeam());
                $serviceRecord = $this->serviceRecordService->editRepair(
                    array_merge($request->request->all(), ['files' => $request->files]),
                    $this->getUser(),
                    $serviceRecord
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecord);
    }

    #[Route('/repairs/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getRepairById(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::REPAIR_COST_LIST, ServiceRecord::class);
        try {
            $serviceRecord = $this->serviceRecordService->getById($id, $this->getUser());

            if ($serviceRecord) {
                $this->denyAccessUnlessGranted(null, $serviceRecord->getTeam());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecord);
    }

    #[Route('/repairs/vehicle/{vehicleId}/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function repairList(Request $request, $vehicleId, $type)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::REPAIR_COST_LIST, ServiceRecord::class);
            $params = $request->query->all();
            $vehicle = $this->em->getRepository(Vehicle::class)
                ->getVehicleById($this->getUser(), $vehicleId);
            if ($vehicle) {
                $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            } else {
                throw new \Exception('Vehicle is not found');
            }
            switch ($type) {
                case 'json':
                    $serviceRecords = $this->serviceRecordService->repairList(
                        array_merge($request->query->all(), ['vehicleId' => $vehicleId]),
                        $this->getUser(),
                        $vehicle
                    );

                    return $this->viewItem($serviceRecords);
                case 'csv':
                    $serviceRecords = $this->serviceRecordService->getRepairListByVehicleExportData(
                        $params,
                        $vehicle,
                        $this->getUser()
                    );

                    return new CsvResponse($serviceRecords);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/repairs/asset/{assetId}/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function assetRepairList(Request $request, $assetId, $type)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::REPAIR_COST_LIST, ServiceRecord::class);
            $params = $request->query->all();

            /** @var Asset $this */
            $asset = $this->em->getRepository(Asset::class)->find($assetId);
            if ($asset) {
                $this->denyAccessUnlessGranted(null, $asset->getTeam());
            }
            switch ($type) {
                case 'json':
                    $serviceRecords = $this->serviceRecordService->repairList(
                        array_merge($request->query->all(), ['assetId' => $assetId]),
                        $this->getUser(), null, true, $asset
                    );

                    return $this->viewItem($serviceRecords);
                case 'csv':
                    $serviceRecords = $this->serviceRecordService->getRepairListByAssetExportData(
                        $params,
                        $asset,
                        $this->getUser()
                    );

                    return new CsvResponse($serviceRecords);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/repairs/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function repairs(Request $request, $type)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::REPAIR_COST_LIST, ServiceRecord::class);
            $params = $request->query->all();
            switch ($type) {
                case 'json':
                    $serviceRecords = $this->serviceRecordService->repairList(
                        $request->query->all(),
                        $this->getUser()
                    );

                    return $this->viewItem($serviceRecords);
                case 'csv':
                    $serviceRecords = $this->serviceRecordService->getRepairListExportData(
                        $request->query->all(),
                        $this->getUser()
                    );

                    return new CsvResponse($serviceRecords);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/repairs/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::REPAIR_COST_DELETE, ServiceRecord::class);
        try {
            $serviceRecord = $this->serviceRecordService->getById($id, $this->getUser());

            if ($serviceRecord) {
                $this->denyAccessUnlessGranted(null, $serviceRecord->getTeam());
                $this->serviceRecordService->removeRepair($serviceRecord);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/dashboard/repairs/stat', methods: ['GET'])]
    public function getRepairsDashboardStatistic(Request $request): JsonResponse
    {
        try {
            $days = $request->query->get('days', 7);
            $data = $this->serviceRecordService->getTeamRepairStat($this->getUser(), $days);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }
}