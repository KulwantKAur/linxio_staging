<?php

namespace App\Service\UserTerms;

use App\Entity\User;
use App\Entity\UserTermAcceptance;
use App\Service\BaseService;
use Doctrine\ORM\EntityManager;

class UserTermsService extends BaseService
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function create(string $type, User $currentUser): UserTermAcceptance
    {
        $userTermsAcceptance = $this->em->getRepository(UserTermAcceptance::class)
            ->findOneBy(['type' => $type, 'user' => $currentUser]);

        if ($userTermsAcceptance) {
            return $userTermsAcceptance;
        }

        $userTermsAcceptance = new UserTermAcceptance(
            [
                'type' => $type,
                'user' => $currentUser,
                'createdAt' => new \DateTime()
            ]
        );

        $this->em->persist($userTermsAcceptance);
        $this->em->flush();

        return $userTermsAcceptance;
    }
}
