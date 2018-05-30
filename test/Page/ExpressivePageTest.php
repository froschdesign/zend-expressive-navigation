<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Navigation\Page;

use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Helper\Exception\RuntimeException as UrlHelperRuntimeException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Navigation\Page\ExpressivePage;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\ZendRouter;
use Zend\Navigation\Exception\InvalidArgumentException;

class ExpressivePageTest extends TestCase
{
    /**
     * @var \Zend\Expressive\Router\Route
     */
    private $route;

    /**
     * @var \Zend\Expressive\Router\RouteResult
     */
    private $routeResult;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    protected function setUp()
    {
        // Create URL helper
        $this->route = new Route('/foo', 'foo', ['GET'], 'foo');
        $router      = new ZendRouter();
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
        $page = new ExpressivePage(
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
        $page = new ExpressivePage(
            [
                'url_helper'   => $this->urlHelper,
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithFragment()
    {
        $page = new ExpressivePage(
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
        $page = new ExpressivePage(
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
        $page = new ExpressivePage(
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

        $failedResultSet = RouteResult::fromRouteFailure();
        $page            = new ExpressivePage(
            [
                'url_helper'   => $this->urlHelper,
                'route_result' => $failedResultSet,
            ]
        );

        $page->getHref();
    }

    public function testGetHrefSetsHrefCache()
    {
        $page = new ExpressivePage(
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
        $page = new ExpressivePage(
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
        $page = new ExpressivePage(
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
        $page = new ExpressivePage(
            [
                'route' => $name,
            ]
        );

        $this->assertSame($name, $page->getRoute());
    }

    public function testSetRoutePerMethod()
    {
        $name = 'foo';
        $page = new ExpressivePage();
        $page->setRoute($name);

        $this->assertSame($name, $page->getRoute());
    }

    public function testSetRouteToNull()
    {
        $page = new ExpressivePage();
        $page->setRoute(null);

        $this->assertNull($page->getRoute());
    }

    /**
     * @dataProvider invalidArgumentProvider
     *
     * @param $value
     */
    public function testInvalidArgumentForRouteShouldThrowException($value)
    {
        $this->expectException(InvalidArgumentException::class);

        $page = new ExpressivePage();
        $page->setRoute($value);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function invalidArgumentProvider() : array
    {
        return [
            [''],
            [new \stdClass()],
            [1],
            [1.0],
            [[]],
        ];
    }

    public function testSetRouterPerConstructor()
    {
        $page = new ExpressivePage(
            [
                'url_helper' => $this->urlHelper,
            ]
        );

        $this->assertSame($this->urlHelper, $page->getUrlHelper());
    }

    public function testSetUrlPerMethod()
    {
        $page = new ExpressivePage();
        $page->setUrlHelper($this->urlHelper);

        $this->assertSame($this->urlHelper, $page->getUrlHelper());
    }

    public function testSetRouteResultPerConstructor()
    {
        $page = new ExpressivePage(
            [
                'route_result' => $this->routeResult,
            ]
        );

        $this->assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetRouteResultPerMethod()
    {
        $page = new ExpressivePage();
        $page->setRouteResult($this->routeResult);

        $this->assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetParamsPerConstructor()
    {
        $params = [
            'foo' => 'bar',
        ];
        $page   = new ExpressivePage(
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
        $page   = new ExpressivePage();
        $page->setParams($params);

        $this->assertSame($params, $page->getParams());
    }

    public function testSetQueryPerConstructor()
    {
        $query = [
            'foo' => 'bar',
        ];
        $page  = new ExpressivePage(
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
        $page  = new ExpressivePage();
        $page->setQuery($query);

        $this->assertSame($query, $page->getQuery());
    }
}
