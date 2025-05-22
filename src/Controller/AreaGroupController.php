<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\AreaGroup;
use App\Service\AreaGroup\AreaGroupService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AreaGroupController extends BaseController
{
    private AreaGroupService $areaGroupService;

    public function __construct(AreaGroupService $areaGroupService)
    {
        $this->areaGroupService = $areaGroupService;
    }

    #[Route('/area-groups', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::AREA_GROUP_NEW, AreaGroup::class);
        try {
            $areaGroup = $this->areaGroupService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroup, AreaGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/area-groups/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getAreaGroupById(Request $request, $id): JsonResponse
    {
        try {
            $areaGroup = $this->areaGroupService->getById($id, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroup, AreaGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/area-groups', methods: ['GET'])]
    public function areaGroupsList(Request $request): JsonResponse
    {
        try {
            $areaGroups = $this->areaGroupService->areaGroupsList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroups);
    }

    #[Route('/area-groups/dropdown', methods: ['GET'])]
    public function areaGroupsListDropdown(Request $request): JsonResponse
    {
        try {
            $areaGroups = $this->areaGroupService->areaGroupsList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroups);
    }

    #[Route('/area-groups/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $areaGroup = $this->areaGroupService->getById($id, $this->getUser());
            if ($areaGroup) {
                $this->denyAccessUnlessGranted(Permission::AREA_GROUP_EDIT, $areaGroup);
                $areaGroup = $this->areaGroupService->edit(
                    $request->request->all(),
                    $this->getUser(),
                    $areaGroup
                );
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroup, AreaGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/area-groups/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $areaGroup = $this->areaGroupService->getById($id, $this->getUser());
            if ($areaGroup) {
                $this->denyAccessUnlessGranted(Permission::AREA_GROUP_DELETE, $areaGroup);
                $this->areaGroupService->remove($areaGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/area-groups/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            $areaGroup = $this->areaGroupService->getById($id, $this->getUser());
            if ($areaGroup) {
                $this->denyAccessUnlessGranted(Permission::AREA_GROUP_ARCHIVE, $areaGroup);
                $this->areaGroupService->restore($areaGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroup, AreaGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/area-groups/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $areaGroup = $this->areaGroupService->getById($id, $this->getUser());
            if ($areaGroup) {
                $this->denyAccessUnlessGranted(Permission::AREA_GROUP_ARCHIVE, $areaGroup);
                $this->areaGroupService->archive($areaGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($areaGroup, AreaGroup::FULL_DISPLAY_VALUES);
    }
}