<?php

namespace App\Security;

use App\Entity\PlanRolePermission;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseVoter extends Voter
{
    protected $em;
    protected $translator;

    public function __construct(EntityManager $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($user instanceof User && !count($user->getPermissions())) {
            $permissions = $this->em->getRepository(PlanRolePermission::class)->getUserPermissions($user);
            $user->setPermissions($permissions);
        }

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return true;
        }

        return true;
    }

    protected function supports($attribute, $subject): bool
    {
        return true;
    }

}