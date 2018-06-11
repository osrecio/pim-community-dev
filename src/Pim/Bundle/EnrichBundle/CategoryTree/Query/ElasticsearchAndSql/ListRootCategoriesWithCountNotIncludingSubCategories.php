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
class ListRootCategoriesWithCountNotIncludingSubCategories implements ListRootCategoriesWithCount
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
     *         'label' => 'label'
     *     ]
     * ]
     */
    private function getRootCategories(LocaleInterface $translationLocale): array
    {
        $sql = <<<SQL
            SELECT 
                root.id as root_id,
                root.code as root_code,
                COALESCE(ct.label, root.code) as label
            FROM pim_catalog_category root
            LEFT JOIN pim_catalog_category_translation ct ON ct.foreign_key = root.id AND ct.locale = :locale
            WHERE root.parent_id IS NULL
            ORDER BY label, root.code
SQL;

        $categories = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocale->getCode()
            ]
        )->fetchAll();

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
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'terms' => [
                                'categories' => [$category['root_code']]
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
