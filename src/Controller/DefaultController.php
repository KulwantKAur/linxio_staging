<?php

namespace App\Controller;

use App\Util\ApplicationVersionHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/health', methods: ['GET'])]
    public function indexAction(Request $request)
    {
        return new JsonResponse(['status' => 'Ok']);
    }

    #[Route('/version', methods: ['GET'])]
    public function versionAction(Request $request)
    {
        return new JsonResponse(ApplicationVersionHelper::getVersionFromTag());
    }

    private function getDatabaseConnectionStatus(EntityManager $em): bool
    {
        $em->getConnection()->connect();

        return $em->getConnection()->isConnected();
    }
}
