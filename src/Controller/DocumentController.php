<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Document;
use App\Entity\DocumentRecord;
use App\Entity\Permission;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Response\CsvResponse;
use App\Service\Vehicle\DocumentRecordService;
use App\Service\Vehicle\DocumentService;
use App\Service\Vehicle\VehicleServiceHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends BaseController
{
    private DocumentService $documentService;
    private EntityManager $em;

    public function __construct(DocumentService $documentService, EntityManager $em)
    {
        $this->documentService = $documentService;
        $this->em = $em;
    }

    #[Route('/documents', methods: ['POST'])]
    public function create(Request $request)
    {
        if ($request->request->get('vehicleId', null)) {
            $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_NEW, Document::class);
        }
        if ($request->request->get('driverId', null)) {
            $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_NEW, Document::class);
        }
        if ($request->request->get('assetId', null)) {
            $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_NEW, Document::class);
        }
        try {
            $document = $this->documentService->create(
                array_merge_recursive($request->request->all(), ['files' => $request->files]),
                $this->getUser()
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($document);
    }

    #[Route('/documents/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getById($id): JsonResponse
    {
        try {
            $document = $this->documentService->getById($id, $this->getUser());
            if (null === $document) {
                throw new NotFoundHttpException();
            }

            if ($document->getVehicle()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_LIST, Document::class);
            }
            if ($document->getDriver()) {
                $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_LIST, Document::class);
            }
            if ($document->getAsset()) {
                $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_LIST, Document::class);
            }

            $this->denyAccessUnlessGranted(null, $document->getTeam());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($document);
    }

    #[Route('/documents/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, $id)
    {
        try {
            $document = $this->documentService->getById($id, $this->getUser());
            if (null === $document) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(null, $document->getTeam());

            if ($document->isDeleted()) {
                throw new BadRequestHttpException('Document deleted');
            }

            if ($document->getVehicle()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_EDIT, Document::class);
            }
            if ($document->getDriver()) {
                $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_EDIT, Document::class);
            }
            if ($document->getAsset()) {
                $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_EDIT, Document::class);
            }

            $document = $this->documentService->edit(
                $document,
                array_merge_recursive($request->request->all(), ['files' => $request->files]),
                $this->getUser()
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($document);
    }

    #[Route('/vehicles/{id}/documents/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getVehicleDocuments(Request $request, $id, $type)
    {
        $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_LIST, Document::class);

        try {
            $vehicle = $this->em->getRepository(Vehicle::class)->getVehicleById($this->getUser(), $id);
            if (!$vehicle) {
                throw new EntityNotFoundException();
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $params = $request->query->all();

            switch ($type) {
                case 'csv':
                    $documents = $this->documentService->documentsList(
                        array_merge($params, ['vehicleId' => $vehicle ? $vehicle->getId() : null]),
                        Document::VEHICLE_DOCUMENT,
                        false
                    );
                    $results = $this->documentService->prepareExportData($documents, $params, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $documents = $this->documentService->documentsList(
                        array_merge($params, ['vehicleId' => $vehicle ? $vehicle->getId() : null]),
                        Document::VEHICLE_DOCUMENT
                    );
                    return $this->viewItem($documents);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/vehicles/documents/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getVehiclesDocuments(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_LIST, Document::class);
        $this->denyAccessUnlessGranted(null, $this->getUser()->getTeam());
        $params = $request->query->all();
        $vehicleIds = $this->em->getRepository(Vehicle::class)->getVehicleIdsByTeam($this->getUser());

        try {
            if ($this->getUser()->isInClientTeam()) {
                $data = ['vehicleId' => $vehicleIds ? array_keys($vehicleIds) : []];
                $params = VehicleServiceHelper::handleDriverVehicleParams($params, $this->em, $this->getUser(), false);

                if (isset($params['vehicleId'])) {
                    $data['vehicleId'] = array_intersect($data['vehicleId'], $params['vehicleId']);
                }
                if (isset($params['vehicleIds'])) {
                    $data['vehicleId'] = array_intersect($data['vehicleId'], $params['vehicleIds']);
                }
            } else {
                $data = [];
            }

            $data['fields'] = array_merge(Document::DEFAULT_LISTING_DISPLAY_VALUES, ['vehicle']);

            switch ($type) {
                case 'csv':
                    $documents = $this->documentService->documentsList(
                        array_merge($params, $data),
                        Document::VEHICLE_DOCUMENT,
                        false
                    );
                    $results = $this->documentService->prepareExportData($documents, $params, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $documents = $this->documentService->documentsList(
                        array_merge($params, $data),
                        Document::VEHICLE_DOCUMENT
                    );
                    return $this->viewItem($documents);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/drivers/documents/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getDriversDocuments(Request $request, $type)
    {
        $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_LIST, Document::class);
        $this->denyAccessUnlessGranted(null, $this->getUser()->getTeam());
        $params = $request->query->all();
        // @todo do smth for `isDualAccount`?
        $driversIds = $this->em->getRepository(User::class)->getDriversIdsByTeam($this->getUser()->getTeam());
        $data = [];

        try {
            if ($this->getUser()->isInClientTeam()) {
                $data = ['driverId' => $driversIds ? array_keys($driversIds) : []];

                if (isset($params['driverId'])) {
                    $data['driverId'] = array_intersect($data['driverId'], $params['driverId']);
                }
            }

            $data['fields'] = array_merge(Document::DEFAULT_LISTING_DISPLAY_VALUES, ['driver']);

            switch ($type) {
                case 'csv':
                    $documents = $this->documentService->documentsList(
                        array_merge($params, $data),
                        Document::DRIVER_DOCUMENT,
                        false
                    );
                    $results = $this->documentService->prepareExportData($documents, $params, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $documents = $this->documentService->documentsList(
                        array_merge($params, $data),
                        Document::DRIVER_DOCUMENT
                    );
                    return $this->viewItem($documents);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/assets/documents/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getAssetsDocuments(Request $request, $type)
    {
        $userTeam = $this->getUser()->getTeam();
        $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_LIST, Document::class);
        $this->denyAccessUnlessGranted(null, $userTeam);
        $params = $request->query->all();

        try {
            if ($this->getUser()->isInClientTeam()) {
                $data = ['teamId' => $userTeam->getId()];
            } else {
                $data = [];
            }

            $data['fields'] = array_merge(Document::DEFAULT_LISTING_DISPLAY_VALUES, ['asset']);

            switch ($type) {
                case 'csv':
                    $documents = $this->documentService
                        ->documentsList(array_merge($params, $data), Document::ASSET_DOCUMENT, false);
                    $results = $this->documentService->prepareExportData($documents, $params, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $documents = $this->documentService
                        ->documentsList(array_merge($params, $data), Document::ASSET_DOCUMENT);
                    return $this->viewItem($documents);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/assets/{id}/documents/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getAssetDocuments(Request $request, $id, $type)
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_LIST, Document::class);

        $asset = $this->em->getRepository(Asset::class)->find($id);

        try {
            $this->denyAccessUnlessGranted(null, $asset->getTeam());
            $params = $request->query->all();

            switch ($type) {
                case 'csv':
                    $documents = $this->documentService->documentsList(
                        array_merge($params, ['assetId' => $asset->getId()]),
                        Document::ASSET_DOCUMENT,
                        false
                    );
                    $results = $this->documentService->prepareExportData($documents, $params, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $documents = $this->documentService->documentsList(
                        array_merge($params, ['assetId' => $asset->getId()]),
                        Document::ASSET_DOCUMENT
                    );
                    return $this->viewItem($documents);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/drivers/{id}/documents/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getDriverDocuments(Request $request, $id, $type)
    {
        $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_LIST, Document::class);

        $driver = $this->em->getRepository(User::class)->find($id);

        try {
            $this->denyAccessUnlessGranted(null, $driver->getTeam());
            $params = $request->query->all();

            switch ($type) {
                case 'csv':
                    $documents = $this->documentService->documentsList(
                        array_merge($params, ['driverId' => $driver ? $driver->getId() : null]),
                        Document::DRIVER_DOCUMENT,
                        false
                    );
                    $results = $this->documentService->prepareExportData($documents, $params, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $documents = $this->documentService->documentsList(
                        array_merge($params, ['driverId' => $driver ? $driver->getId() : null]),
                        Document::DRIVER_DOCUMENT
                    );
                    return $this->viewItem($documents);
            }
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/documents/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete($id)
    {
        try {
            $document = $this->documentService->getById($id, $this->getUser());
            if (null === $document) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(null, $document->getTeam());

            if ($document->isDeleted()) {
                throw new BadRequestHttpException('Document deleted');
            }

            if ($document->getVehicle()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_DELETE, Document::class);
            }
            if ($document->getDriver()) {
                $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_DELETE, Document::class);
            }
            if ($document->getAsset()) {
                $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_DELETE, Document::class);
            }

            $this->documentService->delete($document, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem(null, [], 201);
    }

    #[Route('/dashboard/documents/stat', methods: ['GET'])]
    public function getDocumentsDashboardStatistic(Request $request): JsonResponse
    {
        try {
            $data = $this->documentService->getExpiredAndDueSoonStat($this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }

    #[Route('/documents/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id)
    {
        try {
            $document = $this->documentService->getById($id, $this->getUser());
            if (null === $document) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(null, $document->getTeam());
            if ($document->getVehicle()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_ARCHIVE, Document::class);
            }
            if ($document->getDriver()) {
                $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_ARCHIVE, Document::class);
            }
            if ($document->getAsset()) {
                $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_ARCHIVE, Document::class);
            }

            $document = $this->documentService->restore($document, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($document);
    }

    #[Route('/documents/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id)
    {
        try {
            $document = $this->documentService->getById($id, $this->getUser());
            if (null === $document) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(null, $document->getTeam());
            if ($document->getVehicle()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_ARCHIVE, Document::class);
            }
            if ($document->getDriver()) {
                $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_ARCHIVE, Document::class);
            }
            if ($document->getAsset()) {
                $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_ARCHIVE, Document::class);
            }

            $document = $this->documentService->archive($document, $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($document);
    }

    #[Route('/documents/{id}/record/{recordId}', requirements: ['id' => '\d+', 'recordId' => '\d+'], methods: ['POST'])]
    public function editRecord(Request $request, $id, $recordId, DocumentRecordService $documentRecordService)
    {
        try {
            $document = $this->documentService->getById($id, $this->getUser());
            if (null === $document) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(null, $document->getTeam());

            if ($document->isDeleted()) {
                throw new BadRequestHttpException('Document deleted');
            }

            if ($document->getVehicle()) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_EDIT, Document::class);
            }
            if ($document->getDriver()) {
                $this->denyAccessUnlessGranted(Permission::DRIVER_DOCUMENT_EDIT, Document::class);
            }
            if ($document->getAsset()) {
                $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_EDIT, Document::class);
            }

            $record = $this->em->getRepository(DocumentRecord::class)
                ->findOneBy(['document' => $document, 'id' => $recordId]);

            $record = $documentRecordService->edit(
                $record,
                array_merge_recursive($request->request->all(), ['files' => $request->files]),
                $this->getUser()
            );
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($record);
    }

    #[Route('/documents/{id}/duplicate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function duplicateDocument(Request $request, $id)
    {
        try {
            $assets = $request->request->all('assets') ?? [];
            $vehicles = $request->request->all('vehicles') ?? [];
            $depots = $request->request->all('depots') ?? [];
            $groups = $request->request->all('groups') ?? [];
            $document = $this->documentService->getById($id, $this->getUser());

            if ($document) {
                if ($document->isVehicleDocument()) {
                    $this->denyAccessUnlessGranted(Permission::VEHICLE_DOCUMENT_NEW, Document::class);
                } else {
                    $this->denyAccessUnlessGranted(Permission::ASSET_DOCUMENT_NEW, Document::class);
                }

                $this->denyAccessUnlessGranted(null, $document->getTeam());
                $document = $this->documentService
                    ->duplicate($document, $this->getUser(), $vehicles, $depots, $groups, $assets);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($document);
    }
}
