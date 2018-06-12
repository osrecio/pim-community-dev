<?php

namespace spec\Pim\Component\Enrich\CategoryTree\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\CategoryTree\ReadModel\ChildCategory;

class ChildCategorySpec extends ObjectBehavior
{
    function it_normalize_a_list_of_children_categories()
    {
        $categories = [
            new ChildCategory(1, 'child_1', 'Child 1', false, false, 2, [
                new ChildCategory(2, 'child_2', 'Child 2', true, true, 1, []),
            ]),
            new ChildCategory(3, 'child_3', 'Child 3', false, false, 3, []),
        ];

        $this->normalizeList($categories)->shouldReturn([
            [
                'attr' => [
                    'id' => 'node_1',
                    'data-code' => 'child_1',
                ],
                'data' => 'Child 1 (2)',
                'state' => 'open',
                'children' => [[
                    'attr' => [
                        'id' => 'node_2',
                        'data-code' => 'child_2',
                    ],
                    'data' => 'Child 2 (1)',
                    'state' => 'leaf toselect jstree-checked',
                    'children' => [],
                ]],
            ],
            [
                'attr' => [
                    'id' => 'node_3',
                    'data-code' => 'child_3',
                ],
                'data' => 'Child 3 (3)',
                'state' => 'closed',
                'children' => [],
            ],
        ]);
    }
}
