<?php

namespace App\Service\SSO\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class SAMLAttributesBadge implements BadgeInterface
{
    /**
     * @param array $attributes
     */
    public function __construct(private array $attributes)
    {
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return bool
     */
    public function isResolved(): bool
    {
        return true;
    }
}
