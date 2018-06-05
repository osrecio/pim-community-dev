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
     * @param ListRootCategoriesWithCount $parameters
     *
     * @return ReadModel\RootCategory[]
     */
    public function list(ListRootCategoriesWithCount $parameters): array
    {
        $selectNode = -1 !== $parameters->treeToExpand() ?
            $this->categoryRepository->find($parameters->treeToExpand()) : null;

        if (null === $selectNode) {
            $selectNode = $this->userContext->getUserProductCategoryTree();
        }

        $translationLocale = $this->userContext->getCurrentLocale();
        $user = $this->userContext->getUser();

        $categoriesWithoutCount = $this->getChildrenCategories->fetchTreesWithChildrenCategories($translationLocale, $user);

        $categoriesWithCount = $parameters->countIncludingSubCategories() ?
            $this->countProductInCategories->countByIncludingSubCategories($categoriesWithoutCount) :
            $this->countProductInCategories->countWithoutIncludingSubCategories($categoriesWithoutCount);

        $rootCategories = [];
        foreach ($categoriesWithCount as $categoryWithCount) {
            $rootCategories[] = new ReadModel\RootCategory(
                $categoryWithCount->id(),
                $categoryWithCount->code(),
                $categoryWithCount->label(),
                $categoryWithCount->numberProductsInCategory(),
                $categoryWithCount->id() === $selectNode->getRoot()
            );
        }

        return $rootCategories;
    }
}
