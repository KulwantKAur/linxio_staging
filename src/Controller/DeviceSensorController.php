<?php

namespace App\Controller;

use App\Entity\DeviceSensor;
use App\Entity\Permission;
use App\Entity\Sensor;
use App\Response\CsvResponse;
use App\Service\Device\DeviceSensorService;
use App\Service\Device\DeviceService;
use App\Service\Report\ReportMapper;
use App\Service\Report\ReportService;
use App\Service\Sensor\SensorService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceSensorController extends BaseController
{
    private $deviceService;
    private $deviceSensorService;
    private $sensorService;
    private $paginator;
    private $translator;
    private ReportService $reportService;

    public function __construct(
        DeviceService $deviceService,
        DeviceSensorService $deviceSensorService,
        SensorService $sensorService,
        PaginatorInterface $paginator,
        TranslatorInterface $translator,
        ReportService $reportService
    ) {
        $this->deviceService = $deviceService;
        $this->deviceSensorService = $deviceSensorService;
        $this->sensorService = $sensorService;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->reportService = $reportService;
    }

    #[Route('/devices/sensors/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function view($id): JsonResponse
    {
        try {
            $deviceSensor = $this->deviceSensorService->getDeviceSensorById($id);

            if (!$deviceSensor) {
                throw new NotFoundHttpException($this->translator->trans('entities.device_sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $deviceSensor->getDevice()->getTeam());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceSensor);
    }

    #[Route('/devices/sensors/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function list(Request $request, string $type)
    {
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $deviceSensorList = $this->deviceSensorService->listDeviceSensor($this->getUser(), $params);

                    return $this->viewItem($deviceSensorList);
                case 'csv':
                    $deviceSensorList = $this->deviceSensorService
                        ->getDeviceSensorListExportData($params, $this->getUser());

                    return new CsvResponse($deviceSensorList);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceSensorList ?? null);
    }

    #[Route('/devices/{id}/sensors', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createFromDevice(Request $request, $id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_CREATE, DeviceSensor::class);
            $data = $request->request->all();
            $device = $this->deviceService->getById($id, $this->getUser());

            if (!$device) {
                throw new NotFoundHttpException($this->translator->trans('services.tracker.device_not_found'));
            }

            $this->denyAccessUnlessGranted(null, $device->getTeam());
            $deviceSensor = $this->deviceSensorService->createDeviceSensor($device, $data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceSensor ?? null);
    }

    #[Route('/devices/sensors/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, DeviceSensor::class);
            $data = $request->request->all();
            $deviceSensor = $this->deviceSensorService->getDeviceSensorById($id);

            if (!$deviceSensor) {
                throw new NotFoundHttpException($this->translator->trans('entities.device_sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $deviceSensor = $this->deviceSensorService->updateDeviceSensor($deviceSensor, $data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceSensor);
    }

    #[Route('/devices/sensors/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_DELETE, DeviceSensor::class);
            $deviceSensor = $this->deviceSensorService->getDeviceSensorById($id);

            if (!$deviceSensor) {
                throw new NotFoundHttpException($this->translator->trans('entities.device_sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $deviceSensor->getDevice()->getTeam());
            $deviceSensor = $this->deviceSensorService->deleteDeviceSensor($deviceSensor, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceSensor, array_merge(DeviceSensor::DEFAULT_DISPLAY_VALUES, ['status']));
    }

    #[Route('/devices/{id}/sensors/history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDeviceSensorsHistoryByDevice(
        Request $request,
        $id
    ): JsonResponse {
        try {
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            $device = $this->deviceService->getById($id, $this->getUser());

            if (!$device) {
                throw new NotFoundHttpException($this->translator->trans('services.tracker.device_not_found'));
            }

            $this->denyAccessUnlessGranted(null, $device->getTeam());
            $sensorsHistory = $this->deviceSensorService
                ->getSensorsHistoryByDeviceAndRange($device, $startDate, $endDate);
            $pagination = $this->paginator->paginate($sensorsHistory, $page, ($limit == 0) ? 1 : $limit);

            if ($limit == 0) {
                $pagination = $this->paginator->paginate($sensorsHistory, 1, $pagination->getTotalItemCount());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($pagination ?? []);
    }

    #[Route('/devices/sensors/{id}/history/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function getDeviceSensorHistoryBySensor(Request $request, int $id, string $type)
    {
        try {
            $deviceSensor = $this->deviceSensorService->getDeviceSensorById($id);

            if (!$deviceSensor) {
                throw new NotFoundHttpException($this->translator->trans('entities.device_sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(null, $deviceSensor->getDevice()->getTeam());

            switch ($type) {
                case 'csv':
                    $results = $this->deviceSensorService->getHistoryCSV($request, $deviceSensor, $this->getUser());

                    return new CsvResponse($results);
                case 'json':
                default:
                    $pagination = $this->deviceSensorService->getHistoryJSON($request, $deviceSensor);

                    return $this->viewItem($pagination);
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices/sensors/report/temp-and-humidity/vehicles/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function reportTempAndHumidityByVehicles(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_TEMPERATURE_BY_VEHICLE)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices/sensors/report/temp-and-humidity/vehicles/chart', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function reportTempAndHumidityByVehiclesChart(Request $request)
    {
        try {
            $params = $request->request->all();
            $data = $this->sensorService->getTempAndHumidityByVehiclesData($params, $this->getUser());

            return $this->viewItem($data);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices/sensors/report/temp-and-humidity', methods: ['GET'])]
    public function getSensorTempAndHumidityVehicleListForReport(
        Request $request,
        DeviceSensorService $deviceSensorService
    ) {
        try {
            $user = $this->getUser();
            $sensorParams = $deviceSensorService
                ->getParamsForTempAndHumiditySensorListReport($request->query->all(), $user);

            $sensors = $this->sensorService->listSensor($sensorParams, $user);

            return $this->viewItem($sensors, Sensor::DEFAULT_DISPLAY_VALUES);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices/sensors/report/temp-and-humidity/{type}', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function reportTempAndHumidityBySensors(Request $request, string $type)
    {
        try {
            return $this->reportService
                ->init(ReportMapper::TYPE_TEMPERATURE_BY_SENSOR)
                ->getReport($type, $request->request->all(), $this->getUser());
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/devices/sensors/report/temp-and-humidity/sensors/chart', requirements: ['type' => 'json|csv|pdf'], defaults: ['type' => 'json'], methods: ['POST'])]
    public function reportTempAndHumidityBySensorsChart(Request $request)
    {
        try {
            $params = $request->request->all();
            $data = $this->sensorService->getTempAndHumidityBySensorsData($params, $this->getUser());

            return $this->viewItem($data);
        } catch (\Exception $e) {
            return $this->viewException($e, Response::HTTP_BAD_REQUEST);
        }
    }
}
