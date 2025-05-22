<?php

namespace App\Service\User;

use App\Entity\User;

class UserServiceHelper
{
    /**
     * @param array $params
     * @param User $user
     * @return array
     */
    public static function handleTeamParams(array $params, User $user)
    {
        if ($user->isInClientTeam() || $user->isInResellerTeam()) {
            $params['teamId'] = [$user->getTeam()->getId()];
        }
        if ($user->isClientManager() && !$user->isAllTeamsPermissions()) {
            $params['teamId'] = $user->getManagedTeamsIds();
        }

        return $params;
    }
}