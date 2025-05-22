<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class File extends AbstractStep implements \JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_FILE;
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $options)
    {
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
        ];
    }
}
