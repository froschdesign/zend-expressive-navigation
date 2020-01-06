<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Navigation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RecursiveIteratorIterator;
use Mezzio\Navigation\Page\MezzioPage;
use Mezzio\Router\RouteResult;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Exception;

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
                if ($page instanceof MezzioPage) {
                    $page->setRouteResult($routeResult);
                }
            }
        }

        return $handler->handle($request);
    }
}
