<?php

namespace App\Controller;

use App\Entity\BillingPlan;
use App\Entity\Permission;
use App\Mailer\MailSender;
use App\Service\Billing\BillingPlanService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BillingPlanController extends BaseController
{
    private BillingPlanService $billingPlanService;
    private EntityManager $em;

    public function __construct(BillingPlanService $billingPlanService, EntityManager $em)
    {
        $this->billingPlanService = $billingPlanService;
        $this->em = $em;
    }

    #[Route('/billing/plan/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::BILLING_PLAN_EDIT, BillingPlan::class);
        try {
            $billingPlan = $this->billingPlanService->getBillingPlanById($id);

            if ($billingPlan) {
                $billingPlan = $this->billingPlanService
                    ->edit($request->request->all(), $this->getUser(), $billingPlan);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($billingPlan);
    }

    #[Route('/billing/plan/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getById(Request $request, $id): JsonResponse
    {
        try {
            $billingPlan = $this->billingPlanService->getBillingPlanById($id);
            $this->denyAccessUnlessGranted(null, $billingPlan->getTeam());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($billingPlan);
    }

    #[Route('/billing/change-plan-ntf', methods: ['POST'])]
    public function changePlanNtf(Request $request, MailSender $mailSender): JsonResponse
    {
        try {
            if (!$this->getUser()->isInClientTeam()) {
                return $this->viewItem(false);
            }

            $mailSender->sendChangePlanNtf($this->getUser());

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(true);
    }
}