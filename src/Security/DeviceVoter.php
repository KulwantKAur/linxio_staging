<?php

namespace App\Security;

use App\Entity\Device;
use App\Entity\Permission;
use App\Entity\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DeviceVoter extends BaseVoter
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
                Permission::DEVICE_NEW,
                Permission::DEVICE_EDIT,
                Permission::DEVICE_DELETE,
                Permission::DEVICE_LIST,
                Permission::DEVICE_INSTALL_UNINSTALL,
                Permission::DEVICE_CHANGE_TEAM,
                Permission::DEVICES_VEHICLES_IMPORT_DATA
            ])
            && (is_object($subject) ? get_class($subject) === Device::class : $subject === Device::class);
    }
}