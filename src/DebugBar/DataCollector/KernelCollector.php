<?php

namespace MiniSymfony\CompanionBundle\DebugBar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelCollector extends DataCollector implements Renderable
{
    /** @var KernelInterface $app */
    protected $app;

    /**
     * @param KernelInterface $app
     */
    public function __construct(KernelInterface $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        return [
            "version" => Kernel::VERSION,
            "environment" => $this->app->getEnvironment(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'kenel';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "version" => [
                "icon" => "github",
                "tooltip" => "Version",
                "map" => "kenel.version",
                "default" => ""
            ],
            "environment" => [
                "icon" => "desktop",
                "tooltip" => "Environment",
                "map" => "kenel.environment",
                "default" => ""
            ],
        ];
    }
}