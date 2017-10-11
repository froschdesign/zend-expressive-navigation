<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Navigation;

use Zend\Navigation\Navigation;

class ConfigProvider
{
    /**
     * Return general-purpose zend-navigation configuration.
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
                Service\ExpressiveNavigationAbstractServiceFactory::class,
            ],
            'aliases'            => [
                'navigation' => Navigation::class,
            ],
            'factories'          => [
                Navigation::class => Service\ExpressiveNavigationFactory::class,
            ],
        ];
    }
}
