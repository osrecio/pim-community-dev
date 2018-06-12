<?php

namespace spec\Pim\Component\Enrich\CategoryTree\ReadModel;

use PhpSpec\ObjectBehavior;

class RootCategorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(1, 'code', 'label', 10, true);
    }

    function it_has_an_id()
    {
        $this->id()->shouldReturn(1);
    }

    function it_has_a_code()
    {
        $this->code()->shouldReturn('code');
    }

    function it_has_a_label()
    {
        $this->label()->shouldReturn('label');
    }

    function it_has_the_number_of_product_in_the_category()
    {
        $this->numberProductsInCategory()->shouldReturn(10);
    }

    function it_is_selected()
    {
        $this->selected()->shouldReturn(true);
    }
}
