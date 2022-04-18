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
 * @deprecated Use {@link \Spryker\Zed\UrlStorage\Communication\Plugin\Event\Listener\RedirectStoragePublishListener}
 *   and {@link \Spryker\Zed\UrlStorage\Communication\Plugin\Event\Listener\RedirectStorageUnpublishListener} instead.
 *
 * @method \Spryker\Zed\UrlStorage\Persistence\UrlStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\UrlStorage\Communication\UrlStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\UrlStorage\Business\UrlStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\UrlStorage\UrlStorageConfig getConfig()
 */
class RedirectStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
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
        $redirectIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        if ($eventName === UrlEvents::ENTITY_SPY_URL_REDIRECT_CREATE || $eventName === UrlEvents::ENTITY_SPY_URL_REDIRECT_UPDATE) {
            $this->getFacade()->publishRedirect($redirectIds);
        } elseif ($eventName === UrlEvents::ENTITY_SPY_URL_REDIRECT_DELETE) {
            $this->getFacade()->unpublishRedirect($redirectIds);
        }
    }
}
