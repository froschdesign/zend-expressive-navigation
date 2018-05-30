<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Navigation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RecursiveIteratorIterator;
use Zend\Expressive\Navigation\Page\ExpressivePage;
use Zend\Expressive\Router\RouteResult;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Exception;

/**
 * Pipeline middleware for injecting Navigations with a RouteResult.
 */
class NavigationMiddleware implements MiddlewareInterface
{
    /**
     * @var AbstractContainer[]
     */
    private $containers = [];

    /**
     * @param AbstractContainer[] $containers
     */
    public function __construct(array $containers)
    {
        foreach ($containers as $container) {
            if (! $container instanceof AbstractContainer) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid argument: container must be an instance of %s',
                    AbstractContainer::class
                ));
            }

            $this->containers[] = $container;
        }
    }

    /**
     * @inheritDoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ) : ResponseInterface {
        $routeResult = $request->getAttribute(RouteResult::class, false);

        if (! $routeResult instanceof RouteResult) {
            return $handler->handle($request);
        }

        foreach ($this->containers as $container) {
            $iterator = new RecursiveIteratorIterator(
                $container,
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $page) {
                if ($page instanceof ExpressivePage) {
                    $page->setRouteResult($routeResult);
                }
            }
        }

        return $handler->handle($request);
    }
}
