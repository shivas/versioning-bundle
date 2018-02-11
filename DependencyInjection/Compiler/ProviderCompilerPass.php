<?php

namespace Shivas\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProviderCompilerPass
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('Shivas\VersioningBundle\Service\VersionManager')) {
            return;
        }

        $definition = $container->getDefinition('Shivas\VersioningBundle\Service\VersionManager');
        $providers = $container->findTaggedServiceIds('shivas_versioning.provider');

        foreach ($providers as $id => $attributes) {
            $attributes = reset($attributes);
            $definition->addMethodCall('addProvider', [
                new Reference($id), $attributes['alias'], $attributes['priority'],
            ]);
        }
    }
}
