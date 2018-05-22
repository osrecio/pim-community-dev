<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Query;

use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithChildren;
use Pim\Component\User\Model\User;

/**
 * Class CountProductInCategories
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategories
{
    /**
     * Fetch all the trees with all the children categories of each tree.
     *
     * Example : [
     *      new CategoryWithChildren('master_1', ['master_1_1', 'master_1_2']),
     *      new CategoryWithChildren('master_2', ['master_2_1', 'master_2_1']),
     * ]
     *
     * @param string $translationLocaleCode
     * @param User   $user
     *
     * @return CategoryWithChildren[]
     */
    public function fetchTreesWithChildrenCategories(string $translationLocaleCode, User $user): array;

    /**
     * Fetch all the children categories of a given category,
     * and the children categories associated to each child category.
     *
     * Example : [
     *      new CategoryWithChildren('sub_master_1', ['sub_sub_master_1_1', 'sub_sub_master_1_2']),
     *      new CategoryWithChildren('sub_master_2', ['sub_sub_master_2_1', 'sub_sub_master_2_1']),
     * ]
     *
     * @param string $translationLocaleCode
     * @param User   $user
     * @param string $categoryCode
     *
     * @return CategoryWithChildren[]
     */
    public function fetchChildrenCategories(string $translationLocaleCode, User $user, string $categoryCode): array;
}
