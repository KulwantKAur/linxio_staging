<?php

namespace App\Tests\Behat\Context;


use Behatch\Json\Json;

/**
 * Defines application features from the specific context.
 */
class StorageContext extends UsersContext
{
    /**
     * @When I want to create storage record
     */
    public function iWantCreateStorageRecord()
    {
        $this->post('/api/storage/', $this->fillData);
    }

    /**
     * @When I want to get storage record by key :key
     * @param $key
     */
    public function iWantGetStorageRecord($key)
    {
        $this->get('/api/storage/' . $key);
    }
}
