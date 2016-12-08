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
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->booleanNode('include_vendors')->defaultTrue()->end()
                                ->booleanNode('capture_ajax')->defaultTrue()->end()
                                ->booleanNode('clockwork')->defaultFalse()->end()
                                ->arrayNode('collectors')
                                    ->children()
                                        ->booleanNode('events')->defaultFalse()->end()
                                        ->booleanNode('exceptions')->defaultFalse()->end()
                                        ->booleanNode('request')->defaultFalse()->end()
                                        ->booleanNode('routing')->defaultFalse()->end()
                                        ->booleanNode('phpinfo')->defaultFalse()->end()
                                        ->booleanNode('kernel')->defaultFalse()->end()
                                        ->booleanNode('time')->defaultFalse()->end()
                                        ->booleanNode('memory')->defaultFalse()->end()
                                        ->booleanNode('queries')->defaultFalse()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('options')
                                    ->children()
                                        ->arrayNode('queries')
                                            ->children()
                                                ->booleanNode('with_params')->defaultFalse()->end()
                                                ->booleanNode('timeline')->defaultFalse()->end()
                                                ->arrayNode('explain')
                                                    ->children()
                                                        ->booleanNode('enabled')->defaultFalse()->end()
                                                        ->arrayNode('types')
                                                            ->prototype('scalar')->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                                ->booleanNode('hints')->defaultFalse()->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('routing')
                                            ->children()
                                                ->booleanNode('label')->defaultFalse()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->scalarNode('route_prefix')->defaultValue('_debugbar')->end()
                            ->end()
                        ->end()
                    ->end() // debug
            ->end()
        ;

        return $treeBuilder;
    }
}