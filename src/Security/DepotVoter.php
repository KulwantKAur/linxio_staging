<?php

namespace App\Security;

use App\Entity\Depot;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DepotVoter extends BaseVoter
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
                Permission::DEPOT_NEW,
                Permission::DEPOT_EDIT,
                Permission::DEPOT_DELETE,
                Permission::DEPOT_ARCHIVE
            ])
            && (is_object($subject) ? get_class($subject) === Depot::class : $subject === Depot::class);
    }
}