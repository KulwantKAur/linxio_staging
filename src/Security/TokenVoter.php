<?php

namespace App\Security;

use App\Entity\TokenBlacklist;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenVoter extends BaseVoter
{
    public static $cache = [];

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if ($user instanceof User && $user->isInClientTeam()
            && ($user->getClient()->isClosed() || $user->getClient()->isBlocked())) {
            throw new AuthenticationException($this->translator->trans('entities.client.blocked'));
        }

//        if (!(self::$cache['token'] ?? null)) {
//            if ($this->em->getRepository(TokenBlacklist::class)->findOneBy(['token' => $token->getCredentials()])) {
//                throw new AuthenticationException();
//            }
//            self::$cache['token'] = true;
//        }

        return true;
    }

    protected function supports($attribute, $subject): bool
    {
        return true;
    }

}