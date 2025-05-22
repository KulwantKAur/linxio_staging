<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity\Step;

use App\Service\DigitalForm\DigitalFormStepFactory;

class Signature extends File implements \JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return DigitalFormStepFactory::TYPE_SIGNATURE;
    }
}
