<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mvc\Router;

use ArrayIterator;
use Laminas\Mvc\Router\RoutePluginManager;
use Laminas\Mvc\Router\SimpleRouteStack;
use Laminas\Stdlib\Request;
use LaminasTest\Mvc\Router\FactoryTester;
use PHPUnit_Framework_TestCase as TestCase;

class SimpleRouteStackTest extends TestCase
{
    public function testSetRoutePluginManager()
    {
        $routes = new RoutePluginManager();
        $stack  = new SimpleRouteStack();
        $stack->setRoutePluginManager($routes);

        $this->assertEquals($routes, $stack->getRoutePluginManager());
    }

    public function testAddRoutesWithInvalidArgument()
    {
        $this->setExpectedException('Laminas\Mvc\Router\Exception\InvalidArgumentException', 'addRoutes expects an array or Traversable set of routes');
        $stack = new SimpleRouteStack();
        $stack->addRoutes('foo');
    }

    public function testAddRoutesAsArray()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoutes(array(
            'foo' => new TestAsset\DummyRoute()
        ));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));
    }

    public function testAddRoutesAsTraversable()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoutes(new ArrayIterator(array(
            'foo' => new TestAsset\DummyRoute()
        )));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));
    }

    public function testSetRoutesWithInvalidArgument()
    {
        $this->setExpectedException('Laminas\Mvc\Router\Exception\InvalidArgumentException', 'addRoutes expects an array or Traversable set of routes');
        $stack = new SimpleRouteStack();
        $stack->setRoutes('foo');
    }

    public function testSetRoutesAsArray()
    {
        $stack = new SimpleRouteStack();
        $stack->setRoutes(array(
            'foo' => new TestAsset\DummyRoute()
        ));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));

        $stack->setRoutes(array());

        $this->assertSame(null, $stack->match(new Request()));
    }

    public function testSetRoutesAsTraversable()
    {
        $stack = new SimpleRouteStack();
        $stack->setRoutes(new ArrayIterator(array(
            'foo' => new TestAsset\DummyRoute()
        )));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));

        $stack->setRoutes(new ArrayIterator(array()));

        $this->assertSame(null, $stack->match(new Request()));

    }

    public function testremoveRouteAsArray()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoutes(array(
            'foo' => new TestAsset\DummyRoute()
        ));

        $this->assertEquals($stack, $stack->removeRoute('foo'));
        $this->assertNull($stack->match(new Request()));
    }

    public function testAddRouteWithInvalidArgument()
    {
        $this->setExpectedException('Laminas\Mvc\Router\Exception\InvalidArgumentException', 'Route definition must be an array or Traversable object');
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', 'bar');
    }

    public function testAddRouteAsArrayWithoutOptions()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', array(
            'type' => '\LaminasTest\Mvc\Router\TestAsset\DummyRoute'
        ));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));
    }

    public function testAddRouteAsArrayWithOptions()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', array(
            'type'    => '\LaminasTest\Mvc\Router\TestAsset\DummyRoute',
            'options' => array()
        ));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));
    }

    public function testAddRouteAsArrayWithoutType()
    {
        $this->setExpectedException('Laminas\Mvc\Router\Exception\InvalidArgumentException', 'Missing "type" option');
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', array());
    }

    public function testAddRouteAsArrayWithPriority()
    {
        $stack = new SimpleRouteStack();

        $stack->addRoute('foo', array(
            'type'     => '\LaminasTest\Mvc\Router\TestAsset\DummyRouteWithParam',
            'priority' => 2
        ))->addRoute('bar', array(
            'type'     => '\LaminasTest\Mvc\Router\TestAsset\DummyRoute',
            'priority' => 1
        ));

        $this->assertEquals('bar', $stack->match(new Request())->getParam('foo'));
    }

    public function testAddRouteWithPriority()
    {
        $stack = new SimpleRouteStack();

        $route = new TestAsset\DummyRouteWithParam();
        $route->priority = 2;
        $stack->addRoute('baz', $route);

        $stack->addRoute('foo', array(
            'type'     => '\LaminasTest\Mvc\Router\TestAsset\DummyRoute',
            'priority' => 1
        ));

        $this->assertEquals('bar', $stack->match(new Request())->getParam('foo'));
    }

    public function testAddRouteAsTraversable()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', new ArrayIterator(array(
            'type' => '\LaminasTest\Mvc\Router\TestAsset\DummyRoute'
        )));

        $this->assertInstanceOf('Laminas\Mvc\Router\RouteMatch', $stack->match(new Request()));
    }

    public function testAssemble()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', new TestAsset\DummyRoute());
        $this->assertEquals('', $stack->assemble(array(), array('name' => 'foo')));
    }

    public function testAssembleWithoutNameOption()
    {
        $this->setExpectedException('Laminas\Mvc\Router\Exception\InvalidArgumentException', 'Missing "name" option');
        $stack = new SimpleRouteStack();
        $stack->assemble();
    }

    public function testAssembleNonExistentRoute()
    {
        $this->setExpectedException('Laminas\Mvc\Router\Exception\RuntimeException', 'Route with name "foo" not found');
        $stack = new SimpleRouteStack();
        $stack->assemble(array(), array('name' => 'foo'));
    }

    public function testDefaultParamIsAddedToMatch()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', new TestAsset\DummyRoute());
        $stack->setDefaultParam('foo', 'bar');

        $this->assertEquals('bar', $stack->match(new Request())->getParam('foo'));
    }

    public function testDefaultParamDoesNotOverrideParam()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', new TestAsset\DummyRouteWithParam());
        $stack->setDefaultParam('foo', 'baz');

        $this->assertEquals('bar', $stack->match(new Request())->getParam('foo'));
    }

    public function testDefaultParamIsUsedForAssembling()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', new TestAsset\DummyRouteWithParam());
        $stack->setDefaultParam('foo', 'bar');

        $this->assertEquals('bar', $stack->assemble(array(), array('name' => 'foo')));
    }

    public function testDefaultParamDoesNotOverrideParamForAssembling()
    {
        $stack = new SimpleRouteStack();
        $stack->addRoute('foo', new TestAsset\DummyRouteWithParam());
        $stack->setDefaultParam('foo', 'baz');

        $this->assertEquals('bar', $stack->assemble(array('foo' => 'bar'), array('name' => 'foo')));
    }

    public function testFactory()
    {
        $tester = new FactoryTester($this);
        $tester->testFactory(
            'Laminas\Mvc\Router\SimpleRouteStack',
            array(),
            array(
                'route_plugins'  => new RoutePluginManager(),
                'routes'         => array(),
                'default_params' => array()
            )
        );
    }
}
