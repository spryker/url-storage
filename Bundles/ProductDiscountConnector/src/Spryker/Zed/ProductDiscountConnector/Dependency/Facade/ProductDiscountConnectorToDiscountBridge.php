<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductDiscountConnector\Dependency\Facade;

use Generated\Shared\Transfer\ClauseTransfer;

class ProductDiscountConnectorToDiscountBridge implements ProductDiscountConnectorToDiscountInterface
{

    /**
     * @var \Spryker\Zed\Discount\Business\DiscountFacade
     */
    protected $discountFacade;

    /**
     * @param \Spryker\Zed\Discount\Business\DiscountFacade $discountFacade
     */
    public function __construct($discountFacade)
    {
        $this->discountFacade = $discountFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     * @param mixed $compareWith
     *
     * @return bool
     */
    public function queryStringCompare(ClauseTransfer $clauseTransfer, $compareWith)
    {
        return $this->discountFacade->queryStringCompare($clauseTransfer, $compareWith);
    }

}