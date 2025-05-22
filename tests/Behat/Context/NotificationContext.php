<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\AreaGroupTrait;
use App\Tests\Behat\Context\Traits\AreaTrait;
use App\Tests\Behat\Context\Traits\ClientsTrait;
use App\Tests\Behat\Context\Traits\DeviceTrait;
use App\Tests\Behat\Context\Traits\DigitalFormTrait;
use App\Tests\Behat\Context\Traits\DrivingBehaviorTrait;
use App\Tests\Behat\Context\Traits\InspectionFormTrait;
use App\Tests\Behat\Context\Traits\NotificationTrait;
use App\Tests\Behat\Context\Traits\OdometerTrait;
use App\Tests\Behat\Context\Traits\RabbitMqTrait;
use App\Tests\Behat\Context\Traits\ReminderTraits;
use App\Tests\Behat\Context\Traits\ServiceRecordTrait;
use App\Tests\Behat\Context\Traits\TrackerTrait;
use App\Tests\Behat\Context\Traits\UsersGroupTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;
use App\Tests\Behat\Context\Traits\VehicleGroupTrait;
use App\Tests\Behat\Context\Traits\VehicleTrait;

class NotificationContext extends BasicContext
{
    use AreaTrait;
    use AreaGroupTrait;
    use ClientsTrait;
    use DrivingBehaviorTrait;
    use DeviceTrait;
    use DigitalFormTrait;
    use ReminderTraits;
    use RabbitMqTrait;
    use NotificationTrait;
    use OdometerTrait;
    use ServiceRecordTrait;
    use TrackerTrait;
    use UsersTrait;
    use UsersGroupTrait;
    use VehicleTrait;
    use VehicleGroupTrait;
    use InspectionFormTrait;
}
