<?php

namespace App\Controller;

use App\Entity\StreamaxIntegration;
use App\Service\Streamax\StreamaxService;
use App\Service\Tracker\TrackerService;
use App\Util\ExceptionHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/streamax')]
class StreamaxController extends BaseController
{
    /**
     * @param StreamaxService $streamaxService
     */
    public function __construct(
        private StreamaxService $streamaxService
    ) {
    }

    #[Route('/devices', methods: ['POST'])]
    public function createDevice(Request $request)
    {
        try {
            $params = $request->query->all();
            $device = $this->streamaxService->createDevice($params);
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($device);
    }

    #[Route('/inbound', methods: ['POST'])]
    public function inbound(Request $request, LoggerInterface $logger)
    {
        try {
            $data = $request->request->all();
            $this->streamaxService->setRequestLogId(rand(100000, 10000000));
            $logger->error(json_encode($data), [
                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
                'streamax_time_before_parseFromTcpInQueue' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
//            $this->streamaxService->verifyWebhookSignature($request);
//            $result = $this->streamaxService->parseFromTcp($data);
//            $this->streamaxService->parseFromTcpInQueue($data);
//            $this->streamaxService->handleFromTcpInQueue($data);
            $this->streamaxService->parseFromTcpInProxyQueue($data);
        } catch (\Exception $ex) {
            $exData = isset($data) ? json_encode($data) : null;
            $logger->error(ExceptionHelper::convertToJson($ex), ['data' => $exData]);

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(null);
    }

    #[Route('/inbound/direct', methods: ['POST'])]
    public function inboundDirect(Request $request, LoggerInterface $logger)
    {
        try {
            $data = $request->request->all();
            $this->streamaxService->setRequestLogId($request->headers->get(TrackerService::REQUEST_LOG_HEADER));
            $logger->error(json_encode($data), [
                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
                'streamax_direct_time_before_parseFromTcpDirect' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            $this->streamaxService->parseFromTcpDirect($data);
            $logger->error('streamax_direct_after_parseFromTcpDirect', [
                'streamax_log_id' => $this->streamaxService->getRequestLogId(),
                'streamax_direct_time_after_parseFromTcpDirect' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $ex) {
            $exData = isset($data) ? json_encode($data) : null;
            $logger->error(ExceptionHelper::convertToJson($ex), ['data' => $exData]);

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem(null);
    }

    #[Route('/integrations', methods: ['POST'])]
    public function createIntegration(Request $request)
    {
        try {
            $params = $request->request->all();
            $streamaxIntegration = $this->streamaxService->createIntegration($params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($streamaxIntegration, StreamaxIntegration::DEFAULT_FULL_VALUES);
    }

    #[Route('/integrations/{id}/devices/{deviceId}', requirements: ['id' => '\d+', 'deviceId' => '\d+'], methods: ['POST'])]
    public function setDeviceToIntegration(Request $request, int $id, int $deviceId)
    {
        try {
            $device = $this->streamaxService->setDeviceToIntegration($id, $deviceId);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($device);
    }

    #[Route('/integrations', methods: ['GET'])]
    public function integrationList(Request $request)
    {
        try {
            $params = $request->request->all();
            $streamaxIntegrations = $this->streamaxService->integrationList($params);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($streamaxIntegrations);
    }
}
