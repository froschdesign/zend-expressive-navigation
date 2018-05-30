<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Navigation\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Navigation\Middleware\NavigationMiddleware;
use Zend\Expressive\Navigation\Page\ExpressivePage;
use Zend\Navigation\Exception\InvalidArgumentException;
use Zend\Navigation\Navigation;

class NavigationMiddlewareTest extends TestCase
{
    /**
     * @var NavigationMiddleware
     */
    private $middleware;

    /**
     * @var Navigation
     */
    private $navigation;

    protected function setUp()
    {
        // Create navigation with one page
        $this->navigation = new Navigation([
            new ExpressivePage(['route' => 'home']),
        ]);

        // Create middleware
        $this->middleware = new NavigationMiddleware([$this->navigation]);
    }

    public function testRouteResultShouldAddedToPages()
    {
        // Route result test double
        $routeResult = $this->prophesize(RouteResult::class)->reveal();

        // Request test double
        /** @var ServerRequestInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ServerRequestInterface::class);
        $prophecy->getAttribute(RouteResult::class, false)->willReturn(
            $routeResult
        );
        /** @var ServerRequestInterface $request */
        $request = $prophecy->reveal();

        // Delegate test double
        /** @var DelegateInterface $delegate */
        $delegate = $this->prophesize(DelegateInterface::class)->reveal();
        $this->middleware->process($request, $delegate);

        // Get page
        /** @var ExpressivePage $page */
        $page = $this->navigation->findOneBy('route', 'home');
        if ($page) {
            $this->assertEquals($routeResult, $page->getRouteResult());
        } else {
            $this->fail('Page not found!');
        }
    }

    public function testInvalidContainerShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        new NavigationMiddleware([1]);
    }
}
