<?php

namespace App\Controller;

use App\Entity\BillingSetting;
use App\Entity\Client;
use App\Entity\Team;
use App\Response\CsvResponse;
use App\Service\Billing\BillingPlanService;
use App\Service\Billing\BillingService;
use App\Service\Payment\PaymentService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BillingController extends BaseController
{
    private PaginatorInterface $paginator;
    private BillingService $billingService;
    private EntityManager $em;
    private PaymentService $paymentService;
    private BillingPlanService $billingPlanService;

    public function __construct(
        PaginatorInterface $paginator,
        BillingService $billingService,
        EntityManager $em,
        PaymentService $paymentService,
        BillingPlanService $billingPlanService
    ) {
        $this->paginator = $paginator;
        $this->billingService = $billingService;
        $this->em = $em;
        $this->paymentService = $paymentService;
        $this->billingPlanService = $billingPlanService;
    }

    #[Route('/billing/list/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function clientList(Request $request, $type)
    {
        $params = $request->query->all();
        try {
            switch ($type) {
                case 'json':
                    $pagination = $this->billingService->getClientsBillingForPeriod($params, $this->getUser());
                    $totalData = $this->billingService->getTotalData($pagination->getItems());
                    $pagination->setItems($this->billingService->updateBillingItemsFormat($pagination->getItems()));

                    return $this->viewItem($pagination, [], 200, ['total' => $totalData]);
                case 'csv':
                    $clients = $this->billingService
                        ->getClientsBillingInfo($request->query->all())->getItems();

                    return new CsvResponse($clients);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/payments/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function clientsPaymentList(Request $request, $type, ReportService $reportService)
    {
        try {
            return $reportService
                ->init(ReportMapper::TYPE_BILLING)
                ->getReport($type, $request->query->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/summary', methods: ['GET'])]
    public function summary()
    {
        /** @var Client $client */
        $client = $this->getUser()->getClient();
        try {
            $summary = [
                'billingPlan' => null,
                'accountStatus' => $this->billingService->getAcountStatus($client),
                'nextBillingDate' => Carbon::parse('first day of next month')->toDateString(),
                'subscriptionPrice' => $this->billingPlanService->getSubscriptionCost($client->getTeamId()),
                'defaultPaymentMethod' => $this->paymentService->getDefaultPaymentMethod($client),
            ];
            $clientDetails = $client->toArray(['plan']);
            if (isset($clientDetails['plan'])) {
                $summary['billingPlan'] = $clientDetails['plan'];
            }

            return $this->viewItem($summary);
        } catch (\Exception $exception) {
            return $this->viewException($exception);
        }
    }

    #[Route('/billing/{teamId}', requirements: ['teamId' => '\d+'], methods: ['GET'])]
    public function clientMomentBilling(Request $request, $teamId)
    {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            if ($team) {
                $this->denyAccessUnlessGranted(null, $team);
                $query = $this->em->getRepository(Client::class)->getClientMomentBillingInfo($teamId);
            }
            return $this->viewItem($query->execute()->fetchAssociative());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/chart/gross', methods: ['GET'])]
    public function grossChart(Request $request)
    {
        try {
            $data = $this->billingService->grossChart($request->query->all(), $this->getUser());

            return $this->viewItem($data);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/chart/top-clients', methods: ['GET'])]
    public function topClientsChart(Request $request)
    {
        try {
            $data = $this->billingService->topClientsChart($request->query->all(), $this->getUser());

            return $this->viewItem($data);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/billing/{teamId}/settings', requirements: ['teamId' => '\d+'], methods: ['GET'])]
    public function getBillingSetting(
        Request $request,
        $teamId,
        EntityManager $em,
        BillingService $billingService
    ): JsonResponse {
        try {
            $team = $this->em->getRepository(Team::class)->find($teamId);
            if ($team) {
                $this->denyAccessUnlessGranted(null, $team);
            }

            if ($this->getUser()->getTeam()->isClientTeam()) {
                $settings = $billingService->getBillingSettingByTeam($team);
            } else {
                $settings = $em->getRepository(BillingSetting::class)->findOneBy(['team' => $this->getUser()->getTeam()]);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($settings);
    }

    #[Route('/billing/{teamId}/settings', requirements: ['teamId' => '\d+'], methods: ['POST'])]
    public function setBillingSetting(
        Request $request,
        $teamId,
        BillingService $billingService,
        EntityManager $em
    ): JsonResponse {
        try {
            if ($this->getUser()->getTeam()->isClientTeam() || !$this->getUser()->isControlAdmin()) {
                throw new AccessDeniedException();
            }

            $team = $this->em->getRepository(Team::class)->find($teamId);
            if ($team) {
                $this->denyAccessUnlessGranted(null, $team);
            }

            $data = $request->request->all();
            /** @var BillingSetting $adminInfo */
            $billingSetting = $em->getRepository(BillingSetting::class)->findOneBy(['team' => $this->getUser()->getTeam()]);
            if ($billingSetting) {
                $billingSetting->setAttributes($data);
                $this->em->flush();
            } else {
                $billingSetting = $billingService->createBillingSetting($data, $team);
            }

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($billingSetting);
    }
}