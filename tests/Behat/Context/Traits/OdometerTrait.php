<?php

namespace App\Tests\Behat\Context\Traits;

trait OdometerTrait
{
    protected $vehicleData;
    protected $odometerData;
    protected $driverId;

    /**
     * @When I want to create odometer record and save id
     */
    public function iWantToCreateOdometerRecord()
    {
        $this->post(
            '/api/vehicles/' . $this->vehicleData->id . '/odometer',
            $this->fillData,
            [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $this->odometerData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want to get vehicle current odometer
     */
    public function iWantToGetLastOdometerRecord()
    {
        $this->get(
            '/api/vehicles/' . $this->vehicleData->id . '/odometer'
        );
    }

    /**
     * @When I want to get vehicle odometer by date
     */
    public function iWantToGetOdometerRecordByDate()
    {
        $this->get(
            '/api/vehicles/' . $this->vehicleData->id . '/odometer?occurredAt=' . $this->fillData['occurredAt']
        );
    }

    /**
     * @When I want to get vehicle odometer history
     */
    public function iWantToGetOdometerHistory()
    {
        $this->get(
            '/api/vehicles/' . $this->vehicleData->id . '/odometer/history'
        );
    }

    /**
     * @When I want to update odometer record
     */
    public function iWantToUpdateOdometerRecord()
    {
        $this->patch(
            '/api/vehicles/' . $this->vehicleData->id . '/odometer/' . $this->odometerData->id,
            $this->fillData
        );

        $this->odometerData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want to delete saved odometer record
     */
    public function iWantToDeleteSavedOdometerRecord()
    {
        $this->delete(
            '/api/vehicles/' . $this->vehicleData->id . '/odometer/' . $this->odometerData->id
        );
    }
}
