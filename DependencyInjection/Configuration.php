<?php

namespace KRG\EasyAdminExtensionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('krg_easy_admin_extension');

        $rootNode
            ->children()
                ->arrayNode('dashboard')
                    ->children()
                        ->arrayNode('widgets')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('route')->end()
                                    ->arrayNode('params')->scalarPrototype()->defaultValue([])->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode
            ->children()
                ->arrayNode('google_analytics')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('client_id')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}
