<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\CategoryTree\Query\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Pim\Component\Enrich\CategoryTree\Query\CountProductsInCategories as BaseCountProductInCategories;
use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithChildren;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountProductsInCategories implements BaseCountProductInCategories
{
    /** @var Client */
    private $client;

    /** @var string */
    private $indexType;

    /**
     * @param Client $client
     * @param string $indexType
     */
    public function __construct(Client $client, string $indexType)
    {
        $this->client = $client;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public function countByIncludingSubCategories(array $categories): array
    {
        if (empty($categories)) {
            return [];
        }

        $body = [];
        foreach ($categories as $category) {
            $categoryCodes = $category->childrenCategoryCodes();
            $categoryCodes[] = $category->code();
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

        $categoriesWithCount = [];
        foreach ($categories as $index => $category) {
            $childrenCategoriesWithCount = !empty($category->childrenCategoriesToExpand()) ?
                $this->countByIncludingSubCategories($category->childrenCategoriesToExpand()) : [];

            $categoriesWithCount[] = CategoryWithChildren::fromCategoryWithCount(
                $category,
                $childrenCategoriesWithCount,
                $rows['responses'][$index]['hits']['total'] ?? -1
            );
        }

        return $categoriesWithCount;
    }

    /**
     * {@inheritdoc}
     */
    public function countWithoutIncludingSubCategories(array $categories): array
    {
        $body = [];
        foreach ($categories as $category) {
            $body[] = [];
            $body[] = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'terms' => [
                                'categories' => [$category->code()]
                            ]
                        ]
                    ]
                ]
            ];
        }
        $rows = $this->client->msearch($this->indexType, $body);

        $categoriesWithCount = [];
        foreach ($categories as $index => $category) {
            $childrenCategoriesWithCount = !empty($category->childrenCategoriesToExpand()) ?
                $this->countWithoutIncludingSubCategories($category->childrenCategoriesToExpand()) : [];

            $categoriesWithCount[] = CategoryWithChildren::fromCategoryWithCount(
                $category,
                $childrenCategoriesWithCount,
                $rows['responses'][$index]['hits']['total'] ?? -1
            );
        }

        return $categoriesWithCount;
    }
}
