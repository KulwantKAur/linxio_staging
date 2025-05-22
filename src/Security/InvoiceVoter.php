<?php

namespace App\Security;

use App\Entity\Invoice;
use App\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class InvoiceVoter extends BaseVoter
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
                Permission::BILLING_INVOICE_VIEW,
                Permission::BILLING_INVOICE_PAY,
                Permission::BILLING_INVOICE_CLEAN,
            ])
            && (is_object($subject) ? get_class($subject) === Invoice::class : $subject === Invoice::class);
    }
}