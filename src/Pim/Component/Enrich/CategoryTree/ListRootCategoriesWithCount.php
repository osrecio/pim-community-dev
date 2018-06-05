<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCount
{
    /** @var int */
    private $treeToExpand;

    /** @var bool */
    private $countIncludingSubCategories;

    /**
     * @param int  $treeToExpand
     * @param bool $countIncludingSubCategories
     */
    public function __construct(int $treeToExpand, bool $countIncludingSubCategories)
    {
        $this->treeToExpand = $treeToExpand;
        $this->countIncludingSubCategories = $countIncludingSubCategories;
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
    public function countIncludingSubCategories(): bool
    {
        return $this->countIncludingSubCategories;
    }
}
