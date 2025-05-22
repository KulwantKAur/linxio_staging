<?php

namespace App\Security;

use App\Entity\FuelStation;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FuelStationVoter extends BaseVoter
{
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
                Permission::FUEL_STATION_CREATE,
                Permission::FUEL_STATION_EDIT,
                Permission::FUEL_STATION_DELETE,
                Permission::FUEL_STATION_LIST,
            ])
            && (is_object($subject) ? get_class($subject) === FuelStation::class : $subject === FuelStation::class);
    }
}
