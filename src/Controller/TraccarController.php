<?php

namespace App\Controller;

use App\Service\Traccar\Model\TraccarData;
use App\Service\Traccar\TraccarService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/traccar')]
class TraccarController extends BaseController
{
    /** @var TraccarService */
    private $traccarService;

    /**
     * @param TraccarService $traccarService
     */
    public function __construct(TraccarService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    #[Route('/devices', methods: ['GET'])]
    public function devices(Request $request)
    {
        try {
            $params = $request->query->all();
            $devices = $this->traccarService->devices($params);
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($devices);
    }

    #[Route('/devices', methods: ['POST'])]
    public function createDevice(Request $request)
    {
        try {
            $params = $request->query->all();
            $device = $this->traccarService->createDevice($params);
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($device);
    }

    #[Route('/positions', methods: ['GET'])]
    public function positions(Request $request)
    {
        try {
            $positions = $this->traccarService->positions();
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($positions);
    }

    #[Route('/hook/positions', methods: ['POST'])]
    public function hookPositions(Request $request, LoggerInterface $logger)
    {
        try {
            $data = $request->request->all();
            $logger->notice(json_encode($data));
            $result = $this->traccarService->parseFromTcp($data, type: TraccarData::POSITION_SOURCE);
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($result);
    }

    #[Route('/events', methods: ['GET'])]
    public function events(Request $request)
    {
        $params = $request->query->all();

        try {
            $events = $this->traccarService->reportsEvents($params);
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($events);
    }

    #[Route('/hook/events', methods: ['POST'])]
    public function hookEvents(Request $request, LoggerInterface $logger)
    {
        try {
            $data = $request->request->all();
            $logger->notice(json_encode($data));
            $result = $this->traccarService->parseFromTcp($data, type: TraccarData::EVENT_SOURCE);
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($result);
    }

    #[Route('/socket', methods: ['GET'])]
    public function socket(Request $request)
    {
        try {
            $data = $this->traccarService->socket();
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($data);
    }
}
