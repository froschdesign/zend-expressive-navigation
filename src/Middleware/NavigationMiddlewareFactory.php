<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Navigation\Middleware;

use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;

class NavigationMiddlewareFactory
{
    /**
     * Top-level configuration key indicating navigation configuration
     *
     * @var string
     */
    public const CONFIG_KEY = 'navigation';

    /**
     * Service manager factory prefix
     *
     * @var string
     */
    public const SERVICE_PREFIX = 'Laminas\\Navigation\\';

    /**
     * @var array|null
     */
    private $containerNames;

    /**
     * @param ContainerInterface $container
     * @return NavigationMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $containerNames = $this->getContainerNames($container);

        $containers = [];
        foreach ($containerNames as $containerName) {
            $containers[] = $container->get($containerName);
        }

        return new NavigationMiddleware($containers);
    }

    /**
     * Get navigation container names
     *
     * @param  ContainerInterface $container
     * @return array
     */
    private function getContainerNames(ContainerInterface $container) : array
    {
        if ($this->containerNames !== null) {
            return $this->containerNames;
        }

        if (! $container->has('config')) {
            $this->containerNames = [];
            return $this->containerNames;
        }

        $config = $container->get('config');
        if (! isset($config[self::CONFIG_KEY])
            || ! \is_array($config[self::CONFIG_KEY])
        ) {
            $this->containerNames = [];
            return $this->containerNames;
        }

        $names = array_keys($config[self::CONFIG_KEY]);

        if (\count($names) === 1 && current($names) === 'default') {
            $this->containerNames[] = Navigation::class;
            return $this->containerNames;
        }

        foreach ($names as $name) {
            $this->containerNames[] = self::SERVICE_PREFIX . ucfirst($name);
        }

        return $this->containerNames;
    }
}
