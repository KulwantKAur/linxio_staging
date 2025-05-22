<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

class NumberFloatValidator extends AbstractStepValidator
{
    /**
     * @inheritDoc
     */
    public function isValid(array $stepOptions, $data = null): bool
    {
        return ((float)$data >= $stepOptions['min']) && ((float)$data <= $stepOptions['max']);
    }
}
