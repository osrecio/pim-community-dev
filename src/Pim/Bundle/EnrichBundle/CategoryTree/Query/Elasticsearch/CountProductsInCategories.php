<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\CategoryTree\Query\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Pim\Component\Enrich\CategoryTree\Query\CountProductsInCategories as BaseCountProductInCategories;
use Pim\Component\Enrich\CategoryTree\ReadModel\CategoryWithCount;

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
        $body = [];
        foreach ($categories as $category) {
            $categoryCodes = $category->getChildrenCategoryCodes();
            $categoryCodes[] = $category->getCode();
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
        $categoriesWithCount = [];
        $rows = $this->client->msearch($this->indexType, $body);

        foreach ($categories as $index => $category) {
            $categoriesWithCount[$category->getCode()] = new CategoryWithCount(
                $category->getCode(),
                $rows['responses'][$index]['hits']['total'] ?? 0
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
                                'categories' => [$category->getCode()]
                            ]
                        ]
                    ]
                ]
            ];
        }
        $rows = $this->client->msearch($this->indexType, $body);

        return $rows;
    }
}
