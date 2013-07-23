<?php
namespace Shivas\VersioningBundle;

use Shivas\VersioningBundle\DependencyInjection\Compiler\HandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShivasVersioningBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new HandlerCompilerPass());
    }
}

