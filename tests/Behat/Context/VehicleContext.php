<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\TrackerTrait;
use App\Tests\Behat\Context\Traits\OdometerTrait;
use App\Tests\Behat\Context\Traits\VehicleTrait;
use App\Tests\Behat\Context\Traits\VehicleGroupTrait;
use App\Tests\Behat\Context\Traits\AreaTrait;
use App\Tests\Behat\Context\Traits\AreaGroupTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;
use App\Tests\Behat\Context\Traits\ClientsTrait;

class VehicleContext extends VehicleGroupContext
{
    use VehicleTrait;
    use VehicleGroupTrait;
    use AreaTrait;
    use AreaGroupTrait;
    use UsersTrait;
    use ClientsTrait;
    use TrackerTrait;
    use OdometerTrait;
}
