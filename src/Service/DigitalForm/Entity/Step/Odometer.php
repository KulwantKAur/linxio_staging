<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class Odometer extends AbstractStep implements \JsonSerializable
{
    /** @var int */
    private $default = 0;

    /** @var int */
    private $range = 20;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_ODOMETER;
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $options)
    {
        $this->default = (int) ($options['default'] ?? $this->default);
        $this->range = (int) ($options['range'] ?? $this->range);
    }

    public function setDefault(int $default): void
    {
        $this->default = $default;
    }

    public function setRange(int $range): void
    {
        $this->range = $range;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'default' => $this->default,
            'range' => $this->range,
        ];
    }
}
