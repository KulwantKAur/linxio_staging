<?php

namespace App\Security;

use App\Entity\Reseller;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ResellerVoter extends BaseVoter
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
        return in_array(
                $attribute,
                [
                    Permission::RESELLER_USER_LIST,
                    Permission::RESELLER_TEAM_SECTION,
                    Permission::RESELLER_USER_DELETE,
                    Permission::RESELLER_SECTION,
                    Permission::RESELLER_USER_EDIT,
                    Permission::RESELLER_USER_NEW,
                    Permission::RESELLER_LIST,
                    Permission::RESELLER_DELETE,
                    Permission::RESELLER_EDIT,
                    Permission::RESELLER_NEW,
                    Permission::RESELLER_NOTES_HISTORY
                ]
            )
            && (is_object($subject) ? get_class($subject) === Reseller::class : $subject === Reseller::class);
    }
}