<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesParameters
{
    /** @var int */
    private $treeToExpand;

    /** @var bool */
    private $countProductsInCategories;

    /** @var bool */
    private $countByIncludingSubCategories;

    /**
     * @param int  $treeToExpand
     * @param bool $countProductsInCategories
     * @param bool $countByIncludingSubCategories
     */
    public function __construct(int $treeToExpand, bool $countProductsInCategories, bool $countByIncludingSubCategories)
    {
        $this->treeToExpand = $treeToExpand;
        $this->countProductsInCategories = $countProductsInCategories;
        $this->countByIncludingSubCategories = $countByIncludingSubCategories;
    }

    /**
     * @return int
     */
    public function treeToExpand(): int
    {
        return $this->treeToExpand;
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
