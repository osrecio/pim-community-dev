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
class CategoryWithChildren
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var bool */
    private $isUsedAsFilter;

    /** @var int */
    private $numberProductsInCategory;

    /** @var string[] */
    private $childrenCategoryCodes;

    /** @var CategoryWithChildren[] */
    private $childrenCategoriesToExpand;

    /**
     * @param int                    $id
     * @param string                 $code
     * @param string                 $label
     * @param bool                   $isUsedAsFilter
     * @param int                    $numberProductsInCategory
     * @param string[]               $childrenCategoryCodes
     * @param CategoryWithChildren[] $childrenCategoriesToExpand
     */
    public function __construct(
        int $id,
        string $code,
        string $label,
        bool $isUsedAsFilter,
        int $numberProductsInCategory,
        array $childrenCategoryCodes,
        array $childrenCategoriesToExpand
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->isUsedAsFilter = $isUsedAsFilter;
        $this->numberProductsInCategory = $numberProductsInCategory;
        $this->childrenCategoryCodes = $childrenCategoryCodes;
        $this->childrenCategoriesToExpand = $childrenCategoriesToExpand;
    }

    /**
     * @param int    $id
     * @param string $code
     * @param string $label
     * @param bool   $toExpand
     * @param array  $childrenCategoryCodes
     * @param array  $childrenCategoriesToExpand
     *
     * @return CategoryWithChildren
     */
    public static function withoutCount(
        int $id,
        string $code,
        string $label,
        bool $toExpand,
        array $childrenCategoryCodes,
        array $childrenCategoriesToExpand
    ): self {
        return new self($id, $code, $label, $toExpand, -1, $childrenCategoryCodes, $childrenCategoriesToExpand);
    }

    /**
     * @param CategoryWithChildren   $category
     * @param CategoryWithChildren[] $childrenCategories
     * @param int                    $numberProductsInCategory
     *
     * @return CategoryWithChildren
     */
    public static function fromCategoryWithCount(
        CategoryWithChildren $category,
        array $childrenCategories,
        int $numberProductsInCategory
    ): self {
        return new self(
            $category->id(),
            $category->code(),
            $category->label(),
            $category->isUsedAsFilter(),
            $numberProductsInCategory,
            $category->childrenCategoryCodes(),
            $childrenCategories
        );
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
        return empty($this->childrenCategoryCodes);
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
     * @return string[]
     */
    public function childrenCategoryCodes(): array
    {
        return $this->childrenCategoryCodes;
    }

    /**
     * @return CategoryWithChildren[]
     */
    public function childrenCategoriesToExpand(): array
    {
        return $this->childrenCategoriesToExpand;
    }
}
