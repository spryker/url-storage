<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Acceptance\Category\Category\Zed;

use Acceptance\Category\Category\Zed\PageObject\Category;
use Acceptance\Category\Category\Zed\PageObject\CategoryReSortPage;
use Acceptance\Category\Category\Zed\Tester\CategoryReSortTester;

/**
 * @group Acceptance
 * @group Category
 * @group Category
 * @group Zed
 * @group CategoryReSortCest
 */
class CategoryReSortCest
{

    /**
     * @param \Acceptance\Category\Category\Zed\Tester\CategoryReSortTester $i
     *
     * @return void
     */
    public function testThatICanSeeSubCategories(CategoryReSortTester $i)
    {
        $i->amOnPage(CategoryReSortPage::URL);
        $i->waitForElement(CategoryReSortPage::SELECTOR_CATEGORY_LIST);
        $i->canSeeElement(CategoryReSortPage::SELECTOR_FIRST_SUB_CATEGORY);
    }

    /**
     * @param \Acceptance\Category\Category\Zed\Tester\CategoryReSortTester $i
     *
     * @return void
     */
    public function testThatICanMoveCategories(CategoryReSortTester $i)
    {
        $i->amOnPage(CategoryReSortPage::URL);
        $i->waitForElement(CategoryReSortPage::SELECTOR_CATEGORY_LIST);

        $firstItemName = $i->grabTextFrom(CategoryReSortPage::SELECTOR_FIRST_SUB_CATEGORY_NAME_CELL);

        $i->dragAndDrop(
            CategoryReSortPage::SELECTOR_FIRST_SUB_CATEGORY,
            CategoryReSortPage::SELECTOR_LAST_SUB_CATEGORY
        );
        $i->canSee($firstItemName, CategoryReSortPage::SELECTOR_LAST_SUB_CATEGORY);
    }

    /**
     * @param \Acceptance\Category\Category\Zed\Tester\CategoryReSortTester $i
     *
     * @return void
     */
    public function testThatICanSaveReSortedSubCategories(CategoryReSortTester $i)
    {
        $i->createCategory(Category::CATEGORY_A);

        $i->amOnPage(CategoryReSortPage::URL);
        $i->waitForElement(CategoryReSortPage::SELECTOR_CATEGORY_LIST);

        $lastItemName = $i->grabTextFrom(CategoryReSortPage::SELECTOR_LAST_SUB_CATEGORY_NAME_CELL);

        $i->dragAndDrop(
            CategoryReSortPage::SELECTOR_LAST_SUB_CATEGORY,
            CategoryReSortPage::SELECTOR_FIRST_SUB_CATEGORY
        );
        // This is necessary to move the category under oberservation to the first position in the list
        // Unfortunately dragAndDrop() doesn't move the dragged category to the first position so we have to move the
        // top category down.
        $i->dragAndDrop(
            CategoryReSortPage::SELECTOR_FIRST_SUB_CATEGORY,
            CategoryReSortPage::SELECTOR_SECOND_SUB_CATEGORY
        );
        $i->click(CategoryReSortPage::SELECTOR_SAVE_BUTTON);
        $i->waitForElement(CategoryReSortPage::SELECTOR_ALERT_BOX);
        $i->canSee('Success', CategoryReSortPage::SELECTOR_ALERT_BOX);

        $i->amOnPage(CategoryReSortPage::URL);
        $i->canSee($lastItemName, CategoryReSortPage::SELECTOR_FIRST_SUB_CATEGORY_NAME_CELL);
    }

}