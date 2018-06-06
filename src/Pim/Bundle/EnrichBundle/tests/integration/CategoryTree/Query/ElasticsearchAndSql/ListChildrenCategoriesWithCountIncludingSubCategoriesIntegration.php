<?php

namespace Pim\Bundle\EnrichBundle\tests\integration\CategoryTree\Query\ElasticsearchAndSql;

use Akeneo\Test\Integration\TestCase;

class ListChildrenCategoriesWithCountIncludingSubCategoriesIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
    }

    public function testSortDescendant()
    {
        var_dump('ooo');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
