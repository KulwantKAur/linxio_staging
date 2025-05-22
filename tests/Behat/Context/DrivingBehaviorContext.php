<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\DrivingBehaviorTrait;

/**
 * Defines application features from the specific context.
 */
class DrivingBehaviorContext extends DeviceContext
{
    use DrivingBehaviorTrait;
}
