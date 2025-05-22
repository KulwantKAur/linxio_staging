<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\Asset;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AssetVoter extends BaseVoter
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
        return in_array($attribute, Permission::ASSET_PERMISSIONS_ARRAY)
            && (is_object($subject) ? get_class($subject) === Asset::class : $subject === Asset::class);
    }
}