<?php

namespace App\Security;

use App\Entity\DigitalForm;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DigitalFormVoter extends BaseVoter
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
                Permission::DIGITAL_FORM_LIST,
                Permission::DIGITAL_FORM_VIEW,
                Permission::DIGITAL_FORM_ANSWER_VIEW,
                Permission::DIGITAL_FORM_ANSWER_CREATE,
                Permission::VEHICLE_INSPECTION_FORM_LIST,
                Permission::VEHICLE_INSPECTION_FORM_CREATE,
                Permission::VEHICLE_INSPECTION_FORM_EDIT,
                Permission::VEHICLE_INSPECTION_FORM_DELETE
            ])
            && (is_object($subject) ? get_class($subject) === DigitalForm::class : $subject === DigitalForm::class);
    }
}