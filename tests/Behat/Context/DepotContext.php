<?php

namespace App\Tests\Behat\Context;


use Behatch\Json\Json;

/**
 * Defines application features from the specific context.
 */
class DepotContext extends VehicleContext
{

    protected $depotData;

    /**
     * @When I want to create depot and save id
     */
    public function iWantCreateDepot()
    {
        $response = $this->post('/api/depots', $this->fillData);
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->depotData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to create depot and save assign depot id
     */
    public function iWantCreateDepotAndSaveAssignDepotId()
    {
        $response = $this->post('/api/depots', $this->fillData);
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->fillData['assignDepotId'] = json_decode($response->getResponse()->getContent())->id;
        }
    }

    /**
     * @When I want to get depot by saved id
     */
    public function iWantGetDepotBySavedId()
    {
        $this->get('/api/depots/' . $this->depotData->id);
    }

    /**
     * @When I want to edit depot by saved id
     */
    public function iWantEditDepotBySavedId()
    {
        $this->patch('/api/depots/' . $this->depotData->id, $this->fillData);
    }

    /**
     * @When I want to delete depot by saved id
     */
    public function iWantDeleteDepotBySavedId()
    {
        $params = '';
        if (isset($this->fillData['assignDepotId'])) {
            $params = http_build_query($this->fillData);
        }
        $this->delete('/api/depots/' . $this->depotData->id . '?' . $params);
    }

    /**
     * @When I want get depot list
     */
    public function iWantGetDepotList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/depots?' . $params);
    }

    /**
     * @When I want fill depotId by saved Id
     */
    public function iWantFillDepotIdBySavedId()
    {
        $this->fillData['depotId'] = $this->depotData->id;
    }
}
