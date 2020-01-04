<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Navigation\Service;

use Psr\Container\ContainerInterface;
use Laminas\Navigation\Exception;
use Laminas\Navigation\Navigation;

class MezzioNavigationFactory extends AbstractMezzioNavigationFactory
{
    /**
     * @var array|null
     */
    private $pages;

    /**
     * Create and return a new Navigation instance
     *
     * @param ContainerInterface $container
     * @return Navigation
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Navigation($this->getPages($container));
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    private function getPages(ContainerInterface $container) : array
    {
        // Is already created?
        if (null !== $this->pages) {
            return $this->pages;
        }

        $configuration = $container->get('config');

        if (! isset($configuration['navigation'])) {
            throw new Exception\InvalidArgumentException(
                'Could not find navigation configuration key'
            );
        }
        if (! isset($configuration['navigation']['default'])) {
            throw new Exception\InvalidArgumentException(
                'Failed to find a navigation container by the name "default"'
            );
        }

        $pages = $this->getPagesFromConfig(
            $configuration['navigation']['default']
        );

        $this->pages = $this->preparePages($container, $pages);

        return $this->pages;
    }
}
