<?php

namespace App\Service\SSO\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class SAMLToken extends PostAuthenticationToken implements TokenInterface
{
    /**
     * @param UserInterface $user
     * @param string $firewallName
     * @param array $roles
     * @param array $attributes
     */
    public function __construct(UserInterface $user, string $firewallName, array $roles, array $attributes)
    {
        parent::__construct($user, $firewallName, $roles);
        $this->setAttributes($attributes);
    }
}
