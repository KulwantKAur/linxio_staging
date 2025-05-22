<?php

namespace App\Controller;


use App\Entity\Permission;
use App\Entity\UserGroup;
use App\Service\UserGroup\UserGroupService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserGroupController extends BaseController
{
    private $userGroupService;

    public function __construct(UserGroupService $userGroupService)
    {
        $this->userGroupService = $userGroupService;
    }

    #[Route('/user-groups', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::USER_GROUP_NEW, UserGroup::class);
        try {
            $userGroup = $this->userGroupService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userGroup, UserGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/user-groups/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getUserGroupById(Request $request, $id): JsonResponse
    {
        try {
            $userGroup = $this->userGroupService->getById($id);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userGroup, UserGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/user-groups', methods: ['GET'])]
    public function userGroupsList(Request $request): JsonResponse
    {
        try {
            $userGroups = $this->userGroupService->userGroupsList($request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userGroups);
    }

    #[Route('/user-groups/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            $userGroup = $this->userGroupService->getById($id);
            if ($userGroup) {
                $this->denyAccessUnlessGranted(Permission::USER_GROUP_EDIT, $userGroup);
                $userGroup = $this->userGroupService->edit($request->request->all(), $this->getUser(), $userGroup);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userGroup, UserGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/user-groups/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $userGroup = $this->userGroupService->getById($id);
            if ($userGroup) {
                $this->denyAccessUnlessGranted(Permission::USER_GROUP_DELETE, $userGroup);
                $this->userGroupService->remove($userGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/user-groups/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            $userGroup = $this->userGroupService->getById($id);
            if ($userGroup) {
                $this->denyAccessUnlessGranted(Permission::USER_GROUP_ARCHIVE, $userGroup);
                $this->userGroupService->restore($userGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userGroup, UserGroup::FULL_DISPLAY_VALUES);
    }

    #[Route('/user-groups/{id}/archive', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function archive(Request $request, $id): JsonResponse
    {
        try {
            $userGroup = $this->userGroupService->getById($id);
            if ($userGroup) {
                $this->denyAccessUnlessGranted(Permission::USER_GROUP_ARCHIVE, $userGroup);
                $this->userGroupService->archive($userGroup, $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userGroup, UserGroup::FULL_DISPLAY_VALUES);
    }
}