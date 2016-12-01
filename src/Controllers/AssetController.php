<?php

namespace MiniSymfony\CompanionBundle\Controllers;

use MiniSymfony\CompanionBundle\Debugbar\DebugBar;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

class AssetController
{
    private $renderer;

    private $router;

    public function __construct(DebugBar $debugBar, Router $router)
    {
        $this->renderer = $debugBar->getJavascriptRenderer();
        $this->router   = $router;
    }

    /**
     * @return Response
     */
    public function getJs()
    {
        $content = $this->dumpAssetsToString('js');

        $response = new Response(
            $content, 200, [
                'Content-Type' => 'text/javascript',
            ]
        );

        return $this->cacheResponse($response);
    }

    /**
     * @return Response
     */
    public function getCss()
    {
        $content  = $this->dumpAssetsToString('css');

        $response = new Response(
            $content, 200, [
                'Content-Type' => 'text/css',
            ]
        );

        return $this->cacheResponse($response);
    }

    /**
     * Return assets as a string
     *
     * @param string $type 'js' or 'css'
     * @return string
     */
    public function dumpAssetsToString($type)
    {
        $files   = $this->renderer->getAssets($type);
        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }

    /**
     * Cache the response 1 year (31536000 sec)
     */
    private function cacheResponse(Response $response)
    {
        $response->setSharedMaxAge(31536000);
        $response->setMaxAge(31536000);
        $response->setExpires(new \DateTime('+1 year'));

        return $response;
    }
}