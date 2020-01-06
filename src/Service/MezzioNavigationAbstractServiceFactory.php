<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Navigation\Service;

use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

final class MezzioNavigationAbstractServiceFactory extends AbstractMezzioNavigationFactory implements
    AbstractFactoryInterface
{
    /**
     * Top-level configuration key indicating navigation configuration
     *
     * @var string
     */
    const CONFIG_KEY = 'navigation';

    /**
     * Service manager factory prefix
     *
     * @var string
     */
    const SERVICE_PREFIX = 'Laminas\\Navigation\\';

    /**
     * Navigation configuration
     *
     * @var array|null
     */
    private $config;

    /**
     * @var Navigation[]
     */
    private $containers = [];

    /**
     * Can we create a navigation by the requested name?
     *
     * @param ContainerInterface $container
     * @param string             $requestedName Name by which service was
     *                                          requested, must start with
     *                                          Laminas\Navigation\
     * @return bool
     */
    public function canCreate(
        ContainerInterface $container,
        $requestedName
    ) : bool {
        $requestedName = $this->normalizeRequestedName($requestedName);

        if (0 !== strpos($requestedName, self::SERVICE_PREFIX)) {
            return false;
        }

        if (array_key_exists($requestedName, $this->containers)) {
            return true;
        }

        $config = $this->getConfig($container);

        return $this->hasNamedConfig($requestedName, $config);
    }

    /**
     * {@inheritDoc}
     *
     * @return Navigation
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $requestedName = $this->normalizeRequestedName($requestedName);

        // Is already created?
        if (array_key_exists($requestedName, $this->containers)) {
            return $this->containers[$requestedName];
        }

        // Get config
        $config          = $this->getConfig($container);
        $pagesFromConfig = $this->getPagesFromConfig(
            $this->getNamedConfig($requestedName, $config)
        );

        // Prepare pages
        $pages = $this->preparePages(
            $container,
            $pagesFromConfig
        );

        // Create navigation
        $this->containers[$requestedName] = new Navigation($pages);

        return $this->containers[$requestedName];
    }

    /**
     * Sets the name to "default" if this factory is used for a single navigation
     *
     * @param string $requestedName
     * @return string
     */
    private function normalizeRequestedName(string $requestedName) : string
    {
        if ($requestedName === Navigation::class) {
            $requestedName = 'Laminas\Navigation\Default';
        }

        return $requestedName;
    }

    /**
     * Get navigation configuration, if any
     *
     * @param  ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container) : array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (! $container->has('config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('config');
        if (! isset($config[self::CONFIG_KEY])
            || ! \is_array($config[self::CONFIG_KEY])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[self::CONFIG_KEY];
        return $this->config;
    }

    /**
     * Extract config name from service name
     *
     * @param string $name
     * @return string
     */
    private function getConfigName(string $name) : string
    {
        return substr($name, \strlen(self::SERVICE_PREFIX));
    }

    /**
     * Does the configuration have a matching named section?
     *
     * @param string             $name
     * @param array|\ArrayAccess $config
     * @return bool
     */
    private function hasNamedConfig(string $name, $config) : bool
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return true;
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return true;
        }

        return false;
    }

    /**
     * Get the matching named configuration section.
     *
     * @param string             $name
     * @param array|\ArrayAccess $config
     * @return array
     */
    private function getNamedConfig(string $name, $config) : array
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return $config[$withoutPrefix];
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return $config[strtolower($withoutPrefix)];
        }

        return [];
    }
}
