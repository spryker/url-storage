<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\UrlStorage;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\UrlStorageResourceMapTransfer;
use Generated\Shared\Transfer\UrlStorageTransfer;
use ReflectionClass;
use Spryker\Client\UrlStorage\Dependency\Client\UrlStorageToStorageInterface;
use Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToSynchronizationServiceInterface;
use Spryker\Client\UrlStorage\Dependency\Service\UrlStorageToUtilEncodingServiceInterface;
use Spryker\Client\UrlStorage\Storage\UrlStorageReader;
use Spryker\Client\UrlStorage\UrlStorageDependencyProvider;
use Spryker\Service\Synchronization\Dependency\Plugin\SynchronizationKeyGeneratorPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group UrlStorage
 * @group UrlStorageClientTest
 * Add your own group annotations below this line
 */
class UrlStorageClientTest extends Unit
{
    protected const string TEST_URL = '/en/test-product';

    protected const string TEST_LOCALE = 'en_US';

    protected const string STORAGE_KEY = 'url:/en/test-product';

    protected const string RESOURCE_KEY = 'product_abstract:en_us:1';

    protected const int LOCALE_ID = 66;

    protected UrlStorageClientTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset static key builder cache so each test uses fresh mocks
        $property = (new ReflectionClass(UrlStorageReader::class))->getProperty('storageKeyBuilder');
        $property->setValue(null, null);
    }

    public function testGivenUrlNotFoundInStorageWhenCheckingHasUrlThenReturnsFalse(): void
    {
        // Arrange
        $this->mockExternalDependencies(storageData: []);

        // Act
        $result = $this->tester->getClient()->hasUrl(static::TEST_URL, static::TEST_LOCALE);

        // Assert
        $this->assertFalse($result);
    }

    public function testGivenUrlFoundButNoResourceMapperHandlesWhenCheckingHasUrlThenReturnsFalse(): void
    {
        // Arrange
        $this->mockExternalDependencies(
            storageData: $this->buildStorageEntry($this->buildUrlDetails()),
            plugins: [],
        );

        // Act
        $result = $this->tester->getClient()->hasUrl(static::TEST_URL, static::TEST_LOCALE);

        // Assert
        $this->assertFalse($result);
    }

    public function testGivenUrlFoundAndResourceMapperHandlesWhenCheckingHasUrlThenReturnsTrue(): void
    {
        // Arrange
        $this->mockExternalDependencies(
            storageData: $this->buildStorageEntry($this->buildUrlDetails()),
            plugins: [$this->createResourceMapperPlugin()],
        );

        // Act
        $result = $this->tester->getClient()->hasUrl(static::TEST_URL, static::TEST_LOCALE);

        // Assert
        $this->assertTrue($result);
    }

    public function testGivenNullLocaleNameWhenCheckingHasUrlThenDerivesLocaleFromUrlDetailsAndReturnsTrue(): void
    {
        // Arrange
        $urlDetails = $this->buildUrlDetails(withLocaleUrls: true);
        $this->mockExternalDependencies(
            storageData: $this->buildStorageEntry($urlDetails),
            plugins: [$this->createResourceMapperPlugin()],
        );

        // Act
        $result = $this->tester->getClient()->hasUrl(static::TEST_URL, null);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @param array<string, string> $storageData
     * @param array<\Spryker\Client\UrlStorage\Dependency\Plugin\UrlStorageResourceMapperPluginInterface> $plugins
     */
    protected function mockExternalDependencies(array $storageData, array $plugins = []): void
    {
        $keyBuilderMock = $this->createMock(SynchronizationKeyGeneratorPluginInterface::class);
        $keyBuilderMock->method('generateKey')->willReturn(static::STORAGE_KEY);

        $syncServiceMock = $this->createMock(UrlStorageToSynchronizationServiceInterface::class);
        $syncServiceMock->method('getStorageKeyBuilder')->willReturn($keyBuilderMock);

        $encodingServiceMock = $this->createMock(UrlStorageToUtilEncodingServiceInterface::class);
        $encodingServiceMock->method('decodeJson')->willReturnCallback(
            static fn (string $json): ?array => json_decode($json, true),
        );

        $storageMock = $this->createMock(UrlStorageToStorageInterface::class);
        $storageMock->method('getMulti')->willReturn($storageData);

        $this->tester->setDependency(UrlStorageDependencyProvider::CLIENT_STORAGE, $storageMock);
        $this->tester->setDependency(UrlStorageDependencyProvider::SERVICE_SYNCHRONIZATION, $syncServiceMock);
        $this->tester->setDependency(UrlStorageDependencyProvider::SERVICE_UTIL_ENCODING, $encodingServiceMock);
        $this->tester->setDependency(UrlStorageDependencyProvider::PLUGINS_URL_STORAGE_RESOURCE_MAPPER, $plugins);
    }

    /**
     * @param array<string, mixed> $urlDetails
     *
     * @return array<string, string>
     */
    protected function buildStorageEntry(array $urlDetails): array
    {
        return [static::STORAGE_KEY => json_encode($urlDetails)];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildUrlDetails(bool $withLocaleUrls = false): array
    {
        $details = [
            'id_url' => 1,
            'url' => static::TEST_URL,
            'fk_locale' => static::LOCALE_ID,
        ];

        if ($withLocaleUrls) {
            $details['locale_urls'] = [
                [
                    'fk_locale' => static::LOCALE_ID,
                    'locale_name' => static::TEST_LOCALE,
                    'url' => static::TEST_URL,
                ],
            ];
        }

        return $details;
    }

    protected function createResourceMapperPlugin(): UrlStorageResourceMapperPluginInterface
    {
        return new class implements UrlStorageResourceMapperPluginInterface {
            public function map(UrlStorageTransfer $urlStorageTransfer, array $options = []): UrlStorageResourceMapTransfer
            {
                return (new UrlStorageResourceMapTransfer())->setResourceKey('product_abstract:en_us:1');
            }
        };
    }
}
