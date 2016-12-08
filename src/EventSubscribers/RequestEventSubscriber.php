<?php

namespace MiniSymfony\CompanionBundle\EventSubscribers;

use MiniSymfony\CompanionBundle\DebugBar\DebugBar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class RequestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var DebugBar
     */
    private $debugBar;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    private $kernel;

    /**
     * RequestEventSubscriber constructor.
     * @param DebugBar $debugBar
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(DebugBar $debugBar, EventDispatcherInterface $dispatcher, KernelInterface $kernel)
    {
        $this->debugBar = $debugBar;
        $this->dispatcher = $dispatcher;
        $this->kernel = $kernel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['onResponse', -128],
            ]
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$event->isMasterRequest()) {
            return;
        }

        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $this->debugBar->modifyResponse($request, $response, $this->dispatcher, $this->kernel);
    }

}
