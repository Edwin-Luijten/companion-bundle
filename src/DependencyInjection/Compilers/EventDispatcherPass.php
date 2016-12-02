<?php

namespace MiniSymfony\CompanionBundle\DependencyInjection\Compilers;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventDispatcherPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->setDefinition('event_dispatcher', $container->getDefinition('companion.event_dispatcher'));
        $container->removeDefinition('companion.event_dispatcher');
    }
}