<?php

namespace App\Security;

use App\Entity\Notification\Notification;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotificationVoter extends BaseVoter
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
                Permission::CONFIGURATION_NOTIFICATIONS,
                Permission::NOTIFICATION_NEW,
                Permission::NOTIFICATION_LIST,
                Permission::NOTIFICATION_EDIT,
                Permission::NOTIFICATION_DELETE,
            ])
            && (is_object($subject) ? get_class($subject) === Notification::class : $subject === Notification::class);
    }
}
