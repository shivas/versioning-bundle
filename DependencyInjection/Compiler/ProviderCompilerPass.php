<?php

namespace Shivas\VersioningBundle\DependencyInjection\Compiler;

use Shivas\VersioningBundle\Service\VersionManager;
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
        if (!$container->hasDefinition(VersionManager::class)) {
            return;
        }

        $definition = $container->getDefinition(VersionManager::class);
        $providers = $container->findTaggedServiceIds('shivas_versioning.provider');

        foreach ($providers as $id => $attributes) {
            $attributes = reset($attributes);
            $definition->addMethodCall('addProvider', [
                new Reference($id), $attributes['alias'], $attributes['priority'],
            ]);
        }
    }
}
