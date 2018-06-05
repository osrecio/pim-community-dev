<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Query;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithChildren;
use Pim\Component\Enrich\CategoryTree\ReadModel\RootCategory;
use Pim\Component\User\Model\User;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetRootCategoriesWithoutCount
{
    /**
     * Fetch all the trees with all the children categories of each tree.
     *
     * @param LocaleInterface $translationTranslationLocale
     * @param User            $user
     *
     * @return RootCategoryWithoutCount[]
     */
    public function get(LocaleInterface $translationTranslationLocale, User $user): array;
}
