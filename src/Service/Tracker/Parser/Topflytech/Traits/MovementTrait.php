<?php

declare(strict_types=1);

namespace App\Service\Tracker\Parser\Topflytech\Traits;

/**
 * Class MovementTrait
 * @package App\Service\Tracker\Parser\Topflytech\Traits
 */
trait MovementTrait
{
    public $movement = null;

    /**
     * @param float|null $speed
     * @return void
     */
    public function formatMovement(?float $speed = null): void
    {
        if (is_null($speed) || $speed === false) {
            /** @var float|null $speed */
            $speed = method_exists($this, 'getGpsData') && $this->getGpsData() ? $this->getGpsData()->getSpeed() : null;
        }

        if (!is_null($speed)) {
            $this->movement = ($speed > 0) ? 1 : 0;
        }
    }

    /**
     * @return null
     */
    public function getMovement()
    {
        return $this->movement;
    }
}
