<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Exceptions\ValidationException;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/tracker/simulator')]
class TrackerSimulatorController extends TrackerController
{
    private $simulatorTrackerService;
    protected $simulatorTrackerFactory;

    #[Route('/payload', methods: ['POST'])]
    public function payload(Request $request, EntityManager $em, TranslatorInterface $translator)
    {
        $payload = $request->request->get('payload');
        $deviceId = $request->request->get('deviceId');
        $createdAt = $request->request->get('createdAt');
        $vendorName = $request->request->get('vendor');
        $modelName = $request->request->get('model');
        $imei = $request->request->get('imei');
        $socketId = $request->headers->get('x-socket-id');

        try {
            $this->simulatorTrackerService = $this->simulatorTrackerFactory->getInstanceByVendorName($vendorName);
            $response = $this->simulatorTrackerService->getDataWithUpdatedPayloadByDT(
                $payload,
                $deviceId,
                $vendorName,
                $modelName,
                $createdAt,
                $imei,
                $socketId
            );
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($response);
    }

    #[Route('/generate-track', methods: ['POST'])]
    public function generateTrack(Request $request)
    {
        $this->simulatorTrackerService = $this->simulatorTrackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $imei = $request->request->get('imei');
        $dateFrom = BaseService::parseDateToUTC($request->request->get('dateFrom'))->getTimestamp();
        $dateTo = BaseService::parseDateToUTC($request->request->get('dateTo'))->getTimestamp();
        $trackName = $request->request->get('trackName', null);
        $location = $request->request->get('location', null);

        try {
            $simulatorTrack = $this->simulatorTrackerService->generateTrackPayloadsByImei(
                $imei, $dateFrom, $dateTo, $trackName, $location
            );
        } catch (\Exception $ex) {
            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $this->addFlash('success', 'Track #' . $simulatorTrack->getId() . ' has been generated and saved!');

        return $this->redirectToRoute('app_tracker_map', [
            'imei' => $imei,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    #[Route('/track-number', methods: ['GET'])]
    public function simulatorTrackNumber(Request $request)
    {
        $this->simulatorTrackerService = $this->simulatorTrackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $imei = $request->query->get('imei');

        try {
            $trackNumber = $this->simulatorTrackerService->getTrackNumber($imei);
        } catch (\Exception $ex) {
            if ($ex instanceof ValidationException) {
                return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($trackNumber);
    }

    #[Route('/generate-tracks', methods: ['GET'])]
    public function generateTracks(Request $request)
    {
        $this->simulatorTrackerService = $this->simulatorTrackerFactory->getInstance(DeviceVendor::VENDOR_TELTONIKA);
        $imei = $request->query->get('imei');
        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');

        try {
            $tracks = $this->simulatorTrackerService->generateTracksByImei(
                $imei, $dateFrom, $dateTo
            );
        } catch (\Exception $ex) {
            if ($ex instanceof ValidationException) {
                return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            return $this->viewError($ex->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($tracks);
    }
}
