<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree\Query;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Pim\Component\Enrich\CategoryTree\ReadModel;
use Pim\Component\User\Model\UserInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListRootCategoriesWithCount
{
    /**
     * @param LocaleInterface $translationLocale
     * @param UserInterface   $user
     * @param int             $rootCategoryIdToExpand
     *
     * @return ReadModel\RootCategory[]
     */
    public function list(LocaleInterface $translationLocale, UserInterface $user, int $rootCategoryIdToExpand): array;
}
