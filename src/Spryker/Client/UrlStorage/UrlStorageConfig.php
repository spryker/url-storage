<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\UrlStorage;

use Spryker\Client\Kernel\AbstractBundleConfig;

/**
 * @method \Spryker\Shared\UrlStorage\UrlStorageConfig getSharedConfig()
 */
class UrlStorageConfig extends AbstractBundleConfig
{
    /**
     * To be able to work with data exported with collectors to redis, we need to bring this module into compatibility
     * mode. If this is turned on the UrlClient will be used instead.
     *
     * @api
     *
     * @return bool
     */
    public static function isCollectorCompatibilityMode(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - When enabled, locale_urls are fetched from a separate url_locale_map storage entry per resource.
     *
     * @api
     */
    public function isUrlLocaleMapStorageEnabled(): bool
    {
        return $this->getSharedConfig()->isUrlLocaleMapStorageEnabled();
    }
}
