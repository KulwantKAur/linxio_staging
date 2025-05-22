<?php

namespace App\Service\Tracker\Interfaces;

interface PanicButtonInterface
{
    /**
     * @return bool
     */
    public function isPanicButton(): bool;
}