<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends BaseVoter
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

        return $user->hasPermission($attribute);

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param $attribute
     * @param $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [
                Permission::ADMIN_TEAM_USER_LIST,
                Permission::ADMIN_TEAM_NEW_USER,
                Permission::ADMIN_TEAM_EDIT_USER,
                Permission::USER_HISTORY_LAST_LOGIN,
                Permission::USER_HISTORY_UPDATED,
                Permission::USER_HISTORY_CREATED,
                Permission::ADMIN_TEAM_DELETE_USER,
                Permission::LOGIN_AS_USER,
                Permission::FULL_SEARCH,
                Permission::DRIVER_LIST,
                Permission::SET_MOBILE_DEVICE,
                Permission::SET_MOBILE_DEVICE_TOKEN,
                Permission::LOGIN_WITH_ID,
                Permission::PLATFORM_SETTING_ADMIN_EDIT,
                Permission::PLATFORM_SETTING_RESELLER_EDIT,
            ])
            && $subject === User::class;
    }
}