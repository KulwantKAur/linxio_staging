<?php

namespace App\Security;

use App\Entity\InspectionForm;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class InspectionFormVoter extends BaseVoter
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
                Permission::INSPECTION_FORM_FILL,
                Permission::INSPECTION_FORM_FILLED,
                Permission::INSPECTION_FORM_SET_TEAM,
            ])
            && (is_object($subject) ? get_class($subject) === InspectionForm::class : $subject === InspectionForm::class);
    }
}