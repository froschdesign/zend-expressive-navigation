<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Navigation\Middleware;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;
use ReflectionObject;
use Mezzio\Navigation\Middleware\NavigationMiddleware;
use Mezzio\Navigation\Middleware\NavigationMiddlewareFactory;
use Mezzio\Navigation\Page\MezzioPage;
use Laminas\Navigation\Navigation;

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
                'default' => [],
                'special' => [],
            ],
        ]);
        $prophecy->get('Laminas\Navigation\Default')->willReturn(
            $this->navigation
        );
        $prophecy->get('Laminas\Navigation\Special')->willReturn(
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
                'default' => [],
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

    public function testFactoryWithOneNavigationAndCustomNavigationName()
    {
        // Add page
        $this->navigation->addPage(
            new MezzioPage(['route' => 'home'])
        );

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn([
            'navigation' => [
                'special' => [],
            ],
        ]);
        $prophecy->get('Laminas\Navigation\Special')->willReturn(
            $this->navigation
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        // Test middleware
        $factory = $this->factory;
        $middleware = $factory($container);
        $this->assertInstanceOf(NavigationMiddleware::class, $middleware);

        $reflection = new ReflectionObject($middleware);
        $property = $reflection->getProperty('containers');
        $property->setAccessible(true);
        /** @var array $containers */
        $containers = $property->getValue($middleware);

        $this->assertSame($this->navigation, array_shift($containers));
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
