<?php

namespace App\Tests\Behat\Context\Traits;

use App\Entity\Device;
use App\Entity\DeviceModel;

trait DeviceTrait
{
    protected $deviceData;

    /**
     * @When I want to create device and save id
     */
    public function iWantCreateDevice()
    {
        $response = $this->post('/api/devices', $this->fillData);

        if ($response->getResponse()->getStatusCode() === 200) {
            $this->deviceData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to edit device and save id
     */
    public function iWantEditDevice()
    {
        $this->patch('/api/devices/' . $this->deviceData->id, $this->fillData);
    }

    /**
     * @When I want to delete saved device
     */
    public function iWantDeleteDevice()
    {
        $this->delete('/api/devices/' . $this->deviceData->id);
    }

    /**
     * @When I want restore saved device
     */
    public function iWantRestoreDevice()
    {
        $this->post('/api/devices/' . $this->deviceData->id . '/restore');
    }

    /**
     * @When I want to get device by saved id
     */
    public function iWantGetDeviceBySavedIdData()
    {
        $this->get('/api/devices/' . $this->deviceData->id);
    }

    /**
     * @When I want to get device notes by saved id and type :type
     */
    public function iWantGetDeviceNotesBySavedId($type)
    {
        $this->get('/api/device-notes/' . $this->deviceData->id . '/' . $type);
    }


    /**
     * @When I want get device list
     */
    public function iWantGetDeviceList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/devices/json?' . $params);
    }

    /**
     * @When I want install device
     */
    public function iWantInstallDeviceByVehicle()
    {
        $response = $this->post(
            '/api/devices/' . $this->deviceData->id . '/install',
            ['vehicleId' => $this->vehicleData->id, 'odometer' => $this->fillData['odometer'] ?? null]
        );

        if ($response->getResponse()->getStatusCode() === 200) {
            $this->deviceData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want check vehicle status
     */
    public function iWantCheckVehicleStatus()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/vehicles/' . $this->vehicleData->id . '?' . $params);
    }

    /**
     * @When I want get device installation
     */
    public function iWantGetDeviceInstallation()
    {
        $params = http_build_query(
            [
                'deviceImei' => $this->deviceData->imei,
                'vehicleRegNo' => $this->deviceData->deviceInstallation->vehicle->regNo,
            ]
        );
        $this->get('/api/devices/installation/?' . $params);
    }

    /**
     * @When I see right deviceId
     */
    public function iWantSeeRightDeviceId()
    {
        try {
            $this->jsonResponse()
                ->equal($this->deviceData->id, ['at' => "deviceId"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . " " . $this->getResponse()->getContent());
        }
    }

    /**
     * @When I want get coordinates list by imei :imei
     */
    public function iWantGetCoordinatesListByDeviceImei($imei)
    {
        $device = $this->getEntityManager()->getRepository(Device::class)->findOneBy(['imei' => $imei]);

        $params = http_build_query($this->fillData);
        $this->get('/api/devices/' . $device->getId() . '/coordinates?' . $params);
    }

    /**
     * @When I want get paginated coordinates list by imei :imei
     */
    public function iWantGetPaginatedCoordinatesListByDeviceImei($imei)
    {
        $device = $this->getEntityManager()->getRepository(Device::class)->findOneBy(['imei' => $imei]);

        $params = http_build_query($this->fillData);
        $this->get('/api/devices/' . $device->getId() . '/coordinates/paginated?' . $params);
    }

    /**
     * @When I want fill device model with name :model
     */
    public function iWantFillDeviceModelWithName($model)
    {
        $model = $this->getEntityManager()->getRepository(DeviceModel::class)->findOneBy(['name' => $model]);
        $this->fillData['modelId'] = $model->getId();
    }

    /**
     * @When I want get device vendors list
     */
    public function iWantGetDeviceVendorsList()
    {
        $this->get('/api/devices/vendors/');
    }

    /**
     * @When I want export devices list
     */
    public function iWantExportDevicesList()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/devices/csv?' . $params);
    }

    /**
     * @When I want import devices and vehicles file
     */
    public function iWantUploadDevicesVehiclesFile()
    {
        $this->post(
            '/api/devices-vehicles/upload',
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            ['files' => $this->files]
        );
    }
}
