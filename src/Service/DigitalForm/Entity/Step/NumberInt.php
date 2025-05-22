<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class NumberInt extends AbstractStep implements \JsonSerializable
{
    /** @var int */
    private $min = 0;

    /** @var int */
    private $max = 0;


    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_NUMBER_INT;
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $options)
    {
        $this->min = (int) ($options['min'] ?? $this->min);
        $this->max = (int) ($options['max'] ?? $this->max);
    }

    public function setMin(int $min): void
    {
        $this->min = $min;
    }

    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
