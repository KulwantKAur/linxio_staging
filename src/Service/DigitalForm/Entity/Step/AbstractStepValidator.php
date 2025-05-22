<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

abstract class AbstractStepValidator
{
    abstract public function isValid(array $stepOptions, $data = null): bool;
}
