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
            $label = -1 < $categories->numberProductsInCategory() ?
                sprintf('%s (%s)', $category->label(), $category->numberProductsInCategory()) :
                $categories->label();

            $normalizedCategories[] = [
                'attr' => [
                    'data-code' => $category->code(),
                    'id' => $category->id(),
                ],
                'state' => $category->selected(),

                'label' => $label,
                'selected' => $category->selected(),
            ];
        }

        return $normalizedCategories;
    }
}
