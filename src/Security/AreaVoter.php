<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\Area;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AreaVoter extends BaseVoter
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
                Permission::AREA_NEW,
                Permission::AREA_EDIT,
                Permission::AREA_DELETE,
                Permission::AREA_ARCHIVE,
            ])
            && (is_object($subject) ? get_class($subject) === Area::class : $subject === Area::class);
    }
}