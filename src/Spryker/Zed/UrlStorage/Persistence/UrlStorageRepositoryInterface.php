<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\UrlStorage\Persistence;

use Generated\Shared\Transfer\FilterTransfer;

interface UrlStorageRepositoryInterface
{
    /**
     * @param array<int> $urlIds
     * @param array<string> $localeNames
     *
     * @return array<array<\Orm\Zed\Url\Persistence\SpyUrl>>
     */
    public function findLocalizedUrlsByUrlIds(array $urlIds, array $localeNames): array;

    /**
     * @param array<int> $urlIds
     *
     * @return array<\Orm\Zed\UrlStorage\Persistence\SpyUrlStorage>
     */
    public function findUrlStorageByUrlIds(array $urlIds): array;

    /**
     * @param array<string> $urls
     *
     * @return array<\Orm\Zed\Url\Persistence\SpyUrl>
     */
    public function findUrlEntitiesByUrls(array $urls): array;

    /**
     * @param array<string> $resourceReferences
     *
     * @return array<string, \Orm\Zed\UrlStorage\Persistence\SpyUrlLocaleMapStorage>
     */
    public function findUrlLocaleMapStorageByResourceReferences(array $resourceReferences): array;

    /**
     * @param \Generated\Shared\Transfer\FilterTransfer $filterTransfer
     * @param array<int> $urlLocaleMapStorageIds
     *
     * @return array<\Generated\Shared\Transfer\SynchronizationDataTransfer>
     */
    public function getUrlLocaleMapStorageSynchronizationDataTransfers(FilterTransfer $filterTransfer, array $urlLocaleMapStorageIds = []): array;
}
