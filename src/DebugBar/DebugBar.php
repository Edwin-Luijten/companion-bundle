<?php

namespace MiniSymfony\CompanionBundle\DebugBar;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar as BaseDebugBar;
use MiniSymfony\CompanionBundle\DebugBar\DataCollector\EventCollector;
use MiniSymfony\CompanionBundle\DebugBar\DataCollector\KernelCollector;
use MiniSymfony\CompanionBundle\DebugBar\DataCollector\QueryCollector;
use MiniSymfony\CompanionBundle\DebugBar\DataCollector\RouteCollector;
use MiniSymfony\CompanionBundle\DebugBar\DataCollector\SymfonyRequestCollector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;

class DebugBar extends BaseDebugBar
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $enabledCollectors;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $dbalConnection;

    /**
     * DebugBar constructor.
     * @param Router $router
     * @param array $config
     */
    public function __construct(Router $router, array $config)
    {
        $this->router            = $router;
        $this->config            = $config;
        $this->enabledCollectors = $config['collectors'];
    }

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function setDbalConnection(\Doctrine\DBAL\Connection $connection)
    {
        $this->dbalConnection = $connection;
    }

    public function boot()
    {
        $debugbar = $this;

        if ($this->shouldCollect('phpinfo')) {
            $this->addCollector(new PhpInfoCollector());
        }

        if ($this->shouldCollect('memory')) {
            $this->addCollector(new MemoryCollector());
        }

        if ($this->shouldCollect('time')) {
            if (!$this->hasCollector('time')) {
                $this->addCollector(new TimeDataCollector());
            }

            $debugbar->startMeasure('application', 'Application');
        }

        if ($this->shouldCollect('exceptions')) {
            try {
                $exceptionCollector = new ExceptionsCollector();
                $exceptionCollector->setChainExceptions(true);
                $this->addCollector($exceptionCollector);
            } catch (\Exception $e) {
            }
        }

        if ($this->shouldCollect('queries')) {
            if (!empty($this->dbalConnection)) {
                $options = $this->config['options']['queries'];

                $debugStack = $this->dbalConnection->getConfiguration()->getSQLLogger();

                if (!empty($debugStack)) {
                    if ($this->hasCollector('time') && $options['timeline']) {
                        $timeCollector = $debugbar->getCollector('time');
                    } else {
                        $timeCollector = new TimeDataCollector();
                    }

                    $collector = new QueryCollector($timeCollector);

                    if ($options['with_params']) {
                        $collector->setRenderSqlWithParams(true);
                    }

                    if ($options['explain']['enabled']) {
                        $collector->setExplainSource(true, $options['explain']['types']);
                    }

                    if ($options['hints']) {
                        $collector->setShowHints(true);
                    }

                    if (!empty($debugStack->queries)) {
                        foreach ($debugStack->queries as $query) {
                            $collector->addQuery(
                                $query['sql'],
                                $query['params'],
                                $query['executionMS'],
                                $this->dbalConnection
                            );
                        }
                    }

                    $this->addCollector($collector);
                }
            }
        }
    }

    /**
     * Starts a measure
     *
     * @param string $name Internal name, used to stop the measure
     * @param string $label Public name
     */
    public function startMeasure($name, $label = null)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->startMeasure($name, $label);
        }
    }

    /**
     * Adds a measure
     *
     * @param string $label
     * @param float $start
     * @param float $end
     */
    public function addMeasure($label, $start, $end)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->addMeasure($label, $start, $end);
        }
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param string $label
     * @param \Closure $closure
     */
    public function measure($label, \Closure $closure)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            $collector->measure($label, $closure);
        } else {
            $closure();
        }
    }

    /**
     * Stops a measure
     *
     * @param string $name
     */
    public function stopMeasure($name)
    {
        if ($this->hasCollector('time')) {
            /** @var \DebugBar\DataCollector\TimeDataCollector $collector */
            $collector = $this->getCollector('time');
            try {
                $collector->stopMeasure($name);
            } catch (\Exception $e) {
                //  $this->addThrowable($e);
            }
        }
    }

    /**
     * @param $collector
     * @return bool
     */
    public function shouldCollect($collector)
    {
        if (!isset($this->enabledCollectors[$collector])) {
            return false;
        }

        return $this->enabledCollectors[$collector];
    }

    /**
     * Returns a JavascriptRenderer for this instance
     * @param string $baseUrl
     * @param string $basePath
     * @return JavascriptRenderer
     */
    public function getJavascriptRenderer($baseUrl = null, $basePath = null)
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $this->router, $baseUrl, $basePath);
        }

        return $this->jsRenderer;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function modifyResponse(
        Request $request,
        Response $response,
        EventDispatcherInterface $dispatcher,
        KernelInterface $kernel
    ) {
        // Late bindings

        if ($this->shouldCollect('request')) {
            $this->addCollector(new SymfonyRequestCollector($request, $response));
        }

        if ($this->shouldCollect('routing')) {
            $this->addCollector(new RouteCollector($request, $this->router));
        }

        if ($this->shouldCollect('events')) {
            if ($this->hasCollector('time')) {
                $timeCollector = $this->getCollector('time');
            } else {
                $timeCollector = new TimeDataCollector();
            }
            $this->addCollector(new EventCollector($_SERVER['REQUEST_TIME_FLOAT'], $dispatcher, $timeCollector));
        }

        if ($this->shouldCollect('kernel')) {
            $this->addCollector(new KernelCollector($kernel));
        }

        $this->inject($response);

        return $response;
    }

    /**
     * @param Response $response
     */
    public function inject(Response $response)
    {
        $content = $response->getContent();

        $renderer        = $this->getJavascriptRenderer();
        $renderedContent = $renderer->renderAssets() . $renderer->render();

        $pos = strripos($content, '</body>');
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . $renderedContent . substr($content, $pos);
        }
        // Update the new content and reset the content length
        $response->setContent($content);
        $response->headers->remove('Content-Length');
    }
}