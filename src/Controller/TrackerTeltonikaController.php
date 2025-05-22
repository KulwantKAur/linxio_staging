<?php

namespace App\Controller;

use App\Entity\DeviceVendor;
use App\Service\Tracker\Parser\Teltonika\Exception\InvalidImeiException;
use App\Service\Tracker\Parser\Teltonika\TcpDecoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tracker/teltonika')]
class TrackerTeltonikaController extends TrackerController
{
    private $trackerService;
    private $simulatorTrackerService;

    #[Route('/tcp', methods: ['POST'])]
    public function tcp(Request $request)
    {
        $this->trackerService = $this->trackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $payload = $request->request->get('payload');
        $socketId = $request->headers->get('x-socket-id');

        try {
            $imei = $this->trackerService->getImei($payload, $socketId);

            if ($this->trackerService->isImeiFromSimulator($imei)) {
                $this->simulatorTrackerService = $this->simulatorTrackerFactory
                    ->getInstance(DeviceVendor::VENDOR_TELTONIKA);
                $response = $this->simulatorTrackerService->parseFromTcp($payload, $socketId);
            } else {
                $response = $this->trackerService->parseFromTcp($payload, $socketId, $imei);
            }
        } catch (InvalidImeiException $ex) {
            return $this->viewJsonError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $ex) {
            return $this->viewJsonExceptionError($ex, $ex->getCode());
        }

        return $this->viewItem($response);
    }

    /**
     * todo implement this action
     */
    #[Route('/sms', methods: ['GET'])]
    public function sms(Request $request)
    {
        $this->trackerService = $this->trackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $payload = $request->query->get('payload');

        try {
            $this->trackerService->parseFromSms($payload);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('ok');
    }

    #[Route('/datetime-to-hex', methods: ['GET'])]
    public function dateTimeToHex(Request $request)
    {
        $dateTime = $request->query->get('dt');

        try {
            $dateTimeHex = TcpDecoder::convertDateTimeToHex($dateTime);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($dateTimeHex);
    }
}
