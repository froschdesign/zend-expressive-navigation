<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Navigation\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Navigation\Middleware\NavigationMiddleware;
use Mezzio\Navigation\Page\MezzioPage;
use Laminas\Navigation\Exception\InvalidArgumentException;
use Laminas\Navigation\Navigation;

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
            new MezzioPage(),
            new MezzioPage(),
            new MezzioPage(),
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
        /** @var MezzioPage $page */
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
