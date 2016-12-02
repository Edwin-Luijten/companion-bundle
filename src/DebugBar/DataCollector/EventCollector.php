<?php

namespace MiniSymfony\CompanionBundle\DebugBar\DataCollector;


use DebugBar\DataCollector\TimeDataCollector;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\VarDumper\VarDumper;

class EventCollector extends TimeDataCollector
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var VarDumper */
    protected $exporter;

    protected $events = [];
    /**
     * EventCollector constructor.
     * @param null $requestStartTime
     */
    public function __construct($requestStartTime = null)
    {
        parent::__construct($requestStartTime);

        $this->exporter   = new VarDumper();
    }

    public function onWildcardEvent(Event $event)
    {
        echo 1;
    }

    public function collect()
    {
        $data = parent::collect();

        $data['nb_measures'] = count($data['measures']);
        return $data;
    }

    public function getName()
    {
        return 'events';
    }

    public function getWidgets()
    {
        return [
            "events"       => [
                "icon"    => "tasks",
                "widget"  => "PhpDebugBar.Widgets.TimelineWidget",
                "map"     => "event",
                "default" => "{}",
            ],
            'events:badge' => [
                'map'     => 'event.nb_measures',
                'default' => 0,
            ],
        ];
    }
}