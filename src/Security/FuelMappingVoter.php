<?php

namespace App\Security;

use App\Entity\FuelType\FuelMapping;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FuelMappingVoter extends BaseVoter
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
                Permission::FUEL_MAPPING_NEW,
                Permission::FUEL_MAPPING_EDIT,
                Permission::FUEL_MAPPING_DELETE,
                Permission::FUEL_MAPPING_LIST,
                Permission::FUEL_TYPES_LIST,
            ])
            && (is_object($subject) ? get_class($subject) === FuelMapping::class : $subject === FuelMapping::class);
    }
}