<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

class OdometerValidator extends AbstractStepValidator
{
    /**
     * @inheritDoc
     */
    public function isValid(array $stepOptions, $data = null): bool
    {
        return (int)$data >= 0;
    }
}
