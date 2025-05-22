<?php

namespace App\Controller;


use App\Entity\Plan;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends BaseController
{

    public function __construct()
    {
    }

    #[Route('/plans', methods: ['GET'])]
    public function getPlans(Request $request, EntityManager $em): JsonResponse
    {
        if ($this->getUser()->getTeam()->isChevron()) {
            $plans = $em->getRepository(Plan::class)->findBy(['name' => [Plan::PLAN_ESSENTIALS, Plan::PLAN_PLUS]]);
            $plans = array_map(fn($plan) => $plan->toArray([], $this->getUser()->getTeam()), $plans);

            return $this->viewItem($plans);
        } else {
            $plans = $em->getRepository(Plan::class)->findAll();
        }

        return $this->viewItemsArray($plans);
    }
}