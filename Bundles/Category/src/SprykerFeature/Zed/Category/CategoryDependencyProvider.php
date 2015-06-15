<?php

namespace SprykerFeature\Zed\Category;

use SprykerEngine\Zed\Kernel\AbstractBundleDependencyProvider;
use SprykerEngine\Zed\Kernel\Container;

class CategoryDependencyProvider extends AbstractBundleDependencyProvider
{

    const FACADE_TOUCH = 'touch facade';
    const FACADE_LOCALE = 'locale facade';
    const FACADE_URL = 'url facade';

    /**
     * @var Container $container
     *
     * @return Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container[self::FACADE_TOUCH] = function (Container $container) {
            return $container->getLocator()->touch()->facade();
        };

        $container[self::FACADE_LOCALE] = function (Container $container) {
            return $container->getLocator()->locale()->facade();
        };

        $container[self::FACADE_URL] = function (Container $container) {
            return $container->getLocator()->url()->facade();
        };

        return $container;
    }

}
