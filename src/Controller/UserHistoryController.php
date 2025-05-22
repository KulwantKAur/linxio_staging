<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\User;
use App\Enums\EntityHistoryTypes;
use App\Service\EntityHistory\EntityHistoryService;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserHistoryController extends BaseController
{
    private $userService;
    private $entityHistoryService;

    public function __construct(UserService $userService, EntityHistoryService $entityHistoryService)
    {
        $this->userService = $userService;
        $this->entityHistoryService = $entityHistoryService;
    }

    #[Route('/users/{id}/history/last-login', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function lastLoginList(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::USER_HISTORY_LAST_LOGIN, User::class);

        try {
            $user = $this->userService->get($id);
            $lastLoginList = $this->entityHistoryService->list(
                User::class, $user->getId(), EntityHistoryTypes::USER_LAST_LOGIN
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($lastLoginList);
    }

    #[Route('/users/{id}/history/updated', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function updatedList(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::USER_HISTORY_UPDATED, User::class);

        try {
            $user = $this->userService->get($id);
            $updatedList = $this->entityHistoryService->list(
                User::class, $user->getId(), EntityHistoryTypes::USER_UPDATED
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($updatedList);
    }
}