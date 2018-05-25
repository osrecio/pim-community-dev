<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesParameters
{
    /** @var int */
    private $childrenCategoryIdToExpand;

    /** @var int */
    private $categoryIdToFilterWith;

    /** @var bool */
    private $countProductsInCategories;

    /** @var bool */
    private $countByIncludingSubCategories;

    /**
     * @param int  $childrenCategoryIdToDisplay
     * @param int  $categoryIdToFilterWith
     * @param bool $countProductsInCategories
     * @param bool $countByIncludingSubCategories
     */
    public function __construct(
        int $childrenCategoryIdToDisplay,
        int $categoryIdToFilterWith,
        bool $countProductsInCategories,
        bool $countByIncludingSubCategories
    ) {
        $this->childrenCategoryIdToExpand = $childrenCategoryIdToDisplay;
        $this->categoryIdToFilterWith = $categoryIdToFilterWith;
        $this->countProductsInCategories = $countProductsInCategories;
        $this->countByIncludingSubCategories = $countByIncludingSubCategories;
    }

    /**
     * The category to display is the category that is choosed by the user to be expanded.
     *
     * Do note that the user can expand a category without selecting it as a filter.
     * Therefore, the category to expand can be different from the selected category.
     *
     * @return int
     */
    public function childrenCategoryIdToExpand(): int
    {
        return $this->childrenCategoryIdToExpand;
    }

    /**
     * This category is the category that is selected by the user to filter the product grid.
     * It is useful when:
     *  - the user display the tree
     *  - select a category as filter
     *  - go on another page
     *  - the user go back ont the page to display the tree
     *
     * The tree has to be displayed with the category selected as filter, in order to not loose filters when browsing the application.
     *
     * So, we have to return all the children recursively until this selected category.
     * The correct solution would be to not reload entirely the tree on the front-end part and keep a state of it.
     *
     * @return int
     */
    public function categoryIdToFilterWith(): int
    {
        return $this->categoryIdToFilterWith;
    }

    /**
     * @return bool
     */
    public function countProductsInCategories(): bool
    {
        return $this->countProductsInCategories;
    }

    /**
     * @return bool
     */
    public function countByIncludingSubCategories(): bool
    {
        return $this->countByIncludingSubCategories;
    }
}
