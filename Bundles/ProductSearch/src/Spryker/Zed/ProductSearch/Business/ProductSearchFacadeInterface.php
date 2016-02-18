<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\ProductSearch\Business;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Messenger\Business\Model\MessengerInterface;

interface ProductSearchFacadeInterface
{

    /**
     * @param array $productsRaw
     * @param array $processedProducts
     *
     * @return array
     */
    public function enrichProductsWithSearchAttributes(array $productsRaw, array $processedProducts);

    /**
     * @param array $productsRaw
     * @param array $processedProducts
     * @param \Generated\Shared\Transfer\LocaleTransfer $locale
     *
     * @return array
     */
    public function createSearchProducts(array $productsRaw, array $processedProducts, LocaleTransfer $locale);

    /**
     * @param \Spryker\Zed\Messenger\Business\Model\MessengerInterface $messenger
     *
     * @return void
     */
    public function install(MessengerInterface $messenger);

}