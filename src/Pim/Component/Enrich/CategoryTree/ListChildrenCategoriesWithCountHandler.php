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

    /** @var Query\GetCategories */
    private $getChildrenCategories;

    /** @var Query\CountProductsInCategories */
    private $countProductInCategories;

    /**
     * @param CategoryRepositoryInterface     $categoryRepository
     * @param UserContext                     $userContext
     * @param Query\GetCategories             $getChildrenCategories
     * @param Query\CountProductsInCategories $countProductInCategories
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        UserContext $userContext,
        Query\GetCategories $getChildrenCategories,
        Query\CountProductsInCategories $countProductInCategories
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->userContext = $userContext;
        $this->getChildrenCategories = $getChildrenCategories;
        $this->countProductInCategories = $countProductInCategories;
    }

    /**
     * @param ListChildrenCategoriesWithCount $parameters
     *
     * @return ReadModel\CategoryWithChildren[]
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

        $translationLocale = $this->userContext->getCurrentLocale();
        $user = $this->userContext->getUser();

        $categories = $this->getChildrenCategories->fetchChildrenCategories(
            $translationLocale,
            $user,
            $categoryToExpand,
            $categoryToFilterWith
        );

        if ($parameters->countProductsInCategories()) {
            $categories = $parameters->countByIncludingSubCategories() ?
                $this->countProductInCategories->countByIncludingSubCategories($categories) :
                $this->countProductInCategories->countWithoutIncludingSubCategories($categories);
        }

        return $categories;
    }
}
