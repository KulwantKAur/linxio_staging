<?php

namespace App\Security;

use App\Entity\Permission;
use App\Entity\ServiceRecord;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ServiceRecordVoter extends BaseVoter
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
                Permission::VEHICLE_SERVICE_RECORD_NEW,
                Permission::VEHICLE_SERVICE_RECORD_DELETE,
                Permission::VEHICLE_SERVICE_RECORD_LIST,
                Permission::REPAIR_COST_NEW,
                Permission::REPAIR_COST_EDIT,
                Permission::REPAIR_COST_DELETE,
                Permission::REPAIR_COST_LIST,
            ])
            && (is_object($subject) ? get_class($subject) === ServiceRecord::class : $subject === ServiceRecord::class);
    }
}