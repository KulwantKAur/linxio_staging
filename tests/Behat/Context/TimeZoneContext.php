<?php

namespace App\Tests\Behat\Context;

/**
 * Defines application features from the specific context.
 */
class TimeZoneContext extends BasicContext
{
    /**
     * @When I want get timezones
     */
    public function iWantGetTimezones()
    {
        return $this->get('/api/timezones');
    }
}