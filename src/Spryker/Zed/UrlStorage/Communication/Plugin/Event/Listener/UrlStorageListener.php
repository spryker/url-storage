<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\UrlStorage\Communication\Plugin\Event\Listener;

use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Url\Dependency\UrlEvents;

/**
 * @deprecated Use {@link \Spryker\Zed\UrlStorage\Communication\Plugin\Event\Listener\UrlStoragePublishListener}
 *   and {@link \Spryker\Zed\UrlStorage\Communication\Plugin\Event\Listener\UrlStorageUnpublishListener} instead.
 *
 * @method \Spryker\Zed\UrlStorage\Persistence\UrlStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\UrlStorage\Communication\UrlStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\UrlStorage\Business\UrlStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\UrlStorage\UrlStorageConfig getConfig()
 */
class UrlStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    /**
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $urlIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        if (
            $eventName === UrlEvents::ENTITY_SPY_URL_DELETE ||
            $eventName === UrlEvents::URL_UNPUBLISH
        ) {
            $this->getFacade()->unpublishUrl($urlIds);

            return;
        }

        $this->getFacade()->publishUrl($urlIds);
    }
}
