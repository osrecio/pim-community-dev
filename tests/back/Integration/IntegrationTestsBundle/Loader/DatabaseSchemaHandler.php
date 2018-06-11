<?php

namespace Akeneo\Test\IntegrationTestsBundle\Loader;

use Pim\Behat\Context\DBALPurger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabaseSchemaHandler
{
    /** @var KernelInterface */
    protected $kernel;

    /** @var Application */
    protected $cli;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->cli = new Application($this->kernel);
        $this->cli->setAutoExit(false);
    }

    /**
     * Truncate data in all the tables.
     *
     * @throws \RuntimeException
     */
    public function reset()
    {
        $connection = $this->kernel->getContainer()->get('database_connection');
        $schemaManager = $connection->getSchemaManager();
        $tables = $schemaManager->listTableNames();

        $purger = new DBALPurger(
            $this->kernel->getContainer()->get('database_connection'),
            $tables
        );
        $purger->purge();
    }
}
