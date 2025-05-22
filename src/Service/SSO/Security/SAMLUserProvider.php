<?php

namespace App\Service\SSO\Security;

use App\Entity\User;
use App\Exceptions\SSOException;
use App\Service\SSO\SSOUserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SAMLUserProvider implements UserProviderInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SSOUserService         $userService,
    ) {
    }

    public function loadUserByIdentifier($identifier): UserInterface
    {
        return $this->userService->findUserByEmail($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new SSOException('', Response::HTTP_BAD_REQUEST, new UnsupportedUserException());
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
