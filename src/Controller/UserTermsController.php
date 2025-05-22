<?php

namespace App\Controller;

use App\Entity\UserTermAcceptance;
use App\Service\UserTerms\UserTermsService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserTermsController extends BaseController
{

    public function __construct(private readonly UserTermsService $userTermsService)
    {
    }

    #[Route('/user-terms/{type}', methods: ['POST'])]
    public function accept(Request $request, $type): JsonResponse
    {
        try {
            $userTerms = $this->userTermsService->create($type, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userTerms);
    }

    #[Route('/user-terms', methods: ['GET'])]
    public function list(Request $request, EntityManager $em): JsonResponse
    {
        try {
            $userTerms = $em->getRepository(UserTermAcceptance::class)->findBy(['user' => $this->getUser()]);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($userTerms);
    }

    #[Route('/user-terms/{type}', methods: ['GET'])]
    public function getByType(Request $request, $type, EntityManager $em): JsonResponse
    {
        try {
            $userTermsAcceptance = $em->getRepository(UserTermAcceptance::class)
                ->findOneBy(['type' => $type, 'user' => $this->getUser()]);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($userTermsAcceptance);
    }
}