<?php

namespace App\Controller;

use App\Entity\VehicleOdometer;
use App\Service\Vehicle\VehicleOdometerService;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/vehicles')]
class VehicleOdometerController extends BaseController
{

    public function __construct(
        private readonly VehicleOdometerService $vehicleOdometerService,
        private readonly VehicleService $vehicleService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/{vehicleId}/odometer', requirements: ['vehicleId' => '\d+'], methods: ['POST'])]
    public function save(Request $request, $vehicleId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $vehicleOdometer = $this->vehicleOdometerService
                ->saveByVehicleAndDataAndUser($vehicle, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleOdometer, VehicleOdometer::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/{vehicleId}/odometer/{vehicleOdometerId}', requirements: ['vehicleId' => '\d+', 'vehicleOdometerId' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $vehicleId, $vehicleOdometerId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $vehicleOdometer = $this->vehicleOdometerService
                ->editByIdAndDataAndUser($vehicleOdometerId, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleOdometer, VehicleOdometer::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/{vehicleId}/odometer/{vehicleOdometerId}', requirements: ['vehicleId' => '\d+', 'vehicleOdometerId' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $vehicleId, $vehicleOdometerId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $vehicleOdometer = $this->vehicleOdometerService->deleteById($vehicleOdometerId);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{vehicleId}/odometer/history', requirements: ['vehicleId' => '\d+'], methods: ['GET'])]
    public function list(Request $request, $vehicleId)
    {
        try {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $vehicleOdometerList = $this->vehicleOdometerService->listByVehicle($vehicle, $page, $limit);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleOdometerList, VehicleOdometer::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/{vehicleId}/odometer', requirements: ['vehicleId' => '\d+'], methods: ['GET'])]
    public function current(Request $request, $vehicleId)
    {
        try {
            $occurredAt = $request->query->get('occurredAt', null);
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $odometerData = $this->vehicleOdometerService
                ->getOdometerDataForListByVehicleAndOccurredAt($vehicle, $occurredAt);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($odometerData, VehicleOdometer::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/{vehicleId}/odometer/{vehicleOdometerId}', requirements: ['vehicleId' => '\d+', 'vehicleOdometerId' => '\d+'], methods: ['GET'])]
    public function show(Request $request, $vehicleId, $vehicleOdometerId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $vehicleOdometer = $this->vehicleOdometerService->getById($vehicleOdometerId);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleOdometer, VehicleOdometer::DEFAULT_DISPLAY_VALUES);
    }
}
