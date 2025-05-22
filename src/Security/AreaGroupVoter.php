<?php

namespace App\Security;

use App\Entity\AreaGroup;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AreaGroupVoter extends BaseVoter
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

        if (($user->isClientManager() || $user->isInClientTeam()) && is_object($subject)) {
            return
                $user->hasPermission($attribute)
                && (
                $user->isAllTeamsPermissions()
                || $user->hasTeamPermission($subject->getTeam()->getId())
                || $user->getTeam()->getId() === $subject->getTeam()->getId()
                );
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
                Permission::AREA_GROUP_NEW,
                Permission::AREA_GROUP_EDIT,
                Permission::AREA_GROUP_DELETE,
                Permission::AREA_GROUP_ARCHIVE,
            ])
            && (is_object($subject) ? get_class($subject) === AreaGroup::class : $subject === AreaGroup::class);
    }
}