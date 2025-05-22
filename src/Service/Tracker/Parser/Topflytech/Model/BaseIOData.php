<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Model;

abstract class BaseIOData
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this;
    }
}
