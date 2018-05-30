<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Navigation\Service;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Navigation\Service\ExpressiveNavigationFactory;
use Zend\Expressive\Router\ZendRouter;
use Zend\Navigation\Exception\InvalidArgumentException;
use Zend\Navigation\Navigation;

class ExpressiveNavigationFactoryTest extends TestCase
{
    /**
     * @var ExpressiveNavigationFactory
     */
    private $factory;

    /**
     * @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $container;

    protected function setUp()
    {
        // Create factory
        $this->factory = new ExpressiveNavigationFactory();

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
            new UrlHelper(new ZendRouter())
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
            new UrlHelper(new ZendRouter())
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
            new UrlHelper(new ZendRouter())
        );
        /** @var ContainerInterface $container */
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $factory($container);
    }
}
