<?php

namespace App\Service\SSO\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SSOSAMLBundle extends Bundle
{
    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        $this->extension = $this->extension ?: new SAMLExtension();

        return $this->extension;
    }
}
