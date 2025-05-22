<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class TextMulti extends AbstractStep implements \JsonSerializable
{
    /** @var string */
    private $default = '';

    /** @var int */
    private $min = 0;

    /** @var int */
    private $max = 32768;


    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_TEXT_MULTI;
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $options)
    {
        $this->default = (string) ($options['default'] ?? $this->default);
        $this->min = (int) ($options['min'] ?? $this->min);
        $this->max = (int) ($options['max'] ?? $this->max);
    }

    public function setDefault(string $default): void
    {
        $this->default = $default;
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
            'default' => $this->default,
            'min' => $this->min,
            'max' => $this->max,
        ];
    }
}
