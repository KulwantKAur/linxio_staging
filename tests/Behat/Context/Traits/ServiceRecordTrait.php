<?php

namespace App\Tests\Behat\Context\Traits;

trait ServiceRecordTrait
{

    protected $serviceRecord;
    protected $repair;

    /**
     * @When I want to create service record for saved reminder and save id
     */
    public function iWantCreateServiceRecordForReminderById()
    {
        $response = $this->post(
            '/api/reminders/' . $this->reminderData->id . '/service-records/',
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            ['files' => $this->files]
        );
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->serviceRecord = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to create repair and save id
     */
    public function iWantCreateRepair()
    {
        $response = $this->post(
            '/api/repairs',
            $this->fillData,
            ['CONTENT_TYPE' => 'multipart/form-data'],
            ['files' => $this->files]
        );
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->repair = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to edit repair by saved id
     */
    public function iWantEditRepairById()
    {
        $this->post('/api/repairs/' . $this->repair->id, $this->fillData);
    }

    /**
     * @When I want to get repair by saved id
     */
    public function iWantGetRepairById()
    {
        $this->get('/api/repairs/' . $this->repair->id);
    }

    /**
     * @When I want to delete repair by saved id
     */
    public function iWantDeleteRepairById()
    {
        $this->delete('/api/repairs/' . $this->repair->id);
    }

    /**
     * @When I want get repairs list
     */
    public function iWantGetRepairsList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/repairs/vehicle/' . $this->vehicleData->id . '?' . $params);
    }

    /**
     * @When I want get service records vehicles list
     */
    public function iWantGetServiceRecordsVehiclesList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/service-records/vehicles?' . $params);
    }

    /**
     * @When I want get common report
     */
    public function iWantGetCommonReport()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/service-records/common/json?' . $params);
    }

    /**
     * @When I want get common vehicle list
     */
    public function iWantGetCommonVehicleList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/service-records/common/vehicles/json?' . $params);
    }

    /**
     * @When I want get repairs vehicles list
     */
    public function iWantGetRepairsVehiclesList()
    {
        $this->post('/api/repairs/vehicles', $this->fillData);
    }

    /**
     * @When I want get repairs by vehicle
     */
    public function iWantGetRepairsByVehicle()
    {
        $this->post('/api/repairs/detailed/json', $this->fillData);
    }

    /**
     * @When I want export repairs list
     */
    public function iWantExportRepairsList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/repairs/vehicle/' . $this->vehicleData->id . '/csv?' . $params);
    }


    /**
     * @When I want to get service record from reminder by id
     */
    public function iWantGetServiceRecordFromReminderById()
    {
        $this->get('/api/reminders/' . $this->reminderData->id . '/service-records/' . $this->serviceRecord->id);
    }

    /**
     * @When I want to edit service record for saved reminder by saved id
     */
    public function iWantEditServiceRecordFromReminderById()
    {
        $this->post(
            '/api/reminders/' . $this->reminderData->id . '/service-records/' . $this->serviceRecord->id,
            $this->fillData
        );
    }

    /**
     * @When I want to delete service record for saved reminder by saved id
     */
    public function iWantDeleteServiceRecordFromReminderById()
    {
        $this->delete('/api/reminders/' . $this->reminderData->id . '/service-records/' . $this->serviceRecord->id);
    }

    /**
     * @When I want get service record list for saved reminder
     */
    public function iWantGetServiceRecordListForReminder()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/' . $this->reminderData->id . '/service-records' . '?' . $params);
    }

    /**
     * @When I want get full service record list
     */
    public function iWantGetFullServiceRecordList()
    {
        $this->get('/api/reminders/service-records/');
    }

    /**
     * @When I want get service record summary report
     */
    public function iWantGetServiceRecordSummaryReport()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/service-records/summary/json?' . $params);
    }

    /**
     * @When I want get service record detailed report
     */
    public function iWantGetServiceRecordDetailedReport()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/service-records/detailed/json?' . $params);
    }

    /**
     * @When I want to get repairs dashboard
     */
    public function iWantGetRepairsDashboard()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/dashboard/repairs/stat?' . $params);
    }
}