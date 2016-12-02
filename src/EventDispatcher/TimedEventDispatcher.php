<?php

namespace MiniSymfony\CompanionBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TimedEventDispatcher extends WildcardEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array
     */
    protected $timings = [];

    /**
     * Triggers the listeners of an event.
     *
     * This method can be overridden to add functionality that is executed
     * for each listener.
     *
     * @param callable[] $listeners The event listeners
     * @param string $eventName The name of the event to dispatch
     * @param Event $event The event object to pass to the event handlers/listeners
     */
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }

            $start = microtime(true);
            echo 1;
            call_user_func($listener, $event, $eventName, $this);

            $this->timings[$eventName][$listener] = [
                'start' => $start,
                'end'   => microtime(true),
            ];
        }
    }

    /**
     * @return array
     */
    public function getTimings()
    {
        return $this->timings;
    }
}
