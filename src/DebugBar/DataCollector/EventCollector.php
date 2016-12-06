<?php

namespace MiniSymfony\CompanionBundle\DebugBar\DataCollector;


use DebugBar\DataCollector\TimeDataCollector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\VarDumper\VarDumper;

class EventCollector extends TimeDataCollector
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var VarDumper */
    protected $exporter;

    /**
     * EventCollector constructor.
     * @param null $requestStartTime
     */
    public function __construct($requestStartTime = null, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($requestStartTime);

        $this->exporter   = new VarDumper();
        $this->dispatcher = $dispatcher;
    }

    public function collect()
    {
        foreach ($this->dispatcher->getTimings() as $event => $timings) {
            foreach ($timings as $handler => $timing) {
                $this->addMeasure($event . '@' . $handler, $timing['start'], $timing['end']);
            }
        }

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
                "map"     => "events",
                "default" => "{}",
            ],
            'events:badge' => [
                'map'     => 'events.nb_measures',
                'default' => 0,
            ],
        ];
    }
}