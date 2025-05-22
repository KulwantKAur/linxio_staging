<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\Setting;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SettingVoter extends BaseVoter
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
                Permission::SETTING_SET
            ])
            && (is_object($subject) ? get_class($subject) === Setting::class : $subject === Setting::class);
    }
}