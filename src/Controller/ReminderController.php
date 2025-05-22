<?php

namespace App\Controller;


use App\Entity\Permission;
use App\Entity\Reminder;
use App\Entity\ReminderCategory;
use App\Response\CsvResponse;
use App\Response\PdfResponse;
use App\Service\PdfService;
use App\Service\ReminderCategory\ReminderCategoryService;
use App\Service\Reminder\ReminderService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReminderController extends BaseController
{
    private $reminderService;
    private $reminderCategoryService;
    private $pdfService;
    private ReportService $reportService;

    public function __construct(
        ReminderService $reminderService,
        ReminderCategoryService $reminderCategoryService,
        PdfService $pdfService,
        ReportService $reportService
    ) {
        $this->reminderService = $reminderService;
        $this->reminderCategoryService = $reminderCategoryService;
        $this->pdfService = $pdfService;
        $this->reportService = $reportService;
    }

    #[Route('/reminders/category', methods: ['POST'])]
    public function createReminderCategory(Request $request)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::REMINDER_CATEGORY_NEW, ReminderCategory::class);
            $category = $this->reminderCategoryService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($category);
    }

    #[Route('/reminders/category/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function editReminderCategory(Request $request, $id)
    {
        $category = null;
        try {
            $reminderCategory = $this->reminderCategoryService->getById($id);
            if ($reminderCategory) {
                $this->denyAccessUnlessGranted(Permission::REMINDER_CATEGORY_EDIT, ReminderCategory::class);

                $category = $this->reminderCategoryService
                    ->edit($request->request->all(), $reminderCategory, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($category);
    }

    #[Route('/reminders/category/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function removeReminderCategory(Request $request, $id): JsonResponse
    {
        try {
            $reminderCategory = $this->reminderCategoryService->getById($id);
            if ($reminderCategory) {
                $this->denyAccessUnlessGranted(Permission::REMINDER_CATEGORY_DELETE, ReminderCategory::class);

                $this->reminderCategoryService->remove($reminderCategory);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/reminders/category/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function reminderCategoryList(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::REMINDER_CATEGORY_LIST, Reminder::class);
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $reminders = $this->reminderCategoryService->list($params, $this->getUser());

                    return $this->viewItem($reminders);
                case 'csv':
                    $reminders = $this->reminderCategoryService->getReminderCategoryExportData(
                        $params, $this->getUser(), false
                    );

                    return new CsvResponse($reminders);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/category/dropdown', methods: ['GET'])]
    public function reminderCategoryListDropdown(Request $request)
    {
        try {
            $params = $request->query->all();
            $reminders = $this->reminderCategoryService->list($params, $this->getUser());

            return $this->viewItem($reminders);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if ($request->request->get('vehicleId', null)) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_NEW, Reminder::class);
        }
        if ($request->request->get('assetId', null)) {
            $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_NEW, Reminder::class);
        }
        try {
            $reminder = $this->reminderService->create(
                array_merge($request->request->all(), ['files' => $request->files]),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reminder);
    }

    #[Route('/reminders/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function reminders(Request $request, $type)
    {
//        $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_LIST, Reminder::class);
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_MAINTENANCE_SUMMARY)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getReminderById(Request $request, $id): JsonResponse
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());

            if ($reminder) {
                if ($reminder->isVehicleReminder()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_LIST, Reminder::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_LIST, Reminder::class);
                }
                $this->denyAccessUnlessGranted(null, $reminder->getEntity()->getTeam());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reminder);
    }

    #[Route('/reminders/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder) {
                if ($reminder->isVehicleReminder()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_EDIT, Reminder::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_EDIT, Reminder::class);
                }
                $this->denyAccessUnlessGranted(null, $reminder->getEntity()->getTeam());
                $reminder = $this->reminderService->edit(
                    array_merge($request->request->all(), ['files' => $request->files]),
                    $this->getUser(),
                    $reminder
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reminder);
    }

    #[Route('/reminders/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder) {
                if ($reminder->isVehicleReminder()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_DELETE, Reminder::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_DELETE, Reminder::class);
                }
                $this->denyAccessUnlessGranted(null, $reminder->getEntity()->getTeam());
                $this->reminderService->removeReminder($reminder);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/reminders-report/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function getRemindersReport(Request $request, $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_MAINTENANCE_SUMMARY)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reminders/{id}/duplicate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function duplicateReminder(Request $request, $id)
    {
        try {
            $assets = $request->request->all()['assets'] ?? [];
            $vehicles = $request->request->all()['vehicles'] ?? [];
            $depots = $request->request->all()['depots'] ?? [];
            $groups = $request->request->all()['groups'] ?? [];
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder) {
                if ($reminder->isVehicleReminder()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_NEW, Reminder::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_NEW, Reminder::class);
                }
                $this->denyAccessUnlessGranted(null, $reminder->getEntity()->getTeam());
                $reminder = $this->reminderService
                    ->duplicate($reminder, $this->getUser(), $vehicles, $depots, $groups, $assets);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reminder);
    }

    #[Route('/dashboard/reminders/stat', methods: ['GET'])]
    public function getRemindersDashboardStatistic(Request $request): JsonResponse
    {
        try {
            $data = $this->reminderService->getExpiredAndDueSoonStat($this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/reminders/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id)
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder) {
                if ($reminder->isVehicleReminder()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_ARCHIVE, Reminder::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_ARCHIVE, Reminder::class);
                }
                $this->denyAccessUnlessGranted(null, $reminder->getEntity()->getTeam());
                $reminder = $this->reminderService->restore($reminder, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reminder);
    }

    #[Route('/reminders/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id)
    {
        try {
            $reminder = $this->reminderService->getById($id, $this->getUser());
            if ($reminder) {
                if ($reminder->isVehicleReminder()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_REMINDER_ARCHIVE, Reminder::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_REMINDER_ARCHIVE, Reminder::class);
                }
                $this->denyAccessUnlessGranted(null, $reminder->getEntity()->getTeam());
                $reminder = $this->reminderService->archive($reminder, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($reminder);
    }

    #[Route('/reminders/category/order', methods: ['PATCH'])]
    public function changeCategoryOrder(Request $request)
    {
        try {
            $this->denyAccessUnlessGranted(Permission::REMINDER_CATEGORY_EDIT, Reminder::class);
            $this->reminderCategoryService->changeOrder($request->request->get('order'));
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem([], [], Response::HTTP_NO_CONTENT);
    }
}