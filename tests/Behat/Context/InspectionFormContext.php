<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\InspectionFormTrait;
use App\Tests\Behat\Context\Traits\VehicleTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;

class InspectionFormContext extends VehicleGroupContext
{
    use VehicleTrait;
    use UsersTrait;
    use InspectionFormTrait;
}
