<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Navigation;

use Mezzio\Navigation\Middleware;
use Laminas\Navigation\Navigation;

class ConfigProvider
{
    /**
     * Return general-purpose mezzio-navigation configuration.
     *
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig() : array
    {
        return [
            'abstract_factories' => [
                Service\MezzioNavigationAbstractServiceFactory::class,
            ],
            'aliases'            => [
                'navigation' => Navigation::class,
            ],
            'factories'          => [
                Middleware\NavigationMiddleware::class => Middleware\NavigationMiddlewareFactory::class,
                Navigation::class                      => Service\MezzioNavigationFactory::class,
            ],
        ];
    }
}
