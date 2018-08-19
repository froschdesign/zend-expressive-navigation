<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Navigation\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
            new ExpressivePage(),
            new ExpressivePage(),
            new ExpressivePage(),
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

        // Response test double
        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        // Handler test double
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);
        $this->middleware->process($request, $handler->reveal());

        // Test pages
        /** @var ExpressivePage $page */
        foreach ($this->navigation as $page) {
            $this->assertEquals($routeResult, $page->getRouteResult());
        }
    }

    public function testInvalidContainerShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        new NavigationMiddleware([1]);
    }
}
