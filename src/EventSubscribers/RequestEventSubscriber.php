<?php

namespace MiniSymfony\CompanionBundle\EventSubscribers;

use MiniSymfony\CompanionBundle\DebugBar\DebugBar;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var DebugBar
     */
    private $debugBar;

    /**
     * RequestEventSubscriber constructor.
     * @param DebugBar $debugBar
     */
    public function __construct(DebugBar $debugBar)
    {
        $this->debugBar = $debugBar;
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

        $this->debugBar->modifyResponse($request, $response);
    }

}
