<?php

namespace App\Security;

use App\Entity\FuelType\FuelIgnoreList;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FuelIgnoreListVoter extends BaseVoter
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
                Permission::FUEL_IGNORE_NEW,
                Permission::FUEL_IGNORE_EDIT,
                Permission::FUEL_IGNORE_DELETE,
                Permission::FUEL_IGNORE_LIST
            ])
            && (is_object($subject) ? get_class($subject) === FuelIgnoreList::class : $subject === FuelIgnoreList::class);
    }
}