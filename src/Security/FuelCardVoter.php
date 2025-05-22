<?php

namespace App\Security;

use App\Entity\FuelCard\FuelCard;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FuelCardVoter extends BaseVoter
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
                Permission::FUEL_SUMMARY,
                Permission::FUEL_RECORDS,
                Permission::FUEL_IMPORT_DATA,
                Permission::FUEL_FILE_UPDATE,
                Permission::FUEL_FILE_DELETE,
                Permission::FUEL_RECORD_UPDATE,
            ])
            && (is_object($subject) ? get_class($subject) === FuelCard::class : $subject === FuelCard::class);
    }
}
