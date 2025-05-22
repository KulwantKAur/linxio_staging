<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\NotificationTrait;
use App\Tests\Behat\Context\Traits\RabbitMqTrait;
use App\Tests\Behat\Context\Traits\TrackerTeltonikaTrait;

class TrackerTeltonikaContext extends DeviceSensorContext
{
    use TrackerTeltonikaTrait;
    use RabbitMqTrait;
    use NotificationTrait;
}
