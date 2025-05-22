<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Service\Device\DeviceService;
use App\Service\Device\DeviceStreamService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceStreamController extends BaseController
{
    public function __construct(
        private DeviceService       $deviceService,
        private DeviceStreamService $deviceStreamService,
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/devices/{id}/cameras', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deviceCameras(Request $request, int $id): JsonResponse
    {
        try {
            $device = $this->deviceService->getById($id, $this->getUser());

            if (!$device) {
                throw new NotFoundHttpException($this->translator->trans('services.tracker.device_not_found'));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_BY_ID, $device);
            $streamData = $this->deviceStreamService->getVideoData($device);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($streamData);
    }

    #[Route('/devices/cameras/events', methods: ['GET'])]
    public function deviceCamerasEvents(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(null, $this->getUser()->getTeam());
            $data = $request->query->all();
            $streamData = $this->deviceStreamService->getCameraEvents($data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($streamData);
    }

    #[Route('/devices/cameras/events/types', methods: ['GET'])]
    public function deviceCamerasEventTypes(Request $request): JsonResponse
    {
        try {
            $data = $this->deviceStreamService->getCameraEventTypes();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($data);
    }

    #[Route('/devices/cameras/history', methods: ['GET'])]
    public function getCamerasHistory(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(null, $this->getUser()->getTeam());
            $data = $request->query->all();
            $streamData = $this->deviceStreamService->getCamerasHistory($data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($streamData);
    }

    #[Route('/devices/cameras/types', methods: ['GET'])]
    public function deviceCamerasTypes(Request $request): JsonResponse
    {
        try {
            $data = $this->deviceStreamService->getCameraTypes();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($data);
    }
}
