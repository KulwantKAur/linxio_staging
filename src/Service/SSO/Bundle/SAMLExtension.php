<?php

namespace App\Service\SSO\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SAMLExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new SAMLConfiguration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('sso_saml.settings', $config);
    }

    public function getAlias(): string
    {
        return 'sso_saml';
    }
}