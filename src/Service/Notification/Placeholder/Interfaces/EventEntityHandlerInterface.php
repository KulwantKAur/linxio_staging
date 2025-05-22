<?php

namespace App\Service\Notification\Placeholder\Interfaces;

use App\Entity\Team;
use App\Entity\User;

interface EventEntityHandlerInterface
{
    /**
     * @return object
     */
    public function getEntity(): object;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team;

    /**
     * @return array
     */
    public function getContext(): array;

    /**
     * @return array
     */
    public function getValueHandlerPlaceholder(?User $user = null): array;
}
