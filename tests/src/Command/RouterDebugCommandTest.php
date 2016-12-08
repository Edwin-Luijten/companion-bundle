<?php

namespace MiniSymfony\CompanionBundle\Tests\Command;

use MiniSymfony\CompanionBundle\Command\RouterDebugCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterDebugCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testDebugAllRoutes()
    {
        $tester = $this->createCommandTester();
        $ret    = $tester->execute(['name' => null], ['decorated' => false]);
        $this->assertEquals(0, $ret, 'Returns 0 in case of success');
        $this->assertContains('Name   Method   Scheme   Host   Path', $tester->getDisplay());
    }

    public function testDebugSingleRoute()
    {
        $tester = $this->createCommandTester();
        $ret    = $tester->execute(['name' => 'foo'], ['decorated' => false]);
        $this->assertEquals(0, $ret, 'Returns 0 in case of success');
        $this->assertContains('Route Name   | foo', $tester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDebugInvalidRoute()
    {
        $this->createCommandTester()->execute(['name' => 'test']);
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester()
    {
        $application = new Application();
        $command     = new RouterDebugCommand();
        $command->setContainer($this->getContainer());
        $application->add($command);

        return new CommandTester($application->find('debug:router'));
    }

    private function getContainer()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('foo', new Route('foo'));
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router
            ->expects($this->any())
            ->method('getRouteCollection')
            ->will($this->returnValue($routeCollection));
        $loader    = $this->getMockBuilder('Symfony\Component\Config\Loader\DelegatingLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('has')
            ->with('router')
            ->will($this->returnValue(true));
        $container
            ->method('get')
            ->will($this->returnValueMap([
                ['router', 1, $router],
                ['controller_name_converter', 1, $loader],
            ]));

        return $container;
    }
}