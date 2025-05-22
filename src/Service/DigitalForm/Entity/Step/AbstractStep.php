<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

abstract class AbstractStep
{
    abstract public function getType(): string;

    abstract public function fromArray(array $options);
}
