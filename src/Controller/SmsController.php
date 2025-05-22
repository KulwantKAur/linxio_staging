<?php

namespace App\Controller;

use App\Service\Sms\SmsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sms')]
class SmsController extends BaseController
{
    private $smsService;
    private $logger;

    public function __construct(SmsService $smsService, LoggerInterface $logger)
    {
        $this->smsService = $smsService;
        $this->logger = $logger;
    }

    #[Route('/inbound/status-callback', name: 'sms_status_callback', methods: ['POST'])]
    public function inboundStatusAction(Request $request)
    {
        $params = $request->request->all();
        // @todo remove after verification
        $this->logger->info(json_encode($params));

        return $this->viewItem($this->smsService->update($params));
    }

    #[Route('/{id}', name: 'sms_view', methods: ['GET'])]
    public function getAction(Request $request, $id)
    {
        $sms = $this->smsService->get($id);

        return $this->viewItem($sms);
    }
}
