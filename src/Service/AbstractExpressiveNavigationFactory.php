<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-navigation for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Navigation\Service;

use Psr\Container\ContainerInterface;
use Traversable;
use Zend\Config;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Navigation\Page\ExpressivePage;
use Zend\Navigation\Exception;
use Zend\Stdlib\ArrayUtils;

abstract class AbstractExpressiveNavigationFactory
{
    /**
     * @param ContainerInterface $container
     * @param array              $pages
     * @return array
     */
    protected function preparePages(ContainerInterface $container, array $pages)
    {
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
    ) {
        foreach ($pages as &$page) {
            if (isset($page['route'])) {
                // Set Expressive page as page type
                $page['type'] = ExpressivePage::class;

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
        if (is_string($config)) {
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
        } elseif (! is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'Invalid input, expected array, filename, or Traversable object'
            );
        }

        return $config;
    }
}
