<?php

namespace LaFourchette\FixturesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fixtures');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('groups')
                        ->prototype('array')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('dependencies')
                        ->prototype('array')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('providerClasses')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->scalarNode('fixturesDataProcessor')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
