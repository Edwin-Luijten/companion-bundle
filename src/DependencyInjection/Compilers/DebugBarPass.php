<?php

namespace MiniSymfony\CompanionBundle\DependencyInjection\Compilers;

use DebugBar\DataCollector\TimeDataCollector;
use MiniSymfony\CompanionBundle\DebugBar\DataCollector\ContainerCollector;
use MiniSymfony\CompanionBundle\DebugBar\DebugBar;
use MiniSymfony\CompanionBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DebugBarPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../Resources/config'));
        $loader->load('config.yml');

        $configs = $container->getExtensionConfig('mini_symfony');

        $processor = new Processor();
        $config    = $processor->processConfiguration(new Configuration(), [$configs[1], $configs[0]]);

        if ($config['debug']['debugbar']['enabled'] === true) {
            $definition = new Definition(DebugBar::class);
            $definition->addArgument($container->getDefinition('router'));
            $definition->addArgument($config['debug']['debugbar']['collectors']);
            $definition->addMethodCall('boot', [$container->getDefinition('dbal')]);
            $container->setDefinition('debugbar', $definition);
        }
    }
}