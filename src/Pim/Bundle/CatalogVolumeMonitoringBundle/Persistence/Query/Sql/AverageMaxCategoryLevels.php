<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AverageMaxCategoryLevels implements AverageMaxQuery
{
    private const VOLUME_NAME = 'average_max_category_levels';

    /** @var Connection */
    private $connection;

    /** @var int */
    private $limit;

    /**
     * @param Connection $connection
     * @param int $limit
     */
    public function __construct(Connection $connection, int $limit)
    {
        $this->connection = $connection;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
        SELECT MAX(depth) as max,
              CEIL(AVG(depth)) as average
            FROM (SELECT
                    (
                      SELECT COUNT(parent_id)
                      FROM pim_catalog_category
                      WHERE lft < node.lft
                            AND rgt > node.rgt
                            AND node.lft <> 0
                    )
                      AS depth
                  FROM `pim_catalog_category` AS node
                  WHERE lft <> 0
                  ORDER BY lft
            )as max_depth;
SQL;
        $result = $this->connection->query($sql)->fetch();

        $volume = new AverageMaxVolumes((int) $result['max'], (int) $result['average'], $this->limit, self::VOLUME_NAME);

        return $volume;
    }
}
