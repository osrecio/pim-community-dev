<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\CountFamilyVariants;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Prophecy\Argument;

class CountFamilyVariantsSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, -1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountFamilyVariants::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_gets_count_volume($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['count' => '4']);
        $this->fetch()->shouldBeLike(new CountVolume(4, -1, 'count_family_variants'));
    }
}
