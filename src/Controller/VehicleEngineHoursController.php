<?php

namespace App\Controller;

use App\Entity\VehicleEngineHours;
use App\Service\Vehicle\VehicleEngineHoursService;
use App\Service\Device\DeviceService;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/vehicles')]
class VehicleEngineHoursController extends BaseController
{
    private $deviceService;
    private $vehicleEngineHoursService;
    private $vehicleService;
    private $translator;


    public function __construct(
        DeviceService $deviceService,
        VehicleEngineHoursService $vehicleEngineHoursService,
        VehicleService $vehicleService,
        TranslatorInterface $translator
    ) {
        $this->deviceService = $deviceService;
        $this->vehicleEngineHoursService = $vehicleEngineHoursService;
        $this->vehicleService = $vehicleService;
        $this->translator = $translator;
    }

    #[Route('/{vehicleId}/engine-hours', requirements: ['vehicleId' => '\d+'], methods: ['POST'])]
    public function save(Request $request, $vehicleId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());

            $vehicleEngineHours = $this->vehicleEngineHoursService
                ->saveByVehicleAndDataAndUser($vehicle, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($vehicleEngineHours);
    }

    #[Route('/{vehicleId}/engine-hours', requirements: ['vehicleId' => '\d+'], methods: ['GET'])]
    public function list(Request $request, $vehicleId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());

            $vehicleEngineHoursList = $this->vehicleEngineHoursService->listByVehicle($vehicle);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItemsArray($vehicleEngineHoursList);
    }

    #[Route('/{vehicleId}/engine-hours/{engineHourId}', requirements: ['vehicleId' => '\d+', 'engineHourId' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $vehicleId, $engineHourId)
    {
        try {
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());
            $this->vehicleEngineHoursService->delete($engineHourId, $vehicle);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{vehicleId}/engine-hours/current', requirements: ['vehicleId' => '\d+'], methods: ['GET'])]
    public function current(Request $request, $vehicleId)
    {
        try {
            $occurredAt = $request->query->get('occurredAt', null);
            $vehicle = $this->vehicleService->getById($vehicleId, $this->getUser());

            if (!$vehicle) {
                throw new EntityNotFoundException($this->translator->trans('entities.vehicle.not_found'));
            }

            $this->denyAccessUnlessGranted(null, $vehicle->getTeam());

            $engineHours = $this->vehicleEngineHoursService->getEngineHoursDataByVehicleAndOccurredAt($vehicle, $occurredAt);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($engineHours, VehicleEngineHours::DEFAULT_DISPLAY_VALUES);
    }
}
