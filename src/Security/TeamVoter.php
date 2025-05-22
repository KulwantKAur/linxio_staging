<?php

namespace App\Security;

use App\Entity\Team;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\Common\Util\ClassUtils;

class TeamVoter extends BaseVoter
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

        if (is_object($subject)) {
            return
                $user->isAllTeamsPermissions()
                || $user->hasTeamPermission($subject->getId())
                || $user->getTeam()->getId() === $subject->getId()
                || ($user->isInResellerTeam() && $subject->isClientTeam()
                    && $subject->getClient()?->getOwnerTeam()->getId() === $user->getTeamId());
        }

        return false;
    }

    /**
     * @param $attribute
     * @param $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return is_object($subject) ?  ClassUtils::getClass($subject) === Team::class : $subject === Team::class;
    }
}