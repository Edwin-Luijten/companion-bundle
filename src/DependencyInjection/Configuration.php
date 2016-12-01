<?php

namespace MiniSymfony\CompanionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mini_symfony');
        $rootNode
            ->children()
                ->arrayNode('debug')
                    ->children()
                        ->scalarNode('container_dump_path')->end()
                        ->arrayNode('debugbar')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->arrayNode('collectors')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end() // debug
            ->end()
        ;

        return $treeBuilder;
    }
}