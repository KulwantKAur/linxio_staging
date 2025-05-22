<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

class TextMultiValidator extends AbstractStepValidator
{
    /**
     * @inheritDoc
     */
    public function isValid(array $stepOptions, $data = null): bool
    {
        return (mb_strlen((string)$data) >= $stepOptions['min']) && (mb_strlen((string)$data) <= $stepOptions['max']);
    }
}
