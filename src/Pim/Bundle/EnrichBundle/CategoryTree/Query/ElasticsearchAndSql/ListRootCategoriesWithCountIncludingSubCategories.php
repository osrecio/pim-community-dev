<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\CategoryTree\Query\ElasticsearchAndSql;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Pim\Component\Enrich\CategoryTree\Query\ListRootCategoriesWithCount;
use Pim\Component\Enrich\CategoryTree\ReadModel\RootCategory;
use Pim\Component\User\Model\UserInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCountIncludingSubCategories implements ListRootCategoriesWithCount
{
    /** @var Connection */
    private $connection;

    /** @var Client */
    private $client;

    /** @var string */
    private $indexType;

    /**
     * @param Connection $connection
     * @param Client     $client
     * @param string     $indexType
     */
    public function __construct(Connection $connection, Client $client, string $indexType)
    {
        $this->connection = $connection;
        $this->client = $client;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function list(LocaleInterface $translationLocale, UserInterface $user, int $rootCategoryIdToExpand): array
    {
        $categoriesWithoutCount = $this->getRootCategories($translationLocale);
        $rootCategories = $this->countProductInCategories($categoriesWithoutCount, $rootCategoryIdToExpand);

        return $rootCategories;
    }

    /**
     * @param LocaleInterface $translationLocale
     *
     * @return array
     * [
     *     [
     *         'root_id' => 1,
     *         'root_code' => 'code',
     *         'children_codes = ['child_1', 'child_2'],
     *         'label' => 'label'
     *     ]
     * ]
     */
    private function getRootCategories(LocaleInterface $translationLocale): array
    {
        // TODO: use JSON_ARRAYAGG instead of GROUP_CONCAT (Mysql 5.7.22), or modify size max of GROUP CONCAT
        $sql = <<<SQL
            SELECT
               	root.id as root_id,
   	            root.code as root_code, 
                child.children_codes,
                COALESCE(ct.label, root.code) as label
            FROM pim_catalog_category AS root
            LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = root.id AND ct.locale = :locale
            LEFT JOIN
            (
            	SELECT 
            	    child.root as root_id,
            	    GROUP_CONCAT(child.code) as children_codes
            	FROM pim_catalog_category child
            	WHERE child.parent_id IS NOT NULL
            	GROUP BY child.root
            ) AS child ON root.id = child.root_id
            WHERE root.parent_id IS NULL
            ORDER BY label, root.code
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocale->getCode()
            ]
        )->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $childrenCategoriesCodes = null !== $row['children_codes'] ? explode(',', $row['children_codes']) : [];
            $row['children_codes'] = $childrenCategoriesCodes;

            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * @param array $categoriesWithoutCount
     * @param int   $rootCategoryIdToExpand
     *
     * @return RootCategory[]
     */
    private function countProductInCategories(array $categoriesWithoutCount, int $rootCategoryIdToExpand): array
    {
        if (empty($categoriesWithoutCount)) {
            return [];
        }

        $body = [];
        foreach ($categoriesWithoutCount as $category) {
            $categoryCodes = $category['children_codes'];
            $categoryCodes[] = $category['root_code'];
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'terms' => [
                                'categories' => $categoryCodes
                            ]
                        ]
                    ]
                ]
            ];
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $rootCategories = [];
        $index = 0;
        foreach ($categoriesWithoutCount as $category) {
            $rootCategories[] = new RootCategory(
                (int) $category['root_id'],
                $category['root_code'],
                $category['label'],
                $rows['responses'][$index]['hits']['total'] ?? -1,
                (int) $category['root_id'] === $rootCategoryIdToExpand
            );

            $index++;
        }

        return $rootCategories;
    }
}
