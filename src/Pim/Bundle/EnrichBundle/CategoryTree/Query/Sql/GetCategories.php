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
    public function fetchTreesWithChildrenCategories(LocaleInterface $locale, User $user): array
    {
        $sql = <<<SQL
            SELECT c.root_id, c.root_code, GROUP_CONCAT(c.child_code) as children, COALESCE(ct.label, c.root_code) as label FROM (
                SELECT root.id as root_id, root.code as root_code, child.code as child_code
                FROM pim_catalog_category root
                LEFT JOIN pim_catalog_category child on child.lft BETWEEN root.lft AND root.rgt AND child.root = root.root	
                WHERE root.parent_id IS NULL
            ) as c
            LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = c.root_id AND ct.locale = :locale
            GROUP BY c.root_id, c.root_code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $locale->getCode()
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
        LocaleInterface $translationLocaleCode,
        User $user,
        CategoryInterface $categoryToExpand,
        CategoryInterface $childCategoryToExpand
    ): array {
        $sql = <<<SQL
            SELECT c.root_id, c.root_code, GROUP_CONCAT(c.child_code) as children, COALESCE(ct.label, c.root_code) as label FROM (
                SELECT root.id as root_id, root.code as root_code, child.code as child_code
                FROM pim_catalog_category root
                LEFT JOIN pim_catalog_category child on child.lft BETWEEN root.lft AND root.rgt AND child.root = root.root	
                WHERE root.parent_id IS NULL
            ) as c
            LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = c.root_id AND ct.locale = :locale
            GROUP BY c.root_id, c.root_code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode
            ]
        )->fetchAll();
    }
}
