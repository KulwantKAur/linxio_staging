<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\ServiceRecord;
use App\Entity\UserGroup;
use App\EntityManager\SlaveEntityManager;
use App\Report\Builder\Maintenance\ServiceRecordsSummaryReportBuilder;
use App\Response\CsvResponse;
use App\Service\PdfService;
use App\Service\Reminder\ReminderService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\ServiceRecord\ServiceRecordService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceRecordController extends BaseController
{
    private $serviceRecordService;
    private $reminderService;
    private $pdfService;
    private $paginator;
    private SlaveEntityManager $emSlave;
    private ReportService $reportService;

    public function __construct(
        ServiceRecordService $serviceRecordService,
        ReminderService $reminderService,
        PdfService $pdfService,
        PaginatorInterface $paginator,
        SlaveEntityManager $emSlave,
        ReportService $reportService
    ) {
        $this->serviceRecordService = $serviceRecordService;
        $this->reminderService = $reminderService;
        $this->pdfService = $pdfService;
        $this->paginator = $paginator;
        $this->emSlave = $emSlave;
        $this->reportService = $reportService;
    }

    #[Route('/reminders/{id}/service-records/', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function create(Request $request, $id): JsonResponse
    {
        $reminder = $this->reminderService->getById($id, $this->getUser());

        if ($reminder->isVehicleReminder()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_NEW, ServiceRecord::class);
        } else {
            $this->denyAccessUnlessGranted(Permission::ASSET_SERVICE_RECORD_NEW, ServiceRecord::class);
        }
        try {
            $serviceRecord = $this->serviceRecordService->create(
                array_merge($request->request->all(), ['files' => $request->files, 'reminder' => $reminder]),
                $this->getUser(),
                $reminder
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecord);
    }

    #[Route('/reminders/{id}/service-records/{serviceRecordId}', requirements: ['id' => '\d+', 'serviceRecordId' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, $id, $serviceRecordId): JsonResponse
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            $serviceRecord = $this->serviceRecordService->getByReminderIdAndServiceRecordId($serviceRecordId, $id);

            if ($serviceRecord && $reminder) {
                $this->denyAccessUnlessGranted(null, $serviceRecord->getReminder()->getTeam());
                $serviceRecord = $this->serviceRecordService->edit(
                    array_merge(
                        $request->request->all(),
                        ['files' => $request->files, 'reminder' => $serviceRecord->getReminder()]
                    ),
                    $this->getUser(),
                    $serviceRecord
                );
            } else {
                $serviceRecord = null;
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecord);
    }

    #[Route('/reminders/{id}/service-records/{serviceRecordId}', requirements: ['id' => '\d+', 'serviceRecordId' => '\d+'], methods: ['GET'])]
    public function getServiceRecordById(Request $request, $id, $serviceRecordId): JsonResponse
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder->isVehicleReminder()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_LIST, ServiceRecord::class);
            } else {
                $this->denyAccessUnlessGranted(Permission::ASSET_SERVICE_RECORD_LIST, ServiceRecord::class);
            }
            $serviceRecord = $this->serviceRecordService->getByReminderIdAndServiceRecordId($serviceRecordId, $id);

            if ($reminder && $serviceRecord) {
                $this->denyAccessUnlessGranted(null, $serviceRecord->getReminder()->getTeam());
            } else {
                $serviceRecord = null;
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecord);
    }

    #[Route('/reminders/{id}/service-records', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function serviceRecordList(Request $request, $id): JsonResponse
    {
        $reminder = $this->reminderService->getById($id, $this->getUser());
        if ($reminder->isVehicleReminder()) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_LIST, ServiceRecord::class);
        } else {
            $this->denyAccessUnlessGranted(Permission::ASSET_SERVICE_RECORD_LIST, ServiceRecord::class);
        }
        try {
            if ($reminder) {
                $this->denyAccessUnlessGranted(null, $reminder->getTeam());
            }
            $serviceRecords = $this->serviceRecordService->serviceRecordList(
                array_merge($request->query->all(), ['reminderId' => $reminder ? $reminder->getId() : []]),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecords);
    }

    #[Route('/reminders/service-records/', methods: ['GET'])]
    public function allServiceRecordList(Request $request, EntityManager $em): JsonResponse
    {
//        $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_LIST, ServiceRecord::class);
        $serviceRecordIds = array_column(array_values($em->getRepository(ServiceRecord::class)
            ->getServiceRecordIdsByTeam($this->getUser()->getTeam())), 'id');

        try {
            if ($this->getUser()->isInClientTeam()) {
                $data = ['id' => $serviceRecordIds ? $serviceRecordIds : []];
            } else {
                $data = [];
            }
            if ($this->getUser()->needToCheckUserGroup()) {
                $vehicleIds = $em->getRepository(UserGroup::class)
                    ->getUserVehiclesIdFromUserGroup($this->getUser());
                $data['srVehicleId'] = $vehicleIds;
            }
            $serviceRecords = $this->serviceRecordService->serviceRecordList(
                array_merge($request->query->all(), $data),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($serviceRecords);
    }

    #[Route('/reminders/service-records/vehicles', methods: ['POST'])]
    public function serviceRecordsVehicles(Request $request): JsonResponse
    {
//        $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_LIST, ServiceRecord::class);

        try {
            $vehicles = $this->serviceRecordService->serviceRecordsVehicles(
                $request->request->all(),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicles);
    }

    #[Route('/repairs/detailed/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function repairsDetailed(Request $request, $type)
    {
        try {
//            $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_LIST, ServiceRecord::class);
            return $this->reportService
                ->init(ReportMapper::TYPE_REPAIRS_DETAILED)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/repairs/detailed-by-vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function repairsDetailedByVehicle(Request $request, $type)
    {
        try {
//            $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_LIST, ServiceRecord::class);
            return $this->reportService
                ->init(ReportMapper::TYPE_REPAIRS_BY_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/repairs/vehicles', methods: ['POST'])]
    public function repairVehicles(Request $request): JsonResponse
    {
        try {
            $vehicles = $this->serviceRecordService->repairsVehicles($request->request->all(), $this->getUser());

            return $this->viewItem($vehicles);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/{id}/service-records/{serviceRecordId}', requirements: ['id' => '\d+', 'serviceRecordId' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id, $serviceRecordId): JsonResponse
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder->isVehicleReminder()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_SERVICE_RECORD_DELETE, ServiceRecord::class);
            } else {
                $this->denyAccessUnlessGranted(Permission::ASSET_SERVICE_RECORD_DELETE, ServiceRecord::class);
            }
            $serviceRecord = $this->serviceRecordService->getByReminderIdAndServiceRecordId($serviceRecordId, $id);

            if ($reminder && $serviceRecord) {
                $this->denyAccessUnlessGranted(null, $serviceRecord->getReminder()->getTeam());
                $this->serviceRecordService->removeServiceRecord($serviceRecord);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/reminders/service-records/summary/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getServiceSummaryReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_SERVICE_RECORDS_SUMMARY)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/service-records/summary/vehicles', methods: ['GET'])]
    public function getServiceSummaryReportVehicles(Request $request)
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            //TODO refactoring (as report call?)
            $vehiclesQuery = ServiceRecordsSummaryReportBuilder::generateData(
                $request->query->all(), $this->getUser(), $this->emSlave, true
            );

            $pagination = $this->paginator->paginate($vehiclesQuery, $page, $limit);

            return $this->viewItem($pagination);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/service-records/detailed/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getServiceDetailedReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_SERVICE_RECORDS_DETAILED)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/service-records/detailed-by-vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getServiceDetailedByVehicleReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_SERVICE_RECORDS_BY_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/service-records/detailed/vehicles', methods: ['GET'])]
    public function getServiceDetailedReportVehicles(Request $request)
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $recordsQuery = $this->serviceRecordService->detailedReport(
                $request->query->all(),
                $this->getUser(),
                ServiceRecord::TYPE_SERVICE_RECORD,
                true
            );

            $pagination = $this->paginator->paginate($recordsQuery, $page, $limit);
            $firstItem = $pagination->count() ? $pagination->current() : null;

            return $this->viewItem($pagination, [], 200, ['total' => $firstItem]);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/service-records/common/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getCommonDetailedReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_MAINTENANCE_TOTAL)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/service-records/common-by-vehicle/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getCommonDetailedByVehicleReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_MAINTENANCE_TOTAL_BY_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/service-records/common/vehicles/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getCommonDetailedVehicles(Request $request): JsonResponse
    {
        try {
            $vehicles = $this->serviceRecordService->commonVehicles($request->request->all(), $this->getUser());

            return $this->viewItem($vehicles);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }
}