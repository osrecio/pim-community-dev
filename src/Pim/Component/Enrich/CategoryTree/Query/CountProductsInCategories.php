<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Query;

use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithChildren;
use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithCount;

/**
 * Class CountProductInCategories
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountProductsInCategories
{
    /**
     * @param CategoryWithChildren[] $categories
     *
     * @return CategoryWithCount[]
     */
    public function countByIncludingSubCategories(array $categories): array;

    /**
     * @param CategoryWithChildren[] $categories
     *
     * @return CategoryWithCount[]
     */
    public function countWithoutIncludingSubCategories(array $categories): array;
}
