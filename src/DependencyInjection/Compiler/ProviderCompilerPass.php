<?php

namespace Shivas\VersioningBundle\DependencyInjection\Compiler;

use Shivas\VersioningBundle\Service\VersionManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProviderCompilerPass
 */
final class ProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(VersionManagerInterface::class);
        $providers = $container->findTaggedServiceIds('shivas_versioning.provider');

        foreach ($providers as $id => $attributes) {
            $attributes = reset($attributes);
            $definition->addMethodCall('addProvider', [
                new Reference($id), $attributes['alias'], $attributes['priority'],
            ]);
        }
    }
}
