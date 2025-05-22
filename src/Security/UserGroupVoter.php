<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\UserGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserGroupVoter extends BaseVoter
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
                Permission::USER_GROUP_NEW,
                Permission::USER_GROUP_EDIT,
                Permission::USER_GROUP_DELETE,
                Permission::USER_GROUP_ARCHIVE
            ])
            && (is_object($subject) ? get_class($subject) === UserGroup::class : $subject === UserGroup::class);
    }
}