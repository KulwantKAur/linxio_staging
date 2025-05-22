<?php

namespace App\Controller;

use App\Entity\DeviceVendor;
use App\Exceptions\UnsupportedException;
use App\Service\Tracker\Factory\TrackerFactory;
use App\Service\Tracker\PivotelTrackerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tracker/pivotel')]
class TrackerPivotelController extends TrackerController
{
    /** @var PivotelTrackerService */
    private $trackerService;

    #[Route('/tcp', methods: ['POST'])]
    public function tcp(Request $request, LoggerInterface $logger)
    {
        /** @var TrackerFactory $trackerFactory */
        $this->trackerService = $this->trackerFactory->getInstance(DeviceVendor::VENDOR_PIVOTEL);
        $payload = $request->request->get('payload');
        $socketId = $request->headers->get('x-socket-id');

        try {
            $response = $this->trackerService->parseFromTcp($payload, $socketId);
        } catch (UnsupportedException $ex) {
            $logger->error($ex->getMessage(), ['payload' => $payload]);

            return $this->viewItem('');
        } catch (\Exception $ex) {
            $logger->error($ex->getMessage(), ['payload' => $payload]);

            return $this->viewJsonExceptionError($ex, $ex->getCode());
        }

        return $this->viewItem($response);
    }
}
