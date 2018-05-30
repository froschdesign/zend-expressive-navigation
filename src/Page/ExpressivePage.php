<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Navigation\Page;

use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Router\RouteResult;
use Zend\Navigation\Exception;
use Zend\Navigation\Page\AbstractPage;

class ExpressivePage extends AbstractPage
{
    /**
     * Route name
     *
     * @var string|null
     */
    private $routeName;

    /**
     * Route parameters
     *
     * @var array
     */
    private $routeParams = [];

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var RouteResult
     */
    private $routeResult;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var string|null
     */
    private $hrefCache;

    /**
     * @inheritDoc
     */
    public function isActive($recursive = false)
    {
        if ($this->active
            || $this->routeName === null
            || ! $this->routeResult instanceof RouteResult
        ) {
            return parent::isActive($recursive);
        }

        $intersectionOfParams = array_intersect_assoc(
            $this->routeResult->getMatchedParams(),
            $this->routeParams
        );

        $matchedRouteName = $this->routeResult->getMatchedRouteName();

        if ($matchedRouteName === $this->routeName
            && count($intersectionOfParams) === count($this->routeParams)
        ) {
            $this->active = true;

            return $this->active;
        }

        return parent::isActive($recursive);
    }

    /**
     * @inheritDoc
     */
    public function getHref()
    {
        // User cache?
        if ($this->hrefCache) {
            return $this->hrefCache;
        }

        // Set route result
        $this->urlHelper->setRouteResult($this->routeResult);

        // Generate URL
        return $this->hrefCache = $this->urlHelper->generate(
            $this->routeName,
            $this->routeParams,
            $this->queryParams,
            $this->fragment
        );
    }

    /**
     * @param string|null $route
     */
    public function setRoute($route)
    {
        if (null !== $route && (! is_string($route) || empty($route))) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $route must be a non-empty string or null'
            );
        }

        $this->routeName = $route;
        $this->hrefCache = null;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->routeName;
    }

    /**
     * @param array|null $params
     */
    public function setParams(array $params = null)
    {
        $this->routeParams = $params ? : [];
        $this->hrefCache   = null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->routeParams;
    }

    /**
     * @param array|null $query
     */
    public function setQuery(array $query = null)
    {
        $this->queryParams = $query ? : [];
        $this->hrefCache   = null;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->queryParams;
    }

    /**
     * @param RouteResult $routeResult
     */
    public function setRouteResult(RouteResult $routeResult)
    {
        $this->routeResult = $routeResult;
    }

    /**
     * @return RouteResult
     */
    public function getRouteResult()
    {
        return $this->routeResult;
    }

    /**
     * @return UrlHelper
     */
    public function getUrlHelper()
    {
        return $this->urlHelper;
    }

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }
}
