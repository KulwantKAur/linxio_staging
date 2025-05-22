<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\TrackLink;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrackLinkVoter extends BaseVoter
{
    /**
     * @param $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool|object|string
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
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
        return in_array(
                $attribute,
                [
                    Permission::TRACK_LINK_CREATE
                ]
            )
            && (is_object($subject) ? get_class($subject) === TrackLink::class : $subject === TrackLink::class);
    }
}