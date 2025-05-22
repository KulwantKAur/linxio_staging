<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\DeviceSensorType;

trait DeviceSensorTrait
{
    protected $deviceSensorData;

    /**
     * @When I want to get device sensor list by saved device
     */
    public function iWantToGetDeviceSensorList()
    {
        $this->fillData['deviceId'] = $this->deviceData->id;
        $params = http_build_query($this->fillData);
        $response = $this->get('/api/devices/sensors?' . $params);
        $responseData = json_decode($response->getResponse()->getContent());
        $deviceSensorData = isset($responseData->data) ? $responseData->data[0] : null;
        $this->deviceSensorData = $this->deviceSensorData ?: $deviceSensorData;
    }

    /**
     * @When I want to create device temp and humidity sensor by saved device and save id
     */
    public function iWantToCreateDeviceTempAndHumiditySensor()
    {
        $deviceSensorType = $this->getEntityManager()->getRepository(DeviceSensorType::class)
            ->findOneBy(['name' => DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE]);

        if (!$deviceSensorType) {
            throw new \Exception(
                'You should create device sensor type: ' . DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE
            );
        }

        $this->fillData['type'] = $deviceSensorType->getId();
        $response = $this->post('/api/devices/' . $this->deviceData->id . '/sensors', $this->fillData);

        if ($response->getResponse()->getStatusCode() === 200) {
            $this->deviceSensorData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to edit device sensor by saved sensor
     */
    public function iWantToEditDeviceSensor()
    {
        $this->patch('/api/devices/sensors/' . $this->deviceSensorData->id, $this->fillData);
    }

    /**
     * @When I want to get device sensor history by saved device
     */
    public function iWantToGetDeviceSensorHistoryByDevice()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/devices/' . $this->deviceData->id . '/sensors/history?' . $params);
    }

    /**
     * @When I want to get device sensor history by saved sensor
     */
    public function iWantToGetDeviceSensorHistoryBySensor()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/devices/sensors/' . $this->deviceSensorData->id . '/history?' . $params);
    }

    /**
     * @When I want to delete saved device sensor
     */
    public function iWantToDeleteDeviceSensor()
    {
        $this->delete('/api/devices/sensors/' . $this->deviceSensorData->id);
    }

    /**
     * @When I want to export to csv saved device temp and humidity sensor history
     */
    public function iWantToExportDeviceTempAndHumiditySensorHistory()
    {
        $deviceSensorType = $this->getEntityManager()->getRepository(DeviceSensorType::class)
            ->findOneBy(['name' => DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE]);

        if (!$deviceSensorType) {
            throw new \Exception(
                'You should create device sensor type: ' . DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE
            );
        }

        $this->fillData['sensorType'] = $deviceSensorType->getId();
        $params = http_build_query($this->fillData);
        $this->get('/api/devices/sensors/' . $this->deviceSensorData->id . '/history/csv?' . $params);
    }

    /**
     * @When I want to get vehicle list for report temp and humidity sensor
     */
    public function iWantToGetVehicleListForReportTempAndHumiditySensor()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/report/sensors/temp-and-humidity?' . $params);
    }

    /**
     * @When I want to export report temp and humidity sensor to csv by vehicle
     */
    public function iWantToExportReportTempAndHumiditySensorToCSVByVehicle()
    {
        $this->post('/api/devices/sensors/report/temp-and-humidity/vehicles/csv', $this->fillData);
    }

    /**
     * @When I want to get sensor list for report temp and humidity sensor
     */
    public function iWantToGetSensorListForReportTempAndHumiditySensor()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/devices/sensors/report/temp-and-humidity?' . $params);
    }

    /**
     * @When I want to export report temp and humidity sensor to csv by sensor
     */
    public function iWantToExportReportTempAndHumiditySensorToCSVBySensor()
    {
        $this->post('/api/devices/sensors/report/temp-and-humidity/csv', $this->fillData);
    }
}
