<?php

namespace App\Security;

use App\Entity\Chat;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ChatUserVoter extends BaseVoter
{
    /**
     * @param $attribute
     * @param Chat $subject
     * @param TokenInterface $token
     * @return bool|object|string
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (is_object($subject)) {
            return (($user->hasPermission($attribute) && $subject->hasUser($user))
                    || $user->hasPermission(Permission::CHAT_LIST_ALL)
                ) && $user->getTeam()->getId() === $subject->getTeam()->getId();
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
            Permission::CHAT_LIST
        ]) && is_object($subject) ? get_class($subject) === Chat::class : $subject === Chat::class;
    }
}