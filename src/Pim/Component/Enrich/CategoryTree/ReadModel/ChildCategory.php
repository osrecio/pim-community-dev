<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * DTO representing a category to expand in the tree, with all the children to expand as well.
 * As the children to expand are the same DTO, the tree can be recursively expanded until a given depth.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChildCategory
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var bool */
    private $isUsedAsFilter;

    /** @var bool */
    private $isLeaf;

    /** @var int */
    private $numberProductsInCategory;

    /** @var ChildCategory[] */
    private $childrenCategoriesToExpand;

    /**
     * @param int             $id
     * @param string          $code
     * @param string          $label
     * @param bool            $isUsedAsFilter
     * @param bool            $isLeaf
     * @param int             $numberProductsInCategory
     * @param ChildCategory[] $childrenCategoriesToExpand
     */
    public function __construct(
        int $id,
        string $code,
        string $label,
        bool $isUsedAsFilter,
        bool $isLeaf,
        int $numberProductsInCategory,
        array $childrenCategoriesToExpand
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->isUsedAsFilter = $isUsedAsFilter;
        $this->isLeaf = $isLeaf;
        $this->numberProductsInCategory = $numberProductsInCategory;
        $this->childrenCategoriesToExpand = $childrenCategoriesToExpand;
    }


    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isUsedAsFilter(): bool
    {
        return $this->isUsedAsFilter;
    }

    /**
     * @return bool
     */
    public function isLeaf(): bool
    {
        return $this->isLeaf;
    }

    /**
     * @return bool
     */
    public function isExpanded(): bool
    {
        return !empty($this->childrenCategoriesToExpand);
    }

    /**
     * @return int
     */
    public function numberProductsInCategory(): int
    {
        return $this->numberProductsInCategory;
    }

    /**
     * @return ChildCategory[]
     */
    public function childrenCategoriesToExpand(): array
    {
        return $this->childrenCategoriesToExpand;
    }
}
