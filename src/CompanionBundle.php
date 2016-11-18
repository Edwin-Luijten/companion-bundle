<?php

namespace MiniSymfony\CompanionBundle;

use MiniSymfony\CompanionBundle\DependencyInjection\CompanionExtension;
use MiniSymfony\CompanionBundle\DependencyInjection\Compilers\ContainerBuilderDebugDumpPass;
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
            $container->addCompilerPass(new ContainerBuilderDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
        }

        parent::build($container);
    }

    /**
     * @return CompanionExtension
     */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            return new CompanionExtension();
        }

        return $this->extension;
    }
}
