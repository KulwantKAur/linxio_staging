<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DeviceModel;
use App\Entity\DeviceVendor;
use App\Entity\Note;
use App\Entity\Permission;
use App\Enums\EntityHistoryTypes;
use App\Response\CsvResponse;
use App\Service\Device\DeviceService;
use App\Service\Device\Import\DevicesVehiclesDriverImport;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\Note\NoteService;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceController extends BaseController
{
    private $deviceService;
    private $vehicleService;
    private $paginator;
    private $noteService;
    private $translator;

    public function __construct(
        DeviceService $deviceService,
        VehicleService $vehicleService,
        PaginatorInterface $paginator,
        NoteService $noteService,
        TranslatorInterface $translator
    ) {
        $this->deviceService = $deviceService;
        $this->vehicleService = $vehicleService;
        $this->paginator = $paginator;
        $this->noteService = $noteService;
        $this->translator = $translator;
    }

    #[Route('/devices', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::DEVICE_NEW, Device::class);
        try {
            $device = $this->deviceService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        $fields = array_merge(Device::DEFAULT_DISPLAY_VALUES, ['userName', 'password']);

        return $this->viewItem($device, $fields, 200, null, $this->getUser());
    }

    #[Route('/devices/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function deviceList(Request $request, $type)
    {
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $devices = $this->deviceService->deviceList($params, $this->getUser());

                    return $this->viewItem($devices);
                case 'csv':
                    return $this->deviceService->getDeviceListExportData($params, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDeviceById(Request $request, $id): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            if ($device) {
                $this->denyAccessUnlessGranted(Permission::DEVICE_LIST, $device);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        $fields = array_merge(
            Device::DEFAULT_DISPLAY_VALUES,
            ['userName', 'password', 'model.name', 'vendor.name'],
            $request->query->all('fields')
        );

        return $this->viewItem($device, $fields, 200, null, $this->getUser());
    }

    #[Route('/devices/{id}/install', requirements: ['id' => '\d+'], methods:[] ['POST'])]
    public function installDevice(Request $request, $id): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            if ($device) {
                $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, $device);
                $params = array_merge($request->request->all(), ['files' => $request->files]);
                $device = $this->deviceService->installDevice($params, $device, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $device,
            array_merge(Device::DEFAULT_DISPLAY_VALUES, ['model.name']),
            Response::HTTP_OK,
            null,
            $this->getUser()
        );
    }

    #[Route('/devices/{id}/uninstall', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uninstallDevice(Request $request, $id): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            if ($device) {
                $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, $device);
            }

            $device = $this->deviceService->uninstallDevice(
                array_merge(
                    $request->request->all(),
                    [
                        'device' => $device,
                        'updatedBy' => $this->getUser()
                    ]
                ),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $device,
            array_merge(Device::DEFAULT_DISPLAY_VALUES, ['model.name']),
            Response::HTTP_OK,
            null,
            $this->getUser()
        );
    }

    #[Route('/devices/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id, EntityManager $em): JsonResponse
    {
        try {
            $device = $em->getRepository(Device::class)->find($id);

            if ($device) {
                $this->denyAccessUnlessGranted(Permission::DEVICE_EDIT, $device);
                $device = $this->deviceService->edit($request->request->all(), $device, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        $fields = array_merge(Device::DEFAULT_DISPLAY_VALUES, ['userName', 'password']);

        return $this->viewItem($device, $fields, 200, null, $this->getUser());
    }

    #[Route('/devices/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            if ($device) {
                $this->denyAccessUnlessGranted(Permission::DEVICE_DELETE, $device);
                $this->deviceService->removeDevice($device, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/devices/{id}/coordinates', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function coordinates(Request $request, $id): JsonResponse
    {
        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');
        $filter = $request->query->get('filter', null);

        try {
            $coordinates = $this->deviceService->getCoordinatesByDevice(
                $id,
                $dateFrom,
                $dateTo,
                $filter,
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($coordinates);
    }

    /**
     * todo keep for pagination in future
     */
    #[Route('/devices/{id}/coordinates/paginated', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function coordinatesPaginated(Request $request, $id): JsonResponse
    {
        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');

        try {
            $query = $this->deviceService->getQueryCoordinatesByDevice($id, $dateFrom, $dateTo);
            $pagination = $this->paginator->paginate(
                $query, $request->query->get('page', 1), $request->query->get('limit', 10)
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/devices/vendors/', methods: ['GET'])]
    public function deviceModels(Request $request, EntityManager $em): JsonResponse
    {
        try {
            $vendors = $em->getRepository(DeviceVendor::class)->findAll();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($vendors, ['models', 'name'], 200, $this->getUser());
    }

    #[Route('/devices/{id}/restore', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function restoreDevice($id)
    {
        $device = $this->deviceService->getById($id, $this->getUser());

        $this->denyAccessUnlessGranted(Permission::DEVICE_EDIT, Device::class);
        $this->denyAccessUnlessGranted(null, $device->getTeam());

        try {
            $device = $this->deviceService->restore($device);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($device, Device::DEFAULT_DISPLAY_VALUES, 200, null, $this->getUser());
    }

    #[Route('/devices/installation/', methods: ['GET'])]
    public function getDeviceInstallation(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, null);

            $deviceInstallation = $this->deviceService->getDeviceInstallation(
                $request->query->get('deviceImei'),
                $request->query->get('vehicleRegNo'),
                $this->getUser()
            );
            if (is_iterable($deviceInstallation) && count($deviceInstallation) === 1) {
                $deviceInstallation = $deviceInstallation[0];
            } else {
                $deviceInstallation = null;
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(
            $deviceInstallation,
            array_merge(DeviceInstallation::DEFAULT_DISPLAY_VALUES, ['device']),
            Response::HTTP_OK,
            null,
            $this->getUser()
        );
    }

    #[Route('/device-notes/{id}/{type}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceNotes($id, $type)
    {
        $device = $this->deviceService->getById($id, $this->getUser());
        $this->denyAccessUnlessGranted(null, $device->getTeam());

        try {
            if ($this->getUser()->isInAdminTeam() || $this->getUser()->isInResellerTeam()) {
                $notesList = $this->noteService->list($device, $type);
            } elseif (
                $this->getUser()->isInClientTeam()
                && ($this->noteService->prepareNoteType($type) === Note::TYPE_CLIENT)) {
                $notesList = $this->noteService->list($device, $type);
            } else {
                throw new AccessDeniedException($this->translator->trans('general.access_denied'));
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($notesList);
    }

    #[Route('/devices-vehicles/upload', methods: ['POST'])]
    public function uploadFile(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::DEVICES_VEHICLES_IMPORT_DATA, Device::class);
        try {
            $fileData = $this->deviceService->parseImportFiles(
                array_merge_recursive(
                    $request->request->all(),
                    ['files' => $request->files]
                ),
                $this->getUser()
            );

            return $this->viewItem(
                [
                    'file' => $fileData['file'],
                    'data' => $fileData['data'],
                ]
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices-vehicles-drivers/upload', methods: ['POST'])]
    public function uploadFileVehiclesDrivers(
        Request $request,
        DevicesVehiclesDriverImport $devicesVehiclesDriverImport
    ): JsonResponse {
        $this->denyAccessUnlessGranted(Permission::DEVICES_VEHICLES_IMPORT_DATA, Device::class);

        try {
            $fileData = $this->deviceService->parseImportFilesVehiclesDrivers(
                array_merge_recursive(
                    $request->request->all(),
                    ['files' => $request->files]
                ),
                $devicesVehiclesDriverImport,
                $this->getUser()
            );

            return $this->viewItem(
                [
                    'file' => $fileData['file'],
                    'data' => $fileData['data'],
                ]
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices-vehicles/save', methods: ['POST'])]
    public function saveFile(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::DEVICES_VEHICLES_IMPORT_DATA, Device::class);
        try {
            $fileData = $this->deviceService->saveImportFiles($request->request->all(), $this->getUser());

            return $this->viewItem(
                [
                    'file' => $fileData['file'],
                    'data' => $fileData['data'],
                ]
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/device-status/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceStatusHistory(Request $request, $id, EntityHistoryService $entityHistoryService)
    {
        $device = $this->deviceService->getById($id, $this->getUser());
        $this->denyAccessUnlessGranted(null, $device->getTeam());
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $history = $entityHistoryService
                ->list(Device::class, $id, EntityHistoryTypes::DEVICE_STATUS);
            $pagination = $this->paginator->paginate($history, $page, $limit);
            $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
            $pagination->setItems($data);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/device/{id}/history/{field}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceFieldHistory(Request $request, $id, EntityHistoryService $entityHistoryService, string $field)
    {
        $device = $this->deviceService->getById($id, $this->getUser());
        $this->denyAccessUnlessGranted(null, $device->getTeam());
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $history = $entityHistoryService->listPagination(Device::class, $id, $field);
            $pagination = $this->paginator->paginate($history, $page, $limit);
            $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
            $pagination->setItems($data);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/device/{id}/history/deactivated', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceDeactivatedHistory(Request $request, $id, EntityHistoryService $entityHistoryService)
    {
        $device = $this->deviceService->getById($id, $this->getUser());
        $this->denyAccessUnlessGranted(null, $device->getTeam());
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $history = $entityHistoryService
                ->listPagination(Device::class, $id, EntityHistoryTypes::DEVICE_DEACTIVATED);
            $pagination = $this->paginator->paginate($history, $page, $limit);
            $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
            $pagination->setItems($data);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/device/{id}/history/unavailable', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceUnavailableHistory(Request $request, $id, EntityHistoryService $entityHistoryService)
    {
        $device = $this->deviceService->getById($id, $this->getUser());
        $this->denyAccessUnlessGranted(null, $device->getTeam());
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $history = $entityHistoryService
                ->listPagination(Device::class, $id, EntityHistoryTypes::DEVICE_UNAVAILABLE);
            $pagination = $this->paginator->paginate($history, $page, $limit);
            $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
            $pagination->setItems($data);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/device/{id}/history/contract', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceContractHistory(Request $request, $id, EntityHistoryService $entityHistoryService)
    {
        $device = $this->deviceService->getById($id, $this->getUser());
        $this->denyAccessUnlessGranted(null, $device->getTeam());
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        try {
            $history = $entityHistoryService
                ->listPagination(Device::class, $id, EntityHistoryTypes::DEVICE_CONTRACT_CHANGED);
            $pagination = $this->paginator->paginate($history, $page, $limit);
            $data = array_map(fn($item) => $item->toArray(), $pagination->getItems());
            $pagination->setItems($data);

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination);
    }

    #[Route('/devices/parsers', methods: ['GET'])]
    public function deviceParserTypes(Request $request): JsonResponse
    {
        try {
            $parserTypes = DeviceModel::getParserTypes();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($parserTypes);
    }

    #[Route('/devices/{id}/notes', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createClientNotes(Request $request, $id)
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            if ($device) {
                $this->denyAccessUnlessGranted(null, $device->getTeam());

                $this->deviceService->handleNotesFields($request->request->all(), $device, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($device);
    }

    #[Route('/devices/{id}/installation/{installationId}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateDeviceInstallation(Request $request, $id, $installationId): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            if ($device) {
                $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, $device);
                $device = $this->deviceService
                    ->updateDeviceInstallation($request->request->all(), $device, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($device);
    }

    #[Route('/devices/{id}/history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceHistory(Request $request, $id, EntityHistoryService $entityHistoryService): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            $this->denyAccessUnlessGranted(Permission::DEVICE_BY_ID, $device);
            $lastLoginList = $entityHistoryService
                ->list(Device::class, $device->getId(), $request->query->all('type'));
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($lastLoginList);
    }

    #[Route('/devices/bulk/contract', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function bulkContract(Request $request): JsonResponse
    {
        try {
            if ($this->getUser()->isInClientTeam()) {
                throw new AccessDeniedException();
            }
            $this->deviceService->bulkContract($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/devices/bulk/ownership', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function bulkOwnership(Request $request): JsonResponse
    {
        try {
            if ($this->getUser()->isInClientTeam()) {
                throw new AccessDeniedException();
            }
            $this->deviceService->bulkOwnership($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/devices/{id}/cameras', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceCameras(Request $request, int $id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());

            if (!$device) {
                throw new NotFoundHttpException($translator->trans('services.tracker.device_not_found'));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_BY_ID, $device);
            $streamData = $this->deviceService->getDeviceVideoData($device);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($streamData);
    }

    #[Route('/devices/{id}/replacements/{newId}', requirements: ['id' => '\d+', 'newId' => '\d+'], methods: ['POST'])]
    public function replaceDeviceToNewDevice(
        Request $request,
        int $id,
        int $newId,
        TranslatorInterface $translator
    ): JsonResponse {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());
            $deviceNew = $this->deviceService->getById($newId, $this->getUser());

            if (!$device || !$deviceNew) {
                throw new NotFoundHttpException($translator->trans('entities.device.not_found'));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, $device);
            $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, $deviceNew);
            $deviceReplacement = $this->deviceService
                ->replaceDeviceToNewDevice($request->request->all(), $device, $deviceNew, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceReplacement);
    }

    #[Route('/devices/replacements', methods: ['GET'])]
    public function replacements(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, null);
            $replacements = $this->deviceService->listReplacements($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($replacements);
    }

    #[Route('/devices/replacements/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteReplacement(int $id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, null);
            $deviceReplacement = $this->deviceService->getReplacementById($id);

            if (!$deviceReplacement) {
                throw new NotFoundHttpException($translator->trans('entities.device.replacement_not_found'));
            }

            $this->deviceService->removeDeviceReplacement($deviceReplacement);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/devices/replacements/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateReplacement(Request $request, int $id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_INSTALL_UNINSTALL, null);
            $deviceReplacement = $this->deviceService->getReplacementById($id);

            if (!$deviceReplacement) {
                throw new NotFoundHttpException($translator->trans('entities.device.replacement_not_found'));
            }

            $deviceReplacement = $this->deviceService
                ->updateDeviceReplacement($request->request->all(), $deviceReplacement);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceReplacement);
    }

    #[Route('/devices/{id}/wakeup', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function wakeup(int $id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());

            if (!$device) {
                throw new NotFoundHttpException($translator->trans('services.tracker.device_not_found'));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_BY_ID, $device);
            $result = $this->deviceService->wakeupDevice($device);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($result);
    }

    #[Route('/devices/{id}/tts', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendTTS(int $id, Request $request, TranslatorInterface $translator): JsonResponse
    {
        try {
            $text = $request->request->get('text');
            $device = $this->deviceService->getById($id, $this->getUser());

            if (!$device) {
                throw new NotFoundHttpException($translator->trans('services.tracker.device_not_found'));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_BY_ID, $device);
            $result = $this->deviceService->sendTTSToDevice($device, $text);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($result);
    }
}
