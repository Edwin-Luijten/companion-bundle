<?php

namespace MiniSymfony\CompanionBundle;

use MiniSymfony\CompanionBundle\DependencyInjection\CompanionExtension;
use MiniSymfony\CompanionBundle\DependencyInjection\Compilers\ContainerBuilderDebugDumpPass;
use MiniSymfony\CompanionBundle\DependencyInjection\Compilers\DebugBarPass;
use MiniSymfony\CompanionBundle\DependencyInjection\Compilers\EventDispatcherPass;
use MiniSymfony\CompanionBundle\DependencyInjection\Compilers\InnerEventDispatcherPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CompanionBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new InnerEventDispatcherPass());
            $container->addCompilerPass(new EventDispatcherPass());
            $container->addCompilerPass(new ContainerBuilderDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
            $container->addCompilerPass(new DebugBarPass());
        }

        parent::build($container);
    }

    /**
     * @return CompanionExtension
     */
    public function getContainerExtension()
    {
        return new CompanionExtension();
    }
}
