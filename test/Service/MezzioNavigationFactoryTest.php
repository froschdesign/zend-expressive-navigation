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
use Mezzio\Navigation\Service\MezzioNavigationFactory;
use Mezzio\Router\LaminasRouter;
use Laminas\Navigation\Exception\InvalidArgumentException;
use Laminas\Navigation\Navigation;

class MezzioNavigationFactoryTest extends TestCase
{
    /**
     * @var MezzioNavigationFactory
     */
    private $factory;

    /**
     * @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $container;

    protected function setUp()
    {
        // Create factory
        $this->factory = new MezzioNavigationFactory();

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->get('config')->willReturn([
            'navigation' => [
                'default' => [
                    [
                        'route' => 'home',
                    ],
                ],
            ],
        ]);
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
            $factory($this->container)
        );
    }

    public function testGetPagesSetsPagesProperty()
    {
        $reflection = new \ReflectionClass($this->factory);
        $property   = $reflection->getProperty('pages');
        $property->setAccessible(true);

        $factory = $this->factory;
        $this->assertNull($property->getValue($this->factory));
        $factory($this->container);

        $this->assertTrue(is_array($property->getValue($this->factory)));
        $factory($this->container);
    }

    public function testMissingNavigationConfigShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->get('config')->willReturn([]);
        $prophecy->get(UrlHelper::class)->willReturn(
            new UrlHelper(new LaminasRouter())
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $factory($container);
    }

    public function testMissingDefaultConfigShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->get('config')->willReturn(['navigation' => []]);
        $prophecy->get(UrlHelper::class)->willReturn(
            new UrlHelper(new LaminasRouter())
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $factory($container);
    }
}
