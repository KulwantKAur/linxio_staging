<?php

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Traits\DigitalFormTrait;
use App\Tests\Behat\Context\Traits\UsersTrait;

class DigitalFormContext extends AreaContext
{
    use UsersTrait;
    use DigitalFormTrait;
}
