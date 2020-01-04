<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Navigation\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Mezzio\Helper\UrlHelper;
use Mezzio\Navigation\Service\MezzioNavigationAbstractServiceFactory;
use Mezzio\Router\LaminasRouter;
use Laminas\Navigation\Navigation;

class MezzioNavigationAbstractServiceFactoryTest extends TestCase
{
    /**
     * @var MezzioNavigationAbstractServiceFactory
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp()
    {
        // Create factory
        $this->factory = new MezzioNavigationAbstractServiceFactory();

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn(
            [
                'navigation' => [
                    'default' => [
                        [
                            'route' => 'home',
                        ],
                    ],
                ],
            ]
        );
        $prophecy->get(UrlHelper::class)->willReturn(
            new UrlHelper(new LaminasRouter())
        );
        $this->container = $prophecy->reveal();
    }

    public function testInvokeMethodShouldReturnNavigationInstance()
    {
        $factory = $this->factory;
        $this->assertInstanceOf(
            Navigation::class,
            $factory($this->container, Navigation::class)
        );
    }

    public function testCanCreateMethodWithValidName()
    {
        $this->assertTrue(
            $this->factory->canCreate($this->container, Navigation::class)
        );
    }

    public function testCanCreateMethodWithInvalidName()
    {
        $this->assertFalse(
            $this->factory->canCreate($this->container, 'Foobar')
        );
    }

    public function testCreationWithEmptyConfigShouldReturnEmptyNavigation()
    {
        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn([]);
        $prophecy->get(UrlHelper::class)->willReturn(
            new UrlHelper(new LaminasRouter())
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $result = $factory($container, Navigation::class);
        $this->assertCount(0, $result);
    }
}
