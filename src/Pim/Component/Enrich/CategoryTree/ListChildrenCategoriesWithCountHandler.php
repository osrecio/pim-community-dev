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
class ListChildrenCategoriesWithCountHandler
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var UserContext */
    private $userContext;

    /** @var Query\ListChildrenCategoriesWithCount */
    private $listAndCountIncludingSubCategories;

    /** @var Query\ListChildrenCategoriesWithCount */
    private $listAndCountWithoutIncludingSubCategories;

    /**
     * @param CategoryRepositoryInterface           $categoryRepository
     * @param UserContext                           $userContext
     * @param Query\ListChildrenCategoriesWithCount $listAndCountIncludingSubCategories
     * @param Query\ListChildrenCategoriesWithCount $listAndCountWithoutIncludingSubCategories
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        Query\ListChildrenCategoriesWithCount $listAndCountIncludingSubCategories,
        Query\ListChildrenCategoriesWithCount $listAndCountWithoutIncludingSubCategories
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->userContext = $userContext;
        $this->listAndCountIncludingSubCategories = $listAndCountIncludingSubCategories;
        $this->listAndCountWithoutIncludingSubCategories = $listAndCountWithoutIncludingSubCategories;
    }

    /**
     * @param ListChildrenCategoriesWithCount $parameters
     *
     * @return ReadModel\ChildCategory[]
     */
    public function list(ListChildrenCategoriesWithCount $parameters): array
    {
        $categoryToExpand = -1 !== $parameters->childrenCategoryIdToExpand() ?
            $this->categoryRepository->find($parameters->childrenCategoryIdToExpand()) : null;

        if (null === $categoryToExpand) {
            $categoryToExpand = $this->userContext->getUserProductCategoryTree();
        }

        $categoryToFilterWith = -1 !== $parameters->categoryIdToFilterWith() ?
            $this->categoryRepository->find($parameters->categoryIdToFilterWith()) : null;

        if (null !== $categoryToFilterWith
            && !$this->categoryRepository->isAncestor($categoryToExpand, $categoryToFilterWith)) {
            $categoryToFilterWith = null;
        }

        $categoryIdToFilterWith = null !== $categoryToFilterWith ? $categoryToFilterWith->getId() : null;

        $categories = $parameters->countIncludingSubCategories() ?
            $this->listAndCountIncludingSubCategories->list(
                $parameters->translationLocale(),
                $parameters->user(),
                $categoryToExpand->getId(),
                $categoryIdToFilterWith
            ) :
            $this->listAndCountWithoutIncludingSubCategories->list(
                $parameters->translationLocale(),
                $parameters->user(),
                $categoryToExpand->getId(),
                $categoryIdToFilterWith
            );

        return $categories;
    }
}
