<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\ReminderCategory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ReminderCategoryVoter extends BaseVoter
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
                Permission::REMINDER_CATEGORY_LIST,
                Permission::REMINDER_CATEGORY_NEW,
                Permission::REMINDER_CATEGORY_EDIT,
                Permission::REMINDER_CATEGORY_DELETE
            ])
            && (is_object($subject)
                ? get_class($subject) === ReminderCategory::class
                : $subject === ReminderCategory::class);
    }
}