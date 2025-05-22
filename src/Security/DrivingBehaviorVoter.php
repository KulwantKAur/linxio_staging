<?php

namespace App\Security;

use App\Entity\DrivingBehavior;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DrivingBehaviorVoter extends BaseVoter
{
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if ($user->isClientManager() && is_object($subject)) {
            return $user->hasPermission($attribute)
                && ($user->isAllTeamsPermissions() || $user->hasTeamPermission($subject->getTeam()->getId()));
        }

        return $user->hasPermission($attribute);
    }

    /**
     * @param $attribute
     * @param $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [
                Permission::DRIVING_BEHAVIOUR_DASHBOARD,
                Permission::DRIVING_BEHAVIOUR_VEHICLES,
                Permission::DRIVING_BEHAVIOUR_DRIVERS
            ])
            && (is_object($subject) ? get_class($subject) === DrivingBehavior::class : $subject === DrivingBehavior::class);
    }
}