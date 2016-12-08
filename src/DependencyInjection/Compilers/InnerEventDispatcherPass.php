<?php

namespace MiniSymfony\CompanionBundle\DependencyInjection\Compilers;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InnerEventDispatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasAlias('event_dispatcher')) {
            $container->setAlias(
                'companion.event_dispatcher.inner',
                new Alias((string)$container->getAlias('event_dispatcher'), false)
            );
        } else {
            $definition = $container->getDefinition('event_dispatcher');
            $definition->setPublic(false);
            $container->setDefinition('companion.event_dispatcher.inner', $definition);
        }
    }
}