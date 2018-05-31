<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Normalizer;

use Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryWithChildren
{
    /**
     * @param ReadModel\CategoryWithChildren[] $categories
     *
     * @return array
     */
    public function normalizeList(array $categories)
    {
        $normalizedCategories = [];

        foreach ($categories as $category) {
            $label = -1 < $category->numberProductsInCategory() ?
                sprintf('%s (%s)', $category->label(), $category->numberProductsInCategory()) :
                $category->label();

            $normalizedCategories[] = [
                'attr' => [
                    'id' => 'node_' . $category->id(),
                    'data-code' => $category->code(),
                ],
                'data' => $label,
                'state' => $this->state($category),
                'children' => $this->normalizeList($category->childrenCategoriesToExpand()),
            ];
        }

        return $normalizedCategories;
    }

    /**
     * This dirty css stuff should be done on frontend side.
     *
     * @param ReadModel\CategoryWithChildren $category
     *
     * @return string
     */
    private function state(ReadModel\CategoryWithChildren $category): string
    {
        $state = $category->isLeaf() ? 'leaf' : 'closed';
        if ($category->isExpanded()) {
            $state = 'open';
        }

        if ($category->isUsedAsFilter()) {
            $state .= ' toselect jstree-checked';
        }

        return $state;
    }
}
