<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\DummyPayment\Business;

use Spryker\Zed\DummyPayment\Business\DummyPaymentBusinessFactory;
use Spryker\Zed\DummyPayment\Business\Model\Payment\RefundInterface;

/**
 * @group Spryker
 * @group Zed
 * @group DummyPayment
 * @group DummyPaymentDependencyProvider
 */
class DummyPaymentBusinessFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testCreateRefundShouldReturnRefundInterface()
    {
        $dummyPaymentBusinessFactory = new DummyPaymentBusinessFactory();
        $refund = $dummyPaymentBusinessFactory->createRefund();

        $this->assertInstanceOf(RefundInterface::class, $refund);
    }

}