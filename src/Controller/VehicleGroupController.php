<?php

namespace App\Controller;


use App\Entity\Permission;
use App\Entity\VehicleGroup;
use App\Service\VehicleGroup\VehicleGroupService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VehicleGroupController extends BaseController
{
    private $vehicleGroupService;

    public function __construct(VehicleGroupService $vehicleGroupService)
    {
        $this->vehicleGroupService = $vehicleGroupService;
    }

    #[Route('/vehicle-groups', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::VEHICLE_GROUP_NEW, VehicleGroup::class);
        try {
            $vehicleGroup = $this->vehicleGroupService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleGroup, VehicleGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/vehicle-groups/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getVehicleGroupById(Request $request, $id): JsonResponse
    {
        try {
            $vehicleGroup = $this->vehicleGroupService->getById($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleGroup, VehicleGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/vehicle-groups', methods: ['GET'])]
    public function vehiclesGroupsList(Request $request): JsonResponse
    {
        try {
            $vehiclesGroups = $this->vehicleGroupService->vehicleGroupsList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehiclesGroups);
    }

    #[Route('/vehicle-groups/dropdown', methods: ['GET'])]
    public function vehiclesGroupsListDropdown(Request $request): JsonResponse
    {
        try {
            $vehiclesGroups = $this->vehicleGroupService->vehicleGroupsList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehiclesGroups);
    }

    #[Route('/vehicle-groups/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $vehicleGroup = $this->vehicleGroupService->getById($id, $this->getUser());
            if ($vehicleGroup) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_GROUP_EDIT, $vehicleGroup);
                $vehicleGroup = $this->vehicleGroupService->edit(
                    $request->request->all(),
                    $this->getUser(),
                    $vehicleGroup
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleGroup, VehicleGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/vehicle-groups/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $vehicleGroup = $this->vehicleGroupService->getById($id, $this->getUser());
            if ($vehicleGroup) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_GROUP_DELETE, $vehicleGroup);
                $this->vehicleGroupService->remove($vehicleGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/vehicle-groups/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            $vehicleGroup = $this->vehicleGroupService->getById($id, $this->getUser());
            if ($vehicleGroup) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_GROUP_ARCHIVE, $vehicleGroup);
                $this->vehicleGroupService->restore($vehicleGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleGroup, VehicleGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/vehicle-groups/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $vehicleGroup = $this->vehicleGroupService->getById($id, $this->getUser());
            if ($vehicleGroup) {
                $this->denyAccessUnlessGranted(Permission::VEHICLE_GROUP_ARCHIVE, $vehicleGroup);
                $this->vehicleGroupService->archive($vehicleGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleGroup, VehicleGroup::FULL_DISPLAY_VALUES);
    }
}