<?php

namespace MiniSymfony\CompanionBundle\DebugBar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

class RouteCollector extends DataCollector implements Renderable
{
    /**
     * @var $request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * RouteCollector constructor.
     * @param Router $router
     */
    public function __construct(Request $request, Router $router)
    {
        $this->request = $request;
        $this->router  = $router;
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        /** @var Route $route */
        $name = $this->request->get('_route');
        $route = $this->router->getRouteCollection()->get($name);

        if ($route === null) {
            return;
        }

        $result = [
            'uri' => $this->router->generate($name, $this->request->attributes->get('_route_params'), Router::ABSOLUTE_URL),
            'path' => $this->request->getMethod() . ' ' . $route->getPath() ?: '-',
            'name' => $name,
            'methods' => ($route->getMethods()?: 'ANY'),
            'options' => $route->getOptions(),
            'defaults' => $route->getDefaults(),
            'requirements' => $route->getRequirements(),
        ];

        foreach ($result as $key => $var) {
            if (!is_string($result[$key])) {
                $result[$key] = $this->formatVar($var);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        $widgets = [
            "routing" => [
                "icon"    => "share",
                "widget"  => "PhpDebugBar.Widgets.VariableListWidget",
                "map"     => "route",
                "default" => "{}"
            ]
        ];

        $widgets['currentroute'] = [
            "icon"    => "share",
            "tooltip" => "Routing",
            "map"     => "route.path",
            "default" => ""
        ];

        return $widgets;
    }
}