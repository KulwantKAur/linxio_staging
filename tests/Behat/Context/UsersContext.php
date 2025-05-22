<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\UsersGroupTrait;
use App\Tests\Behat\Context\Traits\NotificationTrait;
use App\Tests\Behat\Context\Traits\RabbitMqTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;
use App\Tests\Behat\Context\Traits\ClientsTrait;

class UsersContext extends BasicContext
{
    use UsersTrait;
    use UsersGroupTrait;
    use ClientsTrait;
    use NotificationTrait;
    use RabbitMqTrait;
}
