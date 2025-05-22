<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\AreaGroupTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;
use App\Tests\Behat\Context\Traits\ClientsTrait;

class AreaGroupContext extends BasicContext
{
    use AreaGroupTrait;
    use UsersTrait;
    use ClientsTrait;
}
