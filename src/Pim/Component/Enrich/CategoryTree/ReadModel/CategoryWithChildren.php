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
     * @param int                    $numberProductsInCategory
     * @param string[]               $childrenCategoryCodes
     * @param CategoryWithChildren[] $childrenCategoriesToExpand
     */
    public function __construct(
        int $id,
        string $code,
        string $label,
        int $numberProductsInCategory,
        array $childrenCategoryCodes,
        array $childrenCategoriesToExpand
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->numberProductsInCategory = $numberProductsInCategory;
        $this->childrenCategoryCodes = $childrenCategoryCodes;
        $this->childrenCategoriesToExpand = $childrenCategoriesToExpand;
    }

    /**
     * @param int    $id
     * @param string $code
     * @param string $label
     * @param array  $childrenCategoryCodes
     * @param array  $childrenCategoriesToExpand
     *
     * @return CategoryWithChildren
     */
    public static function withoutCount(
        int $id,
        string $code,
        string $label,
        array $childrenCategoryCodes,
        array $childrenCategoriesToExpand
    ): self {
        return new self($id, $code, $label, -1, $childrenCategoryCodes, $childrenCategoriesToExpand);
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
