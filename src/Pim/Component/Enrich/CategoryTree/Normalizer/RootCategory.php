<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Normalizer;

use Pim\Component\Enrich\CategoryTree\ReadModel;

/**
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
            $label = sprintf('%s (%s)', $rootCategory->label(), $rootCategory->numberProductsInCategory());

            $normalizedCategories[] = [
                'id' => $rootCategory->id(),
                'code' => $rootCategory->code(),
                'label' => $label,
                'selected' => $rootCategory->selected() ? 'true' : 'false',
            ];
        }

        return $normalizedCategories;
    }
}
