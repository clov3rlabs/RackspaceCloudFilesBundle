<?php

namespace Clov3rLabs\RackspaceCloudFilesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('clov3r_labs_rackspace_cloud_files');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->arrayNode('container')
                    ->children()
                        ->scalarNode('name')->defaultValue(null)->end()
                        ->scalarNode('region')->defaultValue(null)->end()   # Can be DFW o ORD
                    ->end()
                ->end()
                ->arrayNode('auth')
                    ->children()
                        ->scalarNode('apikey')->defaultValue(null)->end()
                        ->scalarNode('username')->defaultValue(null)->end()
                        ->scalarNode('endpoint')->defaultValue('US')->end()     # Can be US (default) or UK
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
