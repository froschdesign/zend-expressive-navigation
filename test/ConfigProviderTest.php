<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Expressive\Navigation;

use PHPUnit\Framework\TestCase;
use Zend\Expressive\Navigation\Middleware;
use Zend\Expressive\Navigation\ConfigProvider;
use Zend\Expressive\Navigation\Service;
use Zend\Navigation\Navigation;

class ConfigProviderTest extends TestCase
{
    private $config = [
        'abstract_factories' => [
            Service\ExpressiveNavigationAbstractServiceFactory::class,
        ],
        'aliases'            => [
            'navigation' => Navigation::class,
        ],
        'factories'          => [
            Middleware\NavigationMiddleware::class => Middleware\NavigationMiddlewareFactory::class,
            Navigation::class                      => Service\ExpressiveNavigationFactory::class,
        ],
    ];

    public function testProvidesExpectedConfiguration()
    {
        $provider = new ConfigProvider();
        $this->assertEquals($this->config, $provider->getDependencyConfig());

        return $provider;
    }

    /**
     * @depends testProvidesExpectedConfiguration
     * @param ConfigProvider $provider
     */
    public function testInvocationProvidesDependencyConfiguration(
        ConfigProvider $provider
    ) {
        $this->assertEquals(
            [
                'dependencies' => $provider->getDependencyConfig(),
            ],
            $provider()
        );
    }
}
