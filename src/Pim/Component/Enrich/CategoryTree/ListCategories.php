<?php

declare(strict_types=1);

namespace Pim\Component\Enrich\CategoryTree;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\CategoryTree\Query;
use Pim\Component\Enrich\CategoryTree\ReadModel;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListCategories
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
     * @param ListRootCategoriesParameters $parameters
     *
     * @return ReadModel\RootCategory[]
     */
    public function listRootCategories(ListRootCategoriesParameters $parameters): array
    {
        $selectedTree = -1 !== $parameters->categoryId() ? $this->categoryRepository->find($parameters->categoryId()) : null;
        if (null === $selectedTree) {
            $selectedTree = $this->userContext->getUserProductCategoryTree();
        }

        $translationLocaleCode = $this->userContext->getCurrentLocaleCode();
        $user = $this->userContext->getUser();

        $categories = $this->getChildrenCategories->fetchTreesWithChildrenCategories($translationLocaleCode, $user);

        if ($parameters->countProductsInCategories()) {
            $categoriesWithCount = $parameters->countByIncludingSubCategories() ?
                $this->countProductInCategories->countByIncludingSubCategories($categories) :
                $this->countProductInCategories->countWithoutIncludingSubCategories($categories);
        }

        $rootCategories = [];
        foreach ($categories as $category) {
            $categoryWithCount = $categoriesWithCount[$category->getCode()] ?? null;
            $numberProductsInCategories = null !== $categoryWithCount ? $categoryWithCount->getCount() : -1;

            $rootCategories[] = new ReadModel\RootCategory(
                $category->getId(),
                $category->getCode(),
                $category->getLabel(),
                $numberProductsInCategories,
                $category->getId() === $selectedTree->getId()
            );
        }

        return $rootCategories;
    }
}
