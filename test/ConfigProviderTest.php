<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Navigation;

use PHPUnit\Framework\TestCase;
use Mezzio\Navigation\Middleware;
use Mezzio\Navigation\ConfigProvider;
use Mezzio\Navigation\Service;
use Laminas\Navigation\Navigation;

class ConfigProviderTest extends TestCase
{
    private $config = [
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
