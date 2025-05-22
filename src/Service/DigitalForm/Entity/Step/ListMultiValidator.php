<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

class ListMultiValidator extends AbstractStepValidator
{
    /**
     * @inheritDoc
     */
    public function isValid(array $stepOptions, $data = null): bool
    {
        $validIndexes = [];
        foreach ($stepOptions['items'] as $item) {
            $validIndexes[] = $item['index'];
        }

        if (empty($data) && (count($validIndexes) > 0)) {
            return false;
        }

        if (!is_array($data)) {
            $data = [$data];
        }

        return count($data) === count(array_intersect($validIndexes, $data));
    }
}
