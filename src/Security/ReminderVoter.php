<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\Reminder;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ReminderVoter extends BaseVoter
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
    }

    /**
     * @param $attribute
     * @param $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [
                Permission::VEHICLE_REMINDER_NEW,
                Permission::VEHICLE_REMINDER_EDIT,
                Permission::VEHICLE_REMINDER_DELETE,
                Permission::VEHICLE_REMINDER_LIST,
                Permission::VEHICLE_REMINDER_ARCHIVE,
                Permission::ASSET_REMINDER_ARCHIVE,
            ])
            && (is_object($subject) ? get_class($subject) === Reminder::class : $subject === Reminder::class);
    }
}