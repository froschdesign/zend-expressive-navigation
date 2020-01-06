<?php
/**
 * @see       https://github.com/mezzio/mezzio-navigation for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-navigation/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Navigation\Service;

use Psr\Container\ContainerInterface;
use Traversable;
use Laminas\Config;
use Mezzio\Helper\UrlHelper;
use Mezzio\Navigation\Page\MezzioPage;
use Laminas\Navigation\Exception;
use Laminas\Stdlib\ArrayUtils;

abstract class AbstractMezzioNavigationFactory
{
    /**
     * @param ContainerInterface $container
     * @param array              $pages
     * @return array
     */
    protected function preparePages(
        ContainerInterface $container,
        array $pages
    ) : array {
        // Get URL helper
        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get(UrlHelper::class);

        return $this->injectComponents($pages, $urlHelper);
    }

    /**
     * @param array          $pages
     * @param UrlHelper|null $urlHelper
     * @return array
     */
    protected function injectComponents(
        array $pages,
        UrlHelper $urlHelper = null
    ) : array {
        foreach ($pages as &$page) {
            if (isset($page['route'])) {
                // Set Mezzio page as page type
                $page['type'] = MezzioPage::class;

                // Set URL helper if exists
                if ($urlHelper !== null && ! isset($page['url_helper'])) {
                    $page['url_helper'] = $urlHelper;
                }
            }

            if (isset($page['pages'])) {
                $page['pages'] = $this->injectComponents(
                    $page['pages'],
                    $urlHelper
                );
            }
        }

        return $pages;
    }

    /**
     * @param string|Config\Config|array $config
     * @return array|null|Config\Config
     * @throws Exception\InvalidArgumentException
     */
    protected function getPagesFromConfig($config = null)
    {
        if (\is_string($config)) {
            if (! file_exists($config)) {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'Config was a string but file "%s" does not exist',
                        $config
                    )
                );
            }
            $config = Config\Factory::fromFile($config);
        } elseif ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        } elseif (! \is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'Invalid input, expected array, filename, or Traversable object'
            );
        }

        return $config;
    }
}
