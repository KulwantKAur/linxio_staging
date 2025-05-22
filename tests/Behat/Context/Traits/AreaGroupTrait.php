<?php

namespace App\Tests\Behat\Context\Traits;

trait AreaGroupTrait
{
    protected $areaGroupData;

    /**
     * @When I want to create area group and save id
     */
    public function iWantCreateAreaGroup()
    {
        $this->post('/api/area-groups', $this->fillData);

        $response = $this->getResponse();
        if ($response->getStatusCode() === 200) {
            $this->areaGroupData = json_decode($response->getContent());
        }
    }

    /**
     * @When I want to edit area group by saved id
     */
    public function iWantEditAreaGroup()
    {
        $this->patch('/api/area-groups/' . $this->areaGroupData->id, $this->fillData);
    }

    /**
     * @When I want delete area group by saved id
     */
    public function iWantDeleteAreaGroup()
    {
        $this->delete('/api/area-groups/' . $this->areaGroupData->id);
    }

    /**
     * @When I want fill area group id
     */
    public function iWantFillAreaGroupId()
    {
        $this->fillData['groupIds'][] = $this->areaGroupData->id;
    }

    /**
     * @When I want fill area group ids
     */
    public function iWantFillAreaGroupIds()
    {
        $this->fillData['areaGroupIds'] = $this->areaGroupData->id;
    }

    /**
     * @When I want get area group by saved id
     */
    public function iWantGetAreaGroupById()
    {
        $this->get('/api/area-groups/' . $this->areaGroupData->id);
    }

    /**
     * @When I want to get area group list
     */
    public function iWantGetAreaGroupList()
    {
        $this->get('/api/area-groups');
    }
}
