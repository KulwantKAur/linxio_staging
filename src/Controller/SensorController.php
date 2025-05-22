<?php

namespace App\Controller;

use App\Entity\DeviceSensor;
use App\Entity\Permission;
use App\Entity\Sensor;
use App\Entity\Tracker\TrackerIOType;
use App\Response\CsvResponse;
use App\Service\Sensor\SensorService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SensorController extends BaseController
{
    private $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    #[Route('/sensors/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function view($id, Request $request, TranslatorInterface $translator): JsonResponse
    {
        try {
            $sensor = $this->sensorService->getSensorById($id);
            $params = $request->query->all();

            if (!$sensor) {
                throw new NotFoundHttpException($translator->trans('entities.device_sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensor, array_merge(Sensor::DEFAULT_DISPLAY_VALUES, $params['fields'] ?? []));
    }

    #[Route('/sensors/{type}', requirements: ['type' => 'json|csv'], defaults: ['type' => 'json'], methods: ['GET'])]
    public function list(Request $request, string $type)
    {
        try {
            $params = $request->query->all();

            switch ($type) {
                case 'json':
                    $sensorList = $this->sensorService->listSensor($params, $this->getUser());

                    return $this->viewItem($sensorList);
                case 'csv':
                    $sensors = $this->sensorService->getSensorListExportData($params, $this->getUser());

                    return new CsvResponse($sensors);
            }

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensorList ?? null);
    }

    #[Route('/sensors', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_CREATE, DeviceSensor::class);
            $data = $request->request->all();
            $sensor = $this->sensorService->createSensor($data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensor ?? null);
    }

    #[Route('/sensors/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function update(Request $request, $id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, DeviceSensor::class);
            $data = $request->request->all();
            $sensor = $this->sensorService->getSensorById($id);

            if (!$sensor) {
                throw new NotFoundHttpException($translator->trans('entities.sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, $sensor);
            $sensor = $this->sensorService->updateSensorAndDependencies($sensor, $data, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensor);
    }

    #[Route('/sensors/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete($id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_DELETE, DeviceSensor::class);
            $sensor = $this->sensorService->getSensorById($id);

            if (!$sensor) {
                throw new NotFoundHttpException($translator->trans('entities.sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, $sensor);
            $sensor = $this->sensorService->deleteSensorAndDependencies($sensor, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensor, Sensor::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/sensors/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore($id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_DELETE, DeviceSensor::class);
            $sensor = $this->sensorService->getSensorById($id);

            if (!$sensor) {
                throw new NotFoundHttpException($translator->trans('entities.sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, $sensor);
            $sensor = $this->sensorService->restoreSensor($sensor, $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($sensor, Sensor::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/sensors/types', methods: ['GET'])]
    public function getSensorTypes(Request $request): JsonResponse
    {
        $sensorTypes = $this->sensorService->getAvailableDeviceSensorTypes();

        return $this->viewItem($sensorTypes);
    }

    #[Route('/sensors/{id}/install', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function install(Request $request, $id, TranslatorInterface $translator): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, DeviceSensor::class);
            $sensor = $this->sensorService->getSensorById($id);

            if (!$sensor) {
                throw new NotFoundHttpException($translator->trans('entities.sensor.id_does_not_exist', [
                    '%id%' => $id
                ]));
            }

            $this->denyAccessUnlessGranted(Permission::DEVICE_SENSOR_EDIT, $sensor);
            $deviceSensor = $this->sensorService->installOnDevice($sensor, $request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($deviceSensor);
    }

    #[Route('/sensors/io-types', methods: ['GET'])]
    public function IOTypes(EntityManager $em): JsonResponse
    {
        try {
            $IOTypes = $em->getRepository(TrackerIOType::class)->getAll();

            return $this->viewItemsArray($IOTypes);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

    }
}
