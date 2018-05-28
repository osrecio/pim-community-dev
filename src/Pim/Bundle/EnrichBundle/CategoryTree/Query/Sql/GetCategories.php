<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\CategoryTree\Query\Sql;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\DBAL\Connection;
use Pim\Component\Enrich\CategoryTree\Query\GetCategories as BaseGetCategories;
use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithChildren;
use Pim\Component\User\Model\User;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategories implements BaseGetCategories
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchTreesWithChildrenCategories(LocaleInterface $translationLocale, User $user): array
    {
        $sql = <<<SQL
            SELECT c.root_id, c.root_code, GROUP_CONCAT(c.child_code) as children, COALESCE(ct.label, c.root_code) as label FROM (
                SELECT root.id as root_id, root.code as root_code, child.code as child_code
                FROM pim_catalog_category root
                LEFT JOIN pim_catalog_category child on child.lft BETWEEN root.lft AND root.rgt AND child.root = root.root	
                WHERE root.parent_id IS NULL
            ) as c
            LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = c.root_id AND ct.locale = :locale
            GROUP BY c.root_id
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocale->getCode()
            ]
        )->fetchAll();

        $categoryWithChildren = [];
        foreach ($rows as $row) {
            $childrenCategories = explode(',', $row['children']);
            $categoryWithChildren[] = CategoryWithChildren::withoutCount(
                (int) $row['root_id'],
                $row['root_code'],
                $row['label'],
                $childrenCategories,
                []
            );
        }

        return $categoryWithChildren;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchChildrenCategories(
        LocaleInterface $translationLocale,
        User $user,
        CategoryInterface $categoryToExpand,
        ?CategoryInterface $categoryToFilterWith
    ): array {
        $categoriesInPath = null !== $categoryToFilterWith ?
            $this->fetchCategoriesBetween($categoryToExpand, $categoryToFilterWith) : [$categoryToExpand->getCode()];


        return $this->fetchChildrenCategoriesFrom($categoriesInPath, $translationLocale);
    }

    /**
     * Returns all category codes between the category to expand (parent) and the category to filter with (subchild).
     *
     * @param CategoryInterface $categoryToExpand
     * @param CategoryInterface $categoryToFilterWith
     *
     * @return string[]
     */
    private function fetchCategoriesBetween(CategoryInterface $categoryToExpand, CategoryInterface $categoryToFilterWith)
    {
        $sql = <<<SQL
            SELECT category_path.code
            FROM pim_catalog_category parent
            JOIN pim_catalog_category category_path on category_path.lft BETWEEN parent.lft AND parent.rgt AND parent.root = category_path.root
            JOIN pim_catalog_category subchild on category_path.lft <= subchild.lft AND category_path.rgt >= subchild.lft AND parent.root = subchild.root
            WHERE parent.code = :category_to_expand and subchild.code = :category_to_filter_with
            ORDER BY category_path.lft;  
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'category_to_expand' => $categoryToExpand->getCode(),
                'category_to_filter_with' => $categoryToFilterWith->getCode(),
            ]
        )->fetchAll();

        $codes = array_map(function ($row) {
            return $row['code'];
        }, $rows);

        return $codes;
    }

    /**
     * @param string[]        $categoriesInPath
     * @param LocaleInterface $translationLocale
     *
     * @return CategoryWithChildren[]
     */
    private function fetchChildrenCategoriesFrom(array $categoriesInPath, LocaleInterface $translationLocale): array
    {
        $parentCategoryCode = array_shift($categoriesInPath);
        $subchildCategoryCode = $categoriesInPath[0] ?? null;

        $sql = <<<SQL
            SELECT c.child_id, c.child_code, GROUP_CONCAT(c.subchild_code) as children, COALESCE(ct.label, c.child_code) as label
            FROM (
                SELECT child.id as child_id, child.code as child_code,  subchild.code as subchild_code
                FROM pim_catalog_category parent
                JOIN pim_catalog_category child on child.parent_id = parent.id
                JOIN pim_catalog_category subchild on subchild.lft BETWEEN child.lft AND child.rgt AND subchild.root = child.root
                WHERE parent.code = :parent_category
            ) as c
            LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = c.child_code AND ct.locale = :locale
            GROUP BY c.child_id
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'parent_category' => $parentCategoryCode,
                'locale' => $translationLocale->getCode()
            ]
        )->fetchAll();

        $categoryWithChildren = [];
        foreach ($rows as $row) {
            $childrenCategoryCodes = explode(',', $row['children']);

            $childrenCategoriesToExpand = null !== $subchildCategoryCode && $subchildCategoryCode === $row['child_code'] ?
                    $this->fetchChildrenCategoriesFrom($categoriesInPath, $translationLocale): [];

            $categoryWithChildren[] = CategoryWithChildren::withoutCount(
                (int) $row['child_id'],
                $row['child_code'],
                $row['label'],
                $childrenCategoryCodes,
                $childrenCategoriesToExpand
            );
        }

        return $categoryWithChildren;
    }
}
