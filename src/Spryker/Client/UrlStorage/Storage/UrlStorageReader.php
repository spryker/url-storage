<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\UrlStorage\Storage;

use Generated\Shared\Transfer\SynchronizationDataTransfer;
use Generated\Shared\Transfer\UrlStorageTransfer;
use Spryker\Client\Kernel\Locator;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStorageInterface;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToUtilEncodingServiceInterface;
use Spryker\Client\UrlStorage\UrlStorageConfig;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;

class UrlStorageReader implements UrlStorageReaderInterface
{
    protected const string URL = 'url';

    protected const string RESOURCE_FK_PREFIX = 'fk_';

    protected const string URL_LOCALE_MAP_RESOURCE = 'url_locale_map';

    protected const string LOCALE_URLS_KEY = 'locale_urls';

    /**
     * @var \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStorageInterface
     */
    protected $storageClient;

    /**
     * @var \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface
     */
    protected $synchronizationService;

    /**
     * @var \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @var array<\Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface>
     */
    protected $urlStorageResourceMapperPlugins;

    /**
     * @var \Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface|null
     */
    protected static $storageKeyBuilder;

    protected static ?SynchronizationKeyGeneratorPluginInterface $localeMapKeyBuilder = null;

    protected UrlStorageConfig $config;

    /**
     * @param \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStorageInterface $storageClient
     * @param \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface $synchronizationService
     * @param \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToUtilEncodingServiceInterface $utilEncodingService
     * @param array<\Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface> $resourceMapperPlugins
     */
    public function __construct(
        UrlStorageToStorageInterface $storageClient,
        UrlStorageToSynchronizationServiceInterface $synchronizationService,
        UrlStorageToUtilEncodingServiceInterface $utilEncodingService,
        array $resourceMapperPlugins,
        UrlStorageConfig $config
    ) {
        $this->storageClient = $storageClient;
        $this->synchronizationService = $synchronizationService;
        $this->utilEncodingService = $utilEncodingService;
        $this->urlStorageResourceMapperPlugins = $resourceMapperPlugins;
        $this->config = $config;
    }

    /**
     * @param string $url
     * @param string|null $localeName
     *
     * @return array<string, mixed>
     */
    public function matchUrl($url, $localeName)
    {
        $urlDetails = $this->getUrlsFromStorage([$url])[0] ?? null;

        if (!$urlDetails) {
            return [];
        }

        if ($localeName === null) {
            $localeName = $this->getLocaleNameFromUrlDetails($urlDetails);
        }

        $options = [
            'locale' => strtolower($localeName),
        ];

        $urlStorageResourceMapTransfer = $this->getUrlStorageResourceMapTransfer($urlDetails, $options);
        if ($urlStorageResourceMapTransfer === null) {
            return [];
        }

        $data = $this->storageClient->get($urlStorageResourceMapTransfer->getResourceKey());
        if ($data) {
            return [
                'type' => $urlStorageResourceMapTransfer->getType(),
                'data' => $data,
            ];
        }

        return [];
    }

    public function hasUrl(string $url, ?string $localeName): bool
    {
        $urlDetails = $this->getUrlsFromStorage([$url])[0] ?? null;

        if (!$urlDetails) {
            return false;
        }

        if ($localeName === null) {
            $localeName = $this->getLocaleNameFromUrlDetails($urlDetails);
        }

        $options = [
            'locale' => strtolower((string)$localeName),
        ];

        return $this->getUrlStorageResourceMapTransfer($urlDetails, $options) !== null;
    }

    /**
     * @param string $url
     *
     * @return \Generated\Shared\Transfer\UrlStorageTransfer|null
     */
    public function findUrlStorageTransferByUrl($url)
    {
        $urlStorageTransfers = $this->getUrlStorageTransferByUrls([$url]);

        return current($urlStorageTransfers) ?: null;
    }

    /**
     * @param array<string> $urlCollection
     *
     * @return array<\Generated\Shared\Transfer\UrlStorageTransfer>
     */
    public function getUrlStorageTransferByUrls(array $urlCollection): array
    {
        $urlStorageData = $this->getUrlsFromStorage($urlCollection);
        if (!$urlStorageData) {
            return [];
        }

        if ($this->config->isUrlLocaleMapStorageEnabled()) {
            $urlStorageData = $this->injectLocaleUrlsInBatch($urlStorageData);
        }

        return $this->mapUrlStorageDataToUrlStorageTransfers($urlStorageData);
    }

    protected function getLocaleNameFromUrlDetails(array $urlDetails): ?string
    {
        if ($this->config->isUrlLocaleMapStorageEnabled()) {
            $urlDetails = $this->injectLocaleUrls($urlDetails);
        }

        if (!isset($urlDetails[static::LOCALE_URLS_KEY])) {
            return null;
        }

        foreach ($urlDetails[static::LOCALE_URLS_KEY] as $localeUrl) {
            if ($localeUrl['fk_locale'] === $urlDetails['fk_locale']) {
                return $localeUrl['locale_name'];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $urlDetails
     */
    protected function buildResourceReference(array $urlDetails): ?string
    {
        foreach ($urlDetails as $key => $value) {
            if ($value === null || !str_starts_with($key, static::RESOURCE_FK_PREFIX . 'resource_')) {
                continue;
            }

            return sprintf('%s:%s', substr($key, strlen(static::RESOURCE_FK_PREFIX)), $value);
        }

        return null;
    }

    protected function getLocaleMapStorageKeyBuilder(): SynchronizationKeyGeneratorPluginInterface
    {
        if (static::$localeMapKeyBuilder === null) {
            static::$localeMapKeyBuilder = $this->synchronizationService->getStorageKeyBuilder(static::URL_LOCALE_MAP_RESOURCE);
        }

        return static::$localeMapKeyBuilder;
    }

    protected function getLocaleMapKey(string $resourceReference): string
    {
        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer->setReference($resourceReference);

        return $this->getLocaleMapStorageKeyBuilder()->generateKey($synchronizationDataTransfer);
    }

    /**
     * @param array<string, mixed> $urlDetails
     *
     * @return array<string, mixed>
     */
    protected function injectLocaleUrls(array $urlDetails): array
    {
        $resourceReference = $this->buildResourceReference($urlDetails);

        if ($resourceReference === null) {
            return $urlDetails;
        }

        $localeMapData = $this->storageClient->get($this->getLocaleMapKey($resourceReference));

        if (!$localeMapData || !isset($localeMapData[static::LOCALE_URLS_KEY])) {
            return $urlDetails;
        }

        $urlDetails[static::LOCALE_URLS_KEY] = $localeMapData[static::LOCALE_URLS_KEY];

        return $urlDetails;
    }

    /**
     * @param array<array<string, mixed>> $urlStorageData
     *
     * @return array<array<string, mixed>>
     */
    protected function injectLocaleUrlsInBatch(array $urlStorageData): array
    {
        $resourceReferences = [];
        foreach ($urlStorageData as $index => $urlDetails) {
            $resourceReference = $this->buildResourceReference($urlDetails);

            if ($resourceReference === null) {
                continue;
            }

            $resourceReferences[$index] = $resourceReference;
        }

        if (count($resourceReferences) === 0) {
            return $urlStorageData;
        }

        $storageKeys = array_map(
            fn (string $reference): string => $this->getLocaleMapKey($reference),
            $resourceReferences,
        );

        $localeMapResults = $this->storageClient->getMulti(array_values($storageKeys));
        if (!$localeMapResults) {
            return $urlStorageData;
        }
        $localeMapByReference = array_combine(array_keys($resourceReferences), array_values($localeMapResults));

        foreach ($localeMapByReference as $index => $localeMapData) {
            if (!$localeMapData) {
                continue;
            }
            $localeMapData = json_decode($localeMapData, true);
            if (!isset($localeMapData[static::LOCALE_URLS_KEY])) {
                continue;
            }

            $urlStorageData[$index][static::LOCALE_URLS_KEY] = $localeMapData[static::LOCALE_URLS_KEY];
        }

        return $urlStorageData;
    }

    /**
     * @param string $url
     *
     * @return array|null
     */
    protected function getCollectorUrlData(string $url)
    {
        $clientLocatorClass = Locator::class;
        /** @var \Generated\Zed\Ide\AutoCompletion&\Spryker\Shared\Kernel\LocatorLocatorInterface $locator */
        $locator = $clientLocatorClass::getInstance();
        $urlClient = $locator->url()->client();
        $localeName = $locator->locale()->client()->getCurrentLocale();
        $urlCollectorStorageTransfer = $urlClient->findUrl($url, $localeName);

        if (!$urlCollectorStorageTransfer) {
            return null;
        }

        $primaryUrlTransfer = null;
        $urlStorageLocaleUrlCollection = [];
        foreach ($urlCollectorStorageTransfer->getLocaleUrls() as $localeUrlTransfer) {
            $localeUrl = $localeUrlTransfer->toArray();
            $urlStorageLocaleUrlCollection[] = $localeUrl;

            if ($localeUrlTransfer->getUrl() === $url) {
                $primaryUrlTransfer = $localeUrlTransfer;
            }
        }

        if (!$primaryUrlTransfer) {
            return null;
        }

        $urlData = $primaryUrlTransfer->toArray();
        $urlData['locale_urls'] = $urlStorageLocaleUrlCollection;

        return $urlData;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getUrlKey($url)
    {
        $synchronizationDataTransfer = new SynchronizationDataTransfer();
        $synchronizationDataTransfer->setReference(rawurldecode($url));

        return $this->getStorageKeyBuilder()->generateKey($synchronizationDataTransfer);
    }

    protected function getStorageKeyBuilder(): SynchronizationKeyGeneratorPluginInterface
    {
        if (static::$storageKeyBuilder === null) {
            static::$storageKeyBuilder = $this->synchronizationService->getStorageKeyBuilder(static::URL);
        }

        return static::$storageKeyBuilder;
    }

    /**
     * @param array $urlDetails
     * @param array<string, mixed> $options
     *
     * @return \Generated\Shared\Transfer\UrlStorageResourceMapTransfer|null
     */
    protected function getUrlStorageResourceMapTransfer(array $urlDetails, array $options = [])
    {
        $spyUrlTransfer = new UrlStorageTransfer();
        $spyUrlTransfer->fromArray($urlDetails, true);

        foreach ($this->urlStorageResourceMapperPlugins as $urlStorageResourceMapperPlugin) {
            $pluginUrlStorageResourceMapTransfer = $urlStorageResourceMapperPlugin->map($spyUrlTransfer, $options);
            if ($pluginUrlStorageResourceMapTransfer->getResourceKey()) {
                return $pluginUrlStorageResourceMapTransfer;
            }
        }

        return null;
    }

    /**
     * @param array<string> $urlCollection
     *
     * @return array
     */
    protected function getUrlsFromStorage(array $urlCollection): array
    {
        if (UrlStorageConfig::isCollectorCompatibilityMode()) {
            $urlStorageData = [];
            foreach ($urlCollection as $url) {
                $urlStorageData[] = $this->getCollectorUrlData($url);
            }

            return $urlStorageData;
        }

        $storageKeys = [];
        foreach ($urlCollection as $url) {
            $storageKeys[] = $this->getUrlKey($url);
        }

        $urlStorageData = $this->storageClient->getMulti($storageKeys);

        $decodedUrlStorageDataItem = [];
        foreach ($urlStorageData as $urlStorageDataItem) {
            $decodedUrlStorageDataItem[] = $this->utilEncodingService->decodeJson($urlStorageDataItem, true);
        }

        return array_filter($decodedUrlStorageDataItem);
    }

    /**
     * @param array $urlStorageData
     *
     * @return array<\Generated\Shared\Transfer\UrlStorageTransfer>
     */
    protected function mapUrlStorageDataToUrlStorageTransfers(array $urlStorageData): array
    {
        $urlStorageTransfers = [];
        foreach ($urlStorageData as $urlStorageDataItem) {
            $urlStorageTransfers[$urlStorageDataItem[static::URL]] = (new UrlStorageTransfer())
                ->fromArray($urlStorageDataItem, true);
        }

        return $urlStorageTransfers;
    }
}
