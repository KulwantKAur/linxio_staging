<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\Vehicle;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VehicleVoter extends BaseVoter
{
    /**
     * @param $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool|object|string
     */
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
                Permission::VEHICLE_EDIT,
                Permission::VEHICLE_DELETE,
                Permission::FLEET_SECTION_ADD_VEHICLE,
                Permission::VEHICLE_ARCHIVE,
            ])
            && (is_object($subject) ? get_class($subject) === Vehicle::class : $subject === Vehicle::class);
    }
}