<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Navigation\Page;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Helper\Exception\RuntimeException as UrlHelperRuntimeException;
use Mezzio\Helper\UrlHelper;
use Mezzio\Navigation\Page\MezzioPage;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Mezzio\Router\LaminasRouter;
use Laminas\Navigation\Exception\InvalidArgumentException;

class MezzioPageTest extends TestCase
{
    /**
     * @var \Mezzio\Router\Route
     */
    private $route;

    /**
     * @var \Mezzio\Router\RouteResult
     */
    private $routeResult;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    protected function setUp()
    {
        // Create middleware double
        $middleware = $this->prophesize(MiddlewareInterface::class)->reveal();

        // Create URL helper
        $this->route = new Route('/foo', $middleware, ['GET'], 'foo');
        $router      = new LaminasRouter();
        $router->addRoute($this->route);
        $this->urlHelper = new UrlHelper($router);

        // Set route result
        $this->routeResult = $router->match(
            $request = new ServerRequest(
                ['REQUEST_METHOD' => 'GET'],
                [],
                '/foo',
                'GET'
            )
        );
    }

    public function testGetHref()
    {
        $page = new MezzioPage(
            [
                'route'        => 'foo',
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithoutRouteName()
    {
        $page = new MezzioPage(
            [
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithFragment()
    {
        $page = new MezzioPage(
            [
                'route'        => 'foo',
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
                'fragment'     => 'bar',
            ]
        );

        $this->assertSame('/foo#bar', $page->getHref());
    }

    public function testGetHrefWithQueryParams()
    {
        $page = new MezzioPage(
            [
                'route'        => 'foo',
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
                'query'        => [
                    'bar' => 1,
                    'baz' => 2,
                ],
            ]
        );

        $this->assertSame('/foo?bar=1&baz=2', $page->getHref());
    }

    public function testGetHrefWithBasePath()
    {
        // Create page
        $page = new MezzioPage(
            [
                'route'        => 'foo',
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        // Set base path
        $this->urlHelper->setBasePath('bar');

        $this->assertSame('/bar/foo', $page->getHref());
    }

    public function testGetHrefWithFailedResultSet()
    {
        $this->expectException(UrlHelperRuntimeException::class);

        $failedResultSet = RouteResult::fromRouteFailure(null);
        $page            = new MezzioPage(
            [
                'url_helper'   => $this->urlHelper,
                'route_result' => $failedResultSet,
            ]
        );

        $page->getHref();
    }

    public function testGetHrefWithRouteResultOnUrlHelperAndNotPageShouldGenerateHref()
    {
        $this->urlHelper->setRouteResult($this->routeResult);

        $page = new MezzioPage(
            [
                'url_helper' => $this->urlHelper,
            ]
        );

        $this->assertSame('/foo', $page->getHref());
    }

    public function testGetHrefSetsHrefCache()
    {
        $page = new MezzioPage(
            [
                'route'        => 'foo',
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        $reflection = new \ReflectionClass($page);
        $property   = $reflection->getProperty('hrefCache');
        $property->setAccessible(true);

        $this->assertNull($property->getValue($page));
        $page->getHref();

        $this->assertSame('/foo', $property->getValue($page));
        $page->getHref();
    }

    public function testIsActive()
    {
        $page = new MezzioPage(
            [
                'route'        => 'foo',
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertTrue($page->isActive());
    }

    public function testIsActiveWithoutRoute()
    {
        $page = new MezzioPage(
            [
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertFalse($page->isActive());
    }

    public function testSetRoutePerConstructor()
    {
        $name = 'foo';
        $page = new MezzioPage(
            [
                'route' => $name,
            ]
        );

        $this->assertSame($name, $page->getRoute());
    }

    public function testSetRoutePerMethod()
    {
        $name = 'foo';
        $page = new MezzioPage();
        $page->setRoute($name);

        $this->assertSame($name, $page->getRoute());
    }

    public function testSetRouteToNull()
    {
        $page = new MezzioPage();
        $page->setRoute(null);

        $this->assertNull($page->getRoute());
    }

    public function testInvalidArgumentForRouteShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $page = new MezzioPage();
        $page->setRoute('');
    }

    public function testSetRouterPerConstructor()
    {
        $page = new MezzioPage(
            [
                'url_helper' => $this->urlHelper,
            ]
        );

        $this->assertSame($this->urlHelper, $page->getUrlHelper());
    }

    public function testSetUrlPerMethod()
    {
        $page = new MezzioPage();
        $page->setUrlHelper($this->urlHelper);

        $this->assertSame($this->urlHelper, $page->getUrlHelper());
    }

    public function testSetRouteResultPerConstructor()
    {
        $page = new MezzioPage(
            [
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetRouteResultPerMethod()
    {
        $page = new MezzioPage();
        $page->setRouteResult($this->routeResult);

        $this->assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetParamsPerConstructor()
    {
        $params = [
            'foo' => 'bar',
        ];
        $page   = new MezzioPage(
            [
                'params' => $params,
            ]
        );

        $this->assertSame($params, $page->getParams());
    }

    public function testSetParamsPerMethod()
    {
        $params = [
            'foo' => 'bar',
        ];
        $page   = new MezzioPage();
        $page->setParams($params);

        $this->assertSame($params, $page->getParams());
    }

    public function testSetQueryPerConstructor()
    {
        $query = [
            'foo' => 'bar',
        ];
        $page  = new MezzioPage(
            [
                'query' => $query,
            ]
        );

        $this->assertSame($query, $page->getQuery());
    }

    public function testSetQueryPerMethod()
    {
        $query = [
            'foo' => 'bar',
        ];
        $page  = new MezzioPage();
        $page->setQuery($query);

        $this->assertSame($query, $page->getQuery());
    }
}
