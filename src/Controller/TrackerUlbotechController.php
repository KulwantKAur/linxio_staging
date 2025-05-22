<?php

namespace App\Controller;

use App\Entity\DeviceVendor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tracker/ulbotech')]
class TrackerUlbotechController extends TrackerController
{
    private $trackerService;

    #[Route('/tcp', methods: ['POST'])]
    public function tcp(Request $request)
    {
        $this->trackerService = $this->trackerFactory->getInstance(DeviceVendor::VENDOR_ULBOTECH);
        $payload = $request->request->get('payload');
        $socketId = $request->headers->get('x-socket-id');

        try {
            $response = $this->trackerService->parseFromTcp($payload, $socketId);
        } catch (\Exception $ex) {
            return $this->viewJsonExceptionError($ex, $ex->getCode());
        }

        return $this->viewItem($response);
    }
}