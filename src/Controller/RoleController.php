<?php

namespace App\Controller;


use App\Entity\Role;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends BaseController
{
    #[Route('/roles', methods: ['GET'])]
    public function getRoles(Request $request, EntityManager $em): JsonResponse
    {
        $plans = $em->getRepository(Role::class)->findAll();

        return $this->viewItemsArray($plans);
    }
}