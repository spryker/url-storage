<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\UrlStorage;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToLocaleClientInterface;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStoreClientInterface;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToUtilEncodingServiceInterface;
use Spryker\Client\UrlStorage\KeyBuilder\UrlRedirectStorageKeyBuilder;
use Spryker\Client\UrlStorage\KeyBuilder\UrlRedirectStorageKeyBuilderInterface;
use Spryker\Client\UrlStorage\Mapper\UrlRedirectStorageMapper;
use Spryker\Client\UrlStorage\Mapper\UrlRedirectStorageMapperInterface;
use Spryker\Client\UrlStorage\Storage\UrlRedirectStorageReader;
use Spryker\Client\UrlStorage\Storage\UrlRedirectStorageReaderInterface;
use Spryker\Client\UrlStorage\Storage\UrlStorageReader;

/**
 * @method \Spryker\Client\UrlStorage\UrlStorageConfig getConfig()
 */
class UrlStorageFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Client\UrlStorage\Storage\UrlStorageReaderInterface
     */
    public function createUrlStorageReader()
    {
        return new UrlStorageReader(
            $this->getStorageClient(),
            $this->getSynchronizationService(),
            $this->getUtilEncodingService(),
            $this->getUrlStorageResourceMapperPlugins(),
        );
    }

    /**
     * @return \Spryker\Client\UrlStorage\Mapper\UrlRedirectStorageMapperInterface
     */
    public function createUrlRedirectStorageMapper(): UrlRedirectStorageMapperInterface
    {
        return new UrlRedirectStorageMapper();
    }

    /**
     * @return \Spryker\Client\UrlStorage\Storage\UrlRedirectStorageReaderInterface
     */
    public function createUrlRedirectStorageReader(): UrlRedirectStorageReaderInterface
    {
        return new UrlRedirectStorageReader(
            $this->getStorageClient(),
            $this->createUrlRedirectStorageKeyBuilder(),
            $this->createUrlRedirectStorageMapper(),
        );
    }

    /**
     * @return \Spryker\Client\UrlStorage\KeyBuilder\UrlRedirectStorageKeyBuilderInterface
     */
    public function createUrlRedirectStorageKeyBuilder(): UrlRedirectStorageKeyBuilderInterface
    {
        return new UrlRedirectStorageKeyBuilder(
            $this->getSynchronizationService(),
            $this->getStoreClient(),
            $this->getLocaleClient(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStorageInterface
     */
    public function getStorageClient()
    {
        return $this->getProvidedDependency(UrlStorageDependencyProvider::CLIENT_STORAGE);
    }

    /**
     * @return \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface
     */
    public function getSynchronizationService()
    {
        return $this->getProvidedDependency(UrlStorageDependencyProvider::SERVICE_SYNCHRONIZATION);
    }

    /**
     * @return array<\Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface>
     */
    public function getUrlStorageResourceMapperPlugins()
    {
        return $this->getProvidedDependency(UrlStorageDependencyProvider::PLUGINS_URL_STORAGE_RESOURCE_MAPPER);
    }

    /**
     * @return \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStoreClientInterface
     */
    public function getStoreClient(): UrlStorageToStoreClientInterface
    {
        return $this->getProvidedDependency(UrlStorageDependencyProvider::CLIENT_STORE);
    }

    /**
     * @return \Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToLocaleClientInterface
     */
    public function getLocaleClient(): UrlStorageToLocaleClientInterface
    {
        return $this->getProvidedDependency(UrlStorageDependencyProvider::CLIENT_LOCALE);
    }

    /**
     * @return \Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): UrlStorageToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(UrlStorageDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
