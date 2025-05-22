<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\AreaTrait;
use App\Tests\Behat\Context\Traits\AreaGroupTrait;
use App\Tests\Behat\Context\Traits\TrackerTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;
use App\Tests\Behat\Context\Traits\ClientsTrait;
use App\Tests\Behat\Context\Traits\VehicleTrait;
use App\Tests\Behat\Context\Traits\VehicleGroupTrait;

class AreaContext extends AreaGroupContext
{
    use AreaTrait;
    use AreaGroupTrait;
    use UsersTrait;
    use ClientsTrait;
    use VehicleTrait;
    use VehicleGroupTrait;
    use TrackerTrait;
}
