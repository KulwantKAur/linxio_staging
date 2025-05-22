<?php

namespace App\Security;

use App\Entity\Client;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClientVoter extends BaseVoter
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
                && ($user->hasTeamPermission($subject->getTeam()->getId()) || $user->isAllTeamsPermissions());
        }

        if ($user->isInResellerTeam() && is_object($subject)) {
            return $subject->getTeam()->isClientTeam()
                && $subject->getOwnerTeam()->getId() === $user->getTeamId();
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
        return in_array(
                $attribute,
                [
                    Permission::NEW_CLIENT,
                    Permission::CLIENT_LIST,
                    Permission::CLIENT_NEW_USER,
                    Permission::CLIENT_EDIT_USER,
                    Permission::CLIENT_BLOCK_USER,
                    Permission::CLIENT_USER_RESET_PWD,
                    Permission::CLIENT_DELETE_USER,
                    Permission::CLIENT_STATUS_HISTORY,
                    Permission::CLIENT_UPDATED_HISTORY,
                    Permission::CLIENT_CREATED_HISTORY,
                    Permission::CLIENT_NOTES_HISTORY,
                    Permission::LOGIN_AS_CLIENT,
                    Permission::CONFIGURATION_COMPANY_INFO_EDIT,
                    Permission::CONFIGURATION_COMPANY_INFO,
                    Permission::CLIENT_ARCHIVE_USER,
                ]
            )
            && (is_object($subject) ? get_class($subject) === Client::class : $subject === Client::class);
    }
}