<?php

namespace App\Tests\Behat\Context\Traits;

trait VehicleGroupTrait
{
    protected $vehicleGroupData;

    /**
     * @When I want to create vehicle group and save id
     */
    public function iWantCreateVehicleGroup()
    {
        $this->post('/api/vehicle-groups', $this->fillData);
        $this->vehicleGroupData = json_decode($this->getResponse()->getContent());
    }

    /**
     * @When I want to edit vehicle group by saved id
     */
    public function iWantEditVehicleGroup()
    {
        $this->patch('/api/vehicle-groups/' . $this->vehicleGroupData->id, $this->fillData);
    }

    /**
     * @When I want delete vehicle group by saved id
     */
    public function iWantDeleteVehicleGroup()
    {
        $this->delete('/api/vehicle-groups/' . $this->vehicleGroupData->id);
    }

    /**
     * @When I want fill vehicle group id
     */
    public function iWantFillVehicleGroupId()
    {
        $this->fillData['groupIds'][] = $this->vehicleGroupData->id;
    }

    /**
     * @When I want get vehicle group by saved id
     */
    public function iWantGetVehicleGroupById()
    {
        $this->get('/api/vehicle-groups/' . $this->vehicleGroupData->id);
    }

    /**
     * @When I want to get vehicle group list
     */
    public function iWantGetVehicleGroupList()
    {
        $this->get('/api/vehicle-groups');
    }
}
