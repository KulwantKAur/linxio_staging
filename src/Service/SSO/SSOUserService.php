<?php

namespace App\Service\SSO;

use App\Entity\User;
use App\Exceptions\SSOException;
use App\Service\Auth\AuthService;
use App\Service\BaseService;
use App\Service\SSO\Provider\SSOProvider;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SSOUserService extends BaseService
{
    public function __construct(
        private ?SSOProvider               $SSOProvider,
        protected EntityManagerInterface   $em,
        protected UserService              $userService,
        protected AuthService              $authService,
        protected JWTEncoderInterface      $JWTEncoder,
        protected EventDispatcherInterface $eventDispatcher,
        protected RouterInterface          $router,
        protected ValidatorInterface       $validator,
    ) {
    }

    public function setSSOProvider(?SSOProvider $SSOProvider)
    {
        $this->SSOProvider = $SSOProvider;
    }

    public function findUserByEmail(string $email): User
    {
        try {
            $user = $this->userService->findUserByEmail($email);
        } catch (BadCredentialsException $e) {
            throw new UserNotFoundException();
        } catch (\Exception $e) {
            throw new SSOException($e->getMessage());
        }

        return $user;
    }

    public function loginUser(User $user, Request $request): array
    {
        return $this->authService->login($user, $request, AuthService::LOGIN_TYPE_SSO);
    }

    public function createUser(string $username, array $attributes = []): UserInterface
    {
        $user = $this->SSOProvider->createUser($username, $attributes);
        $this->validate($this->validator, $user, ['sso']);
        $this->em->persist($user);

        if ($this->SSOProvider->isStatusBlocked()) {
            $this->userService->blockHandler($user, ['isBlocked' => true]);
        }

        $this->em->flush();

        return $user;
    }

    public function updateUser(User $user, array $attributes = []): UserInterface
    {
        $this->SSOProvider->initAttributes($attributes);
        $mappedAttributes = $this->SSOProvider->mapUserAttributes();
        $user->setAttributes($mappedAttributes);
        $this->validate($this->validator, $user, ['sso']);

        if ($this->SSOProvider->isStatusBlocked()) {
            $this->userService->blockHandler($user, ['isBlocked' => true]);
        }

        $this->em->flush();

        return $user;
    }
}
