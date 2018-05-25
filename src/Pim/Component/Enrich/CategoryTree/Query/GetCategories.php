<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Query;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
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
     * @param LocaleInterface $translationLocale
     * @param User            $user
     *
     * @return CategoryWithChildren[]
     */
    public function fetchTreesWithChildrenCategories(LocaleInterface $translationLocale, User $user): array;

    /**
     * Fetch all the children categories of a given category,
     * and the children categories associated to each child category.
     *
     * Example : [
     *      new CategoryWithChildren('sub_master_1', ['sub_sub_master_1_1', 'sub_sub_master_1_2']),
     *      new CategoryWithChildren('sub_master_2', ['sub_sub_master_2_1', 'sub_sub_master_2_1']),
     * ]
     *
     * @param LocaleInterface   $translationLocaleCode
     * @param User              $user
     * @param CategoryInterface $parentCategoryToExpand
     * @param CategoryInterface $childCategoryToExpand
     *
     * @return CategoryWithChildren[]
     */
    public function fetchChildrenCategories(
        LocaleInterface $translationLocaleCode,
        User $user,
        CategoryInterface $parentCategoryToExpand,
        CategoryInterface $childCategoryToExpand
    ): array;
}
