<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class NumberFloat extends AbstractStep implements \JsonSerializable
{
    /** @var string */
    private $default = '';

    /** @var int */
    private $min = 0;

    /** @var int */
    private $max = 0;

    /**
     * @return string
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_NUMBER_FLOAT;
    }

    /**
     * @param array $options
     */
    public function fromArray(array $options)
    {
        $this->default = (string) ($options['default'] ?? $this->default);
        $this->min = (float) ($options['min'] ?? $this->min);
        $this->max = (float) ($options['max'] ?? $this->max);
    }

    /**
     * @param string $default
     */
    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    /**
     * @param float $min
     */
    public function setMin(float $min): void
    {
        $this->min = $min;
    }

    /**
     * @param float $max
     */
    public function setMax(float $max): void
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
            'default' => $this->default,
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
