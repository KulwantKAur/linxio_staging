<?php

namespace App\DependencyInjection\CompilerPass;

use App\Service\Route\Driver\SetDriverExecutorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SetDriverExecutorCompilerPass implements CompilerPassInterface
{
    public const SET_DRIVER_EXECUTOR_TAG = 'set_driver.executor';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container) : void
    {
        $definition = $container->getDefinition(SetDriverExecutorRegistry::class);
        $taggedServices = $container->findTaggedServiceIds(self::SET_DRIVER_EXECUTOR_TAG);

        $services = [];
        foreach ($taggedServices as $id => $service) {
            $services[] = new Reference($id);
        }

        $definition->addMethodCall('addExecutors', [$services]);
    }
}
