<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\UrlStorage\KeyBuilder;

use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToLocaleClientInterface;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStoreClientInterface;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface;
use Spryker\Client\UrlStorage\UrlStorageConfig;
use Spryker\Shared\UrlStorage\UrlStorageConstants;

class UrlRedirectStorageKeyBuilder implements UrlRedirectStorageKeyBuilderInterface
{
    /**
     * @var \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface
     */
    protected $synchronizationService;

    /**
     * @var \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStoreClientInterface
     */
    protected $storeClient;

    /**
     * @var \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToLocaleClientInterface
     */
    protected $localeClient;

    /**
     * @var \Spryker\Client\UrlStorage\UrlStorageConfig
     */
    protected $urlStorageConfig;

    public function __construct(
        UrlStorageToSynchronizationServiceInterface $synchronizationService,
        UrlStorageToStoreClientInterface $storeClient,
        UrlStorageToLocaleClientInterface $localeClient,
        UrlStorageConfig $urlStorageConfig
    ) {
        $this->synchronizationService = $synchronizationService;
        $this->storeClient = $storeClient;
        $this->localeClient = $localeClient;
        $this->urlStorageConfig = $urlStorageConfig;
    }

    public function generateKey(int $idRedirectUrl): string
    {
        if ($this->urlStorageConfig::isCollectorCompatibilityMode()) {
            return sprintf(
                '%s.%s.resource.redirect.%s',
                strtolower($this->storeClient->getCurrentStore()->getName()),
                strtolower($this->localeClient->getCurrentLocale()),
                $idRedirectUrl,
            );
        }

        $synchronizationDataTransfer = (new SynchronizationDataTransfer())
            ->setReference($idRedirectUrl);

        return $this->synchronizationService
            ->getStorageKeyBuilder(UrlStorageConstants::REDIRECT_RESOURCE_NAME)
            ->generateKey($synchronizationDataTransfer);
    }
}
