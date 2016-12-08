<?php

namespace MiniSymfony\CompanionBundle\DebugBar;

use DebugBar\JavascriptRenderer as BaseJavascriptRenderer;
use Symfony\Component\Routing\Router;

class JavascriptRenderer extends BaseJavascriptRenderer
{
    /**
     * @var Router
     */
    private $router;

    /**
     * JavascriptRenderer constructor.
     * @param DebugBar $debugBar
     * @param null|string $baseUrl
     * @param null|string $basePath
     * @param Router $router
     */
    public function __construct(DebugBar $debugBar, Router $router, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);

        $this->router                    = $router;
        $this->cssFiles['companion']     = __DIR__ . '/../Resources/assets/debugbar.css';
        $this->cssVendors['fontawesome'] = __DIR__ . '/../Resources/assets/vendor/font-awesome/style.css';
    }

    /**
     * @return string
     */
    public function renderAssets()
    {
        $cssRoute = $this->router->generate('companion_bundle_debugbar_css', [
            'v' => $this->getModifiedTime('css')
        ], Router::ABSOLUTE_URL);
        $jsRoute  = $this->router->generate('companion_bundle_debugbar_js', [
            'v' => $this->getModifiedTime('js')
        ], Router::ABSOLUTE_URL);

        $html = '<link rel="stylesheet" type="text/css" property="stylesheet" href="' . $cssRoute . '"/>' . PHP_EOL;
        $html .= '<script type="text/javascript" src="' . $jsRoute . '"></script>' . PHP_EOL;

        return $html;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     * @return int
     */
    protected function getModifiedTime($type)
    {
        $files  = $this->getAssets($type);
        $latest = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $latest) {
                $latest = $mtime;
            }
        }

        return $latest;
    }
}