<?php
namespace Shivas\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('shivas_versioning.manager')) {
            return;
        }

        $definition = $container->getDefinition('shivas_versioning.manager');
        $handlers = $container->findTaggedServiceIds('shivas_versioning.handler');

        foreach ($handlers as $id => $attributes) {
            $attributes = reset($attributes);
            $definition->addMethodCall(
                'addHandler',
                array(new Reference($id), $attributes['alias'], $attributes['priority'])
            );
        }
    }
}

