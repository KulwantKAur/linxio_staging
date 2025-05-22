<?php

namespace App\Security;

use App\Entity\DeviceSensor;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DeviceSensorVoter extends BaseVoter
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
                Permission::DEVICE_SENSOR_EDIT,
                Permission::DEVICE_SENSOR_CREATE,
                Permission::DEVICE_SENSOR_DELETE,
            ])
            && (is_object($subject) ? get_class($subject) === DeviceSensor::class : $subject === DeviceSensor::class);
    }
}