<?php
/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Collector\Helper;

use Codeception\Module;
use DateTime;
use Orm\Zed\Touch\Persistence\Map\SpyTouchTableMap;
use Spryker\Zed\Collector\Business\Exporter\Reader\ReaderInterface;
use Spryker\Zed\Collector\Business\Exporter\Writer\TouchUpdaterInterface;
use Spryker\Zed\Collector\Business\Exporter\Writer\WriterInterface;
use Spryker\Zed\Collector\Business\Model\BatchResult;
use Spryker\Zed\Collector\CollectorConfig;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use Spryker\Zed\PropelOrm\Business\Model\Formatter\PropelArraySetFormatter;
use Spryker\Zed\Touch\Persistence\TouchQueryContainer;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;
use Symfony\Component\Console\Output\NullOutput;

class CollectorDataHelper extends Module
{
    use LocatorHelperTrait;

    /**
     * @param \Spryker\Zed\Kernel\Business\AbstractFacade $facade
     * @param string $facadeCollectorMethod
     * @param string $resourceType
     *
     * @return array
     */
    public function runCollector(AbstractFacade $facade, $facadeCollectorMethod, $resourceType)
    {
        $localeTransfer = $this->getLocaleFacade()->getCurrentLocale();

        $baseQuery = $this->createTouchBaseQuery($resourceType, $localeTransfer);

        $writerMock = $this->getWriterMock();

        $collectedData = [];
        $writerMock->method('write')->with(
            $this->callback(function($data) use(&$collectedData) {
                $collectedData[] = $data;
                return $data;
            }
            ));

        $facade->$facadeCollectorMethod(
            $baseQuery,
            $localeTransfer,
            new BatchResult(),
            $this->getDataReaderMock(),
            $writerMock,
            $this->getTouchUpdaterMock(),
            new NullOutput()
        );

        return $collectedData;
    }

    /**
     * @return \Spryker\Zed\Locale\Business\LocaleFacadeInterface
     */
    public function getLocaleFacade()
    {
        return $this->getLocator()->locale()->facade();
    }

    /**
     * @return \Spryker\Zed\Touch\Persistence\TouchQueryContainerInterface
     */
    public function getTouchQueryContainer()
    {
        return $this->getLocator()->touch()->queryContainer();
    }

    /**
     * @return \Spryker\Zed\Collector\Business\Exporter\Reader\ReaderInterface
     */
    protected function getDataReaderMock()
    {
        return $this->getMockBuilder(ReaderInterface::class)->getMock();
    }

    /**
     * @return \Spryker\Zed\Collector\Business\Exporter\Writer\WriterInterface
     */
    protected function getWriterMock()
    {
        return $this->getMockBuilder(WriterInterface::class)->getMock();
    }

    /**
     * @return mixed
     */
    protected function getTouchUpdaterMock()
    {
        return $this->getMockBuilder(TouchUpdaterInterface::class)->getMock();
    }

    /**
     * @param string $resourceType
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return $this|\Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected function createTouchBaseQuery($resourceType, $localeTransfer)
    {
        return $this->getTouchQueryContainer()
            ->createBasicExportableQuery(
            $resourceType,
            $localeTransfer,
            new DateTime('Yesterday')
        )->withColumn(SpyTouchTableMap::COL_ID_TOUCH, CollectorConfig::COLLECTOR_TOUCH_ID)
         ->withColumn(SpyTouchTableMap::COL_ITEM_ID, CollectorConfig::COLLECTOR_RESOURCE_ID)
         ->setFormatter(new PropelArraySetFormatter());
    }
}
