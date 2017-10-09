<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Communication\Console;

use Orm\Zed\Shipment\Persistence\SpyShipmentMethod;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodPrice;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodPriceQuery;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @method \Spryker\Zed\Shipment\Communication\ShipmentCommunicationFactory getFactory()
 */
class MigrateShipmentMethodPricesConsole extends Console
{

    const COMMAND_NAME = 'shipment:shipment-method-price:migrate';
    const COMMAND_DESCRIPTION = 'Console command to migrate shipment prices to multi currency implementation.';

    /**
     * @var array
     */
    protected $currencyCache = [];

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::COMMAND_DESCRIPTION);

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storeTransferCollection = $this->getFactory()->getStoreFacade()->getAllStores();
        $shipmentMethodCollection = SpyShipmentMethodQuery::create()->find();

        if (count($shipmentMethodCollection) === 0) {
            $output->writeln('There are no shipment methods to migrate.');
            return;
        }
        if (count($storeTransferCollection) === 0) {
            $output->writeln('There are no stores set up to migrate.');
            return;
        }

        $question = new ConfirmationQuestion(
            sprintf('Migrate %s shipment methods? (y|n)', count($shipmentMethodCollection)),
            false
        );

        if (!$this->getQuestionHelper()->ask($input, $output, $question)) {
            $output->writeln('Aborted.');
            return;
        }

        $storeCurrencies = $this->getStoreCurrencies($storeTransferCollection);
        $defaultIdStore = $this->getDefaultIdStore();
        $defaultIdCurrency = $this->getDefaultIdCurrency();

        foreach ($shipmentMethodCollection as $shipmentMethodEntity) {
            $this->processShipmentMethod($shipmentMethodEntity, $storeCurrencies, $defaultIdStore, $defaultIdCurrency);

            $output->writeln(sprintf('Shipment method %d is migrated.', $shipmentMethodEntity->getIdShipmentMethod()));
        }

        $output->writeln('done.');
    }

    /**
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethod $shipmentMethodEntity
     * @param array $storeCurrencies
     * @param int $defaultIdStore
     * @param int $defaultIdCurrency
     *
     * @return void
     */
    protected function processShipmentMethod(SpyShipmentMethod $shipmentMethodEntity, array $storeCurrencies, $defaultIdStore, $defaultIdCurrency)
    {
        foreach ($storeCurrencies as list($idStore, $idCurrency)) {
            $shipmentMethodPriceEntity = SpyShipmentMethodPriceQuery::create()
                ->filterByFkShipmentMethod($shipmentMethodEntity->getIdShipmentMethod())
                ->filterByFkStore($idStore)
                ->filterByFkCurrency($idCurrency)
                ->findOneOrCreate();

            $isDefaultStoreCurrency = $idStore === $defaultIdStore && $idCurrency === $defaultIdCurrency;

            $this->setNetPrice($shipmentMethodPriceEntity, $shipmentMethodEntity, $isDefaultStoreCurrency);
            $this->setGrossPrice($shipmentMethodPriceEntity, $shipmentMethodEntity, $isDefaultStoreCurrency);

            $shipmentMethodPriceEntity->save();
        }
    }

    /**
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethodPrice $shipmentMethodPrice
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethod $shipmentMethod
     * @param bool $isDefaultStoreCurrency
     *
     * @return void
     */
    protected function setNetPrice(SpyShipmentMethodPrice $shipmentMethodPrice, SpyShipmentMethod $shipmentMethod, $isDefaultStoreCurrency)
    {
        if ($shipmentMethodPrice->getDefaultNetPrice() !== null) {
            return;
        }

        $shipmentMethodPrice->setDefaultNetPrice($isDefaultStoreCurrency ? (int)$shipmentMethod->getDefaultPrice() : 0);
    }

    /**
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethodPrice $shipmentMethodPrice
     * @param \Orm\Zed\Shipment\Persistence\SpyShipmentMethod $shipmentMethod
     * @param bool $isDefaultStoreCurrency
     *
     * @return void
     */
    protected function setGrossPrice(SpyShipmentMethodPrice $shipmentMethodPrice, SpyShipmentMethod $shipmentMethod, $isDefaultStoreCurrency)
    {
        if ($shipmentMethodPrice->getDefaultGrossPrice() !== null) {
            return;
        }

        $shipmentMethodPrice->setDefaultGrossPrice($isDefaultStoreCurrency ? (int)$shipmentMethod->getDefaultPrice() : 0);
    }

    /**
     * Returns with a list of available store-currency id pairs.
     *
     * Example:
     *   Store 1 has currency 5, 6
     *   Store 2 has currency 10
     *   Result: [
     *              [1, 5],
     *              [1, 6],
     *              [3, 10]
     *           ]
     *
     * @param \Generated\Shared\Transfer\StoreTransfer[] $storeTransferCollection
     *
     * @return array
     */
    protected function getStoreCurrencies(array $storeTransferCollection)
    {
        $currencies = [];

        foreach ($storeTransferCollection as $storeTransfer) {
            foreach ($storeTransfer->getAvailableCurrencyIsoCodes() as $isoCode) {
                $currencies[] = [$storeTransfer->getIdStore(), $this->getIdCurrencyByIsoCode($isoCode)];
            }
        }

        return $currencies;
    }

    /**
     * @param string $currencyIsoCode
     *
     * @return int
     */
    protected function getIdCurrencyByIsoCode($currencyIsoCode)
    {
        if (!isset($this->currencyCache[$currencyIsoCode])) {
            $this->currencyCache[$currencyIsoCode] = $this->getFactory()
                ->getCurrencyFacade()
                ->fromIsoCode($currencyIsoCode)
                ->getIdCurrency();
        }

        return $this->currencyCache[$currencyIsoCode];
    }

    /**
     * @return int
     */
    protected function getDefaultIdCurrency()
    {
        return $this->getIdCurrencyByIsoCode(
            $this->getFactory()
                ->getStoreFacade()
                ->getCurrentStore()
                ->getDefaultCurrencyIsoCode()
        );
    }

    /**
     * @return int
     */
    protected function getDefaultIdStore()
    {
        return $this->getFactory()->getStoreFacade()->getCurrentStore()->getIdStore();
    }

    /**
     * @return \Symfony\Component\Console\Helper\QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

}
