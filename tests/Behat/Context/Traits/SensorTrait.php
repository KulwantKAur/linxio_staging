<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\DeviceSensorType;

trait SensorTrait
{
    protected $sensorData;

    /**
     * @When I want to create temp and humidity sensor and save id
     */
    public function iWantToCreateTempAndHumiditySensor()
    {
        $sensorType = $this->getEntityManager()->getRepository(DeviceSensorType::class)
            ->findOneBy(['name' => DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE]);

        if (!$sensorType) {
            throw new \Exception(
                'You should create sensor type: ' . DeviceSensorType::TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE
            );
        }

        $this->fillData['type'] = $sensorType->getId();
        $response = $this->post('/api/sensors', $this->fillData);

        if ($response->getResponse()->getStatusCode() === 200) {
            $this->sensorData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to edit sensor by saved id
     */
    public function iWantToEditSensor()
    {
        $this->patch('/api/sensors/' . $this->sensorData->id, $this->fillData);
    }

    /**
     * @When I want to delete saved sensor
     */
    public function iWantToDeleteSensor()
    {
        $this->delete('/api/sensors/' . $this->sensorData->id);
    }

    /**
     * @When I want to get sensor by saved id
     */
    public function iWantToGetSensorBySavedId()
    {
        $this->get('/api/sensors/' . $this->sensorData->id);
    }

    /**
     * @When I want to export to csv temp and humidity sensor
     */
    public function iWantToExportTempAndHumiditySensor()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/sensors/csv?' . $params);
    }

    /**
     * @When I want to get sensor list
     */
    public function iWantToGetSensors()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/sensors?' . $params);
    }
}
