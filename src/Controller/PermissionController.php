<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Fixtures\PlansPermissions\InitPlansPermissionsFixture;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PermissionController extends BaseController
{
    #[Route('/permissions', methods: ['GET'])]
    public function getPermissions(Request $request, EntityManager $em): JsonResponse
    {
        $permissions = $em->getRepository(Permission::class)->findAll();

        return $this->viewItemsArray($permissions);
    }

    #[Route('/permissions/current-plan', methods: ['GET'])]
    public function getCurrentPlanPermissions()
    {
        $user = $this->getUser();
        $permissions = [];
        if ($user->isInClientTeam()) {
            $planName = $this->getUser()->getClient()->getPlan()->getName();

            $exclude = $user->getExcludePermissions();
            $permissions = InitPlansPermissionsFixture::CLIENT_PLAN_MAX_PERMISSIONS[$planName] ?? [];

            if ($exclude) {
                $permissions = array_filter($permissions, function ($permission) use ($exclude) {
                    return !in_array($permission, $exclude);
                });
            }
        }

        return $this->viewItemsArray($permissions);
    }
}