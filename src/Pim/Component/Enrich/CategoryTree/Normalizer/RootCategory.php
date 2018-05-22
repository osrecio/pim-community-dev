<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Normalizer;

use Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootCategory
{
    /**
     * @param ReadModel\RootCategory[] $rootCategories
     *
     * @return array
     */
    public function normalizeList(array $rootCategories)
    {
        $normalizedCategories = [];

        foreach ($rootCategories as $rootCategory) {
            $label = -1 < $rootCategory->getNumberProductsInCategory() ?
                sprintf('%s (%s)', $rootCategory->getLabel(), $rootCategory->getNumberProductsInCategory()) :
                $rootCategory->getLabel();

            $normalizedCategories[] = [
                'id' => $rootCategory->getId(),
                'code' => $rootCategory->getCode(),
                'label' => $label,
                'selected' => $rootCategory->isSelected(),
            ];
        }

        return $normalizedCategories;
    }
}
