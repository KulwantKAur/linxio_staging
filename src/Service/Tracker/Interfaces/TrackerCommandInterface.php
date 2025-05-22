<?php

namespace App\Service\Tracker\Interfaces;

interface TrackerCommandInterface
{
    /**
     * @return string|null
     */
    public function getCommand(): ?string;

    /**
     * @return int|null
     */
    public function getType(): ?int;
}