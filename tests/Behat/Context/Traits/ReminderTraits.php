<?php

namespace App\Tests\Behat\Context\Traits;

trait ReminderTraits
{
    protected $reminderData;
    protected $reminderCategoryData;

    /**
     * @When I want to create reminder and save id
     */
    public function iWantCreateReminder()
    {
        $response = $this->post('/api/reminders/', $this->fillData);
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->reminderData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want to get reminder by saved id
     */
    public function iWantGetReminderBySavedId()
    {
        $this->get('/api/reminders/' . $this->reminderData->id);
    }

    /**
     * @When I want to edit reminder by saved id
     */
    public function iWantEditReminderBySavedId()
    {
        $this->post('/api/reminders/' . $this->reminderData->id, $this->fillData);
    }

    /**
     * @When I want to delete reminder by saved id
     */
    public function iWantDeleteReminderBySavedId()
    {
        $this->delete('/api/reminders/' . $this->reminderData->id);
    }

    /**
     * @When I want get reminder list
     */
    public function iWantGetReminderList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders?' . $params);
    }

    /**
     * @When I want export reminders list
     */
    public function iWantExportRemindersList()
    {
        $params = http_build_query($this->fillData);
        return $this->get('/api/reminders/csv?' . $params);
    }

    /**
     * @When I want fill :field with saved reminder category id
     */
    public function iWantFillReminderCategoryId($field)
    {
        $this->fillData[$field] = $this->reminderCategoryData->id;
    }

    /**
     * @When I want create reminder category and save id
     */
    public function iWantCreateReminderCategory()
    {
        $response = $this->post('/api/reminders/category', $this->fillData);
        if ($response->getResponse()->getStatusCode() === 200) {
            $this->reminderCategoryData = json_decode($response->getResponse()->getContent());
        }
    }

    /**
     * @When I want edit reminder category
     */
    public function iWantEditReminderCategory()
    {
        $this->patch('/api/reminders/category/' . $this->reminderCategoryData->id, $this->fillData);
    }

    /**
     * @When I want get reminder category list
     */
    public function iWantGetReminderCategoryList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/category?' . $params);
    }

    /**
     * @When I want export reminder category list
     */
    public function iWantExportReminderCategoryList()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/category/csv?' . $params);
    }

    /**
     * @When I want get reminder category by saved id
     */
    public function iWantGetReminderCategoryById()
    {
        $this->get('/api/reminders/category/' . $this->reminderCategoryData->id);
    }

    /**
     * @When I want to delete reminder category by saved id
     */
    public function iWantDeleteReminderCategoryBySavedId()
    {
        $this->delete('/api/reminders/category/' . $this->reminderCategoryData->id);
    }

    /**
     * @When I want export reminders csv
     */
    public function iWantExportRemindersCsv()
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders/csv?' . $params);
    }

    /**
     * @When I want get reminders report with type :type
     */
    public function iWantGetRemindersReport($type)
    {
        $params = http_build_query($this->fillData);
        $this->get('/api/reminders-report/' . $type . '?' . $params);
    }

    /**
     * @When I want to duplicate reminder
     */
    public function iWantDuplicateReminder()
    {
        $this->post('/api/reminders/' . $this->reminderData->id . '/duplicate', $this->fillData);
    }

    /**
     * @When I want get reminder dashboard statistic
     */
    public function iWantGetReminderDashboardStatistic()
    {
        return $this->get('/api/dashboard/reminders/stat');
    }
}
