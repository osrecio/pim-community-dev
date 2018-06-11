<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\CategoryTree\Query;
use Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCountHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var UserContext */
    private $userContext;

    /** @var Query\ListRootCategoriesWithCount */
    private $listAndCountIncludingSubCategories;

    /** @var Query\ListRootCategoriesWithCount */
    private $listAndCountWithoutIncludingSubCategories;

    /**
     * @param CategoryRepositoryInterface       $categoryRepository
     * @param UserContext                       $userContext
     * @param Query\ListRootCategoriesWithCount $listAndCountIncludingSubCategories
     * @param Query\ListRootCategoriesWithCount $listAndCountWithoutIncludingSubCategories
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        Query\ListRootCategoriesWithCount $listAndCountIncludingSubCategories,
        Query\ListRootCategoriesWithCount $listAndCountWithoutIncludingSubCategories
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->userContext = $userContext;
        $this->listAndCountIncludingSubCategories = $listAndCountIncludingSubCategories;
        $this->listAndCountWithoutIncludingSubCategories = $listAndCountWithoutIncludingSubCategories;
    }

    /**
     * @param ListRootCategoriesWithCount $parameters
     *
     * @return ReadModel\RootCategory[]
     */
    public function list(ListRootCategoriesWithCount $parameters): array
    {
        $categoryToFilter = -1 !== $parameters->categoryIdToFilterWith() ?
            $this->categoryRepository->find($parameters->categoryIdToFilterWith()) : null;

        // TODO: don't use user context but a query instead
        if (null === $categoryToFilter) {
            $categoryToFilter = $this->userContext->getUserProductCategoryTree();
        }
        $rootCategoryIdToExpand = $categoryToFilter->getRoot();

        $rootCategories = $parameters->countIncludingSubCategories() ?
            $this->listAndCountIncludingSubCategories->list(
                $parameters->translationLocale(),
                $parameters->user(),
                $rootCategoryIdToExpand
            ) :
            $this->listAndCountWithoutIncludingSubCategories->list(
                $parameters->translationLocale(),
                $parameters->user(),
                $rootCategoryIdToExpand
            );

        return $rootCategories;
    }
}
