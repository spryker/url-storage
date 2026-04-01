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
     * Defines queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const PUBLISH_URL = 'publish.url';

    /**
     * Defines retry queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const PUBLISH_URL_RETRY = 'publish.url.retry';

    /**
     * Defines queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const string PUBLISH_URL_REDIRECT = 'publish.url.redirect';

    /**
     * Defines retry queue name that as used for asynchronous event handling.
     *
     * @var string
     */
    public const string PUBLISH_URL_REDIRECT_RETRY = 'publish.url.redirect.retry';
}
