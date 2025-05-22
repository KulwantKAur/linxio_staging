<?php

namespace App\Controller;

use App\Entity\FuelType\FuelMapping;
use App\Entity\FuelType\FuelType;
use App\Entity\Permission;
use App\Service\FuelType\FuelMappingService;
use App\Service\FuelType\FuelTypeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FuelMappingController extends BaseController
{
    private $fuelMappingService;
    private $fuelTypeService;

    public function __construct(FuelMappingService $fuelMappingService, FuelTypeService $fuelTypeService)
    {
        $this->fuelMappingService = $fuelMappingService;
        $this->fuelTypeService = $fuelTypeService;
    }

    #[Route('/fuel-mapping', methods: ['GET'])]
    public function getFuelMappingList(Request $request)
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_MAPPING_LIST, FuelMapping::class);
        try {
            $fuelMapping = $this->fuelMappingService->fuelMappingList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelMapping, FuelMapping::DISPLAYED_VALUES);
    }

    #[Route('/fuel-mapping', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::FUEL_MAPPING_NEW, FuelMapping::class);
        try {
            $fuelMapping = $this->fuelMappingService->create(
                array_merge(
                    $request->request->all(),
                    ['createdBy' => $this->getUser()]
                ),
                $this->getUser()
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelMapping, FuelMapping::DISPLAYED_VALUES);
    }

    #[Route('/fuel-mapping/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $fuelMapping = $this->fuelMappingService->getById($id, $this->getUser());
            if ($fuelMapping) {
                $this->denyAccessUnlessGranted(Permission::FUEL_MAPPING_EDIT, $fuelMapping);
                $fuelMapping = $this->fuelMappingService->edit(
                    array_merge(
                        $request->request->all(),
                        ['updatedBy' => $this->getUser()]
                    ),
                    $this->getUser(),
                    $fuelMapping
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelMapping, FuelMapping::DISPLAYED_VALUES);
    }

    #[Route('/fuel-mapping/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $fuelMapping = $this->fuelMappingService->getById($id, $this->getUser());
            if ($fuelMapping) {
                $this->denyAccessUnlessGranted(Permission::FUEL_MAPPING_DELETE, $fuelMapping);
                $this->fuelMappingService->remove($fuelMapping, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/fuel-mapping/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getFuelMappingById(Request $request, $id): JsonResponse
    {
        try {
            $fuelMapping = $this->fuelMappingService->getById($id, $this->getUser());
            if ($fuelMapping) {
                $this->denyAccessUnlessGranted(Permission::FUEL_TYPES_LIST, $fuelMapping);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($fuelMapping, FuelMapping::DISPLAYED_VALUES);
    }

    #[Route('/fuel-types', methods: ['GET'])]
    public function getFuelTypeList(Request $request)
    {
        try {
            $fuelTypes = $this->fuelTypeService->fuelTypeList($request->query->all(), $this->getUser(), false);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($fuelTypes, FuelType::DISPLAYED_VALUES, 200, $this->getUser());
    }
}
