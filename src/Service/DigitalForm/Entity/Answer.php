<?php

declare(strict_types = 1);

namespace App\Service\DigitalForm\Entity;

use App\Entity\DigitalFormStep;

class Answer
{
    /** @var array */
    private $data = [];


    public function setAnswer(DigitalFormStep $step, array $data): void
    {
        $this->data[] = [
            'step' => $step,
            'data' => $data,
        ];
    }

    public function getData(): array
    {
        return $this->data;
    }
}
