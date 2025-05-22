<?php

namespace App\Security;

use App\Entity\Document;
use App\Entity\Permission;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DocumentVoter extends BaseVoter
{
    /**
     * @param $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool|object|string
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
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
                Permission::VEHICLE_DOCUMENT_LIST,
                Permission::VEHICLE_DOCUMENT_NEW,
                Permission::VEHICLE_DOCUMENT_EDIT,
                Permission::VEHICLE_DOCUMENT_DELETE,
                Permission::VEHICLE_DOCUMENT_ARCHIVE,
                Permission::VEHICLE_DOCUMENT_RECORD_NEW,
                Permission::VEHICLE_DOCUMENT_RECORD_LIST,
                Permission::DRIVER_DOCUMENT_LIST,
                Permission::DRIVER_DOCUMENT_NEW,
                Permission::DRIVER_DOCUMENT_EDIT,
                Permission::DRIVER_DOCUMENT_DELETE,
                Permission::DRIVER_DOCUMENT_ARCHIVE,
                Permission::DRIVER_DOCUMENT_RECORD_NEW,
                Permission::DRIVER_DOCUMENT_RECORD_LIST,
                Permission::ASSET_DOCUMENT_LIST,
                Permission::ASSET_DOCUMENT_NEW,
                Permission::ASSET_DOCUMENT_EDIT,
                Permission::ASSET_DOCUMENT_DELETE,
                Permission::ASSET_DOCUMENT_ARCHIVE,
                Permission::ASSET_DOCUMENT_RECORD_NEW,
                Permission::ASSET_DOCUMENT_RECORD_LIST,
            ], true)
            && (is_object($subject) ? get_class($subject) === Document::class : $subject === Document::class);
    }
}