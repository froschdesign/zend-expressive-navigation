<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Navigation\Middleware;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Navigation\Middleware\NavigationMiddleware;
use Zend\Expressive\Navigation\Middleware\NavigationMiddlewareFactory;
use Zend\Navigation\Navigation;

class NavigationMiddlewareFactoryTest extends TestCase
{
    /**
     * @var NavigationMiddlewareFactory
     */
    private $factory;

    /**
     * @var Navigation
     */
    private $navigation;

    protected function setUp()
    {
        // Create factory
        $this->factory = new NavigationMiddlewareFactory();

        // Create navigation
        $this->navigation = new Navigation();
    }

    public function testFactoryWithMultipleNavigations()
    {
        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn([
            'navigation' => [
                'default' => [
                    [
                        'route' => 'home',
                    ],
                ],
                'special' => [
                    [
                        'route' => 'home',
                    ],
                ],
            ],
        ]);
        $prophecy->get('Zend\Navigation\Default')->willReturn(
            $this->navigation
        );
        $prophecy->get('Zend\Navigation\Special')->willReturn(
            $this->navigation
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $this->assertInstanceOf(
            NavigationMiddleware::class,
            $factory($container)
        );
    }

    public function testFactoryWithOneNavigation()
    {
        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn([
            'navigation' => [
                'default' => [
                    [
                        'route' => 'home',
                    ],
                ],
            ],
        ]);
        $prophecy->get(Navigation::class)->willReturn(
            $this->navigation
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $this->assertInstanceOf(
            NavigationMiddleware::class,
            $factory($container)
        );
    }

    public function testFactoryWithoutConfigShouldReturnMiddleware()
    {
        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(false);
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $this->assertInstanceOf(
            NavigationMiddleware::class,
            $factory($container)
        );
    }

    public function testFactoryWithoutNavigationConfigShouldReturnMiddleware()
    {
        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn([]);
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $this->assertInstanceOf(
            NavigationMiddleware::class,
            $factory($container)
        );
    }
}
