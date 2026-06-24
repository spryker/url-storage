<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\UrlStorage;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class UrlStorageConfig extends AbstractSharedConfig
{
    /**
     * @api
     *
     * Defines queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const PUBLISH_URL = 'publish.url';

    /**
     * @api
     *
     * Defines retry queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const PUBLISH_URL_RETRY = 'publish.url.retry';

    /**
     * @api
     *
     * Defines queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const string PUBLISH_URL_REDIRECT = 'publish.url.redirect';

    /**
     * @api
     *
     * Defines retry queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const string PUBLISH_URL_REDIRECT_RETRY = 'publish.url.redirect.retry';

    /**
     * Specification:
     * - When enabled, locale_urls are stored in a separate url_locale_map storage entry per resource instead of being
     *   duplicated in every URL entry. Must be enabled consistently in both Zed and Client layers.
     *
     * @api
     */
    public function isUrlLocaleMapStorageEnabled(): bool
    {
        return false;
    }
}
