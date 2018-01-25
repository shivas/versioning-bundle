<?php
namespace Shivas\VersioningBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('shivas_versioning');

        $rootNode
            ->children()
                ->scalarNode('version_parameter')
                    ->defaultValue('application_version')
                ->end()
                ->scalarNode('version_file')
                    ->defaultValue('parameters.yaml')
                ->end()
                ->scalarNode(('version_formatter'))
                    ->defaultValue('shivas_versioning.formatters.git')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
