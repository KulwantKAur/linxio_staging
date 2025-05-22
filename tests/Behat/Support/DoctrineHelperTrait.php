<?php

namespace App\Tests\Behat\Support;

use App\Kernel;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait DoctrineHelperTrait
{
    protected static $needToReload = false;

    /**
     * @return ContainerInterface
     */
    abstract public function getContainer();

    protected function rememberToReloadEntities()
    {
        self::$needToReload = true;
    }

    /**
     * @AfterStep
     */
    protected function reloadChanges()
    {
        if (!self::$needToReload) {
            return;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $im = $em->getUnitOfWork()->getIdentityMap();
        foreach ($im as $entities) {
            foreach ($entities as $entity) {
                $em->refresh($entity);
            }
        }
    }

    /**
     * @TODO: closed on DQL operation
     * @AfterStep
     */
    public function flushChanges()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if ($em->isOpen()) {
            $em->flush();
        }
    }

    /**
     * @BeforeSuite
     */
    public static function prepareFixturesAndElastica()
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $tool = new SchemaTool($em);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }

        $input = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--purge-with-truncate' => true,
            '--no-interaction' => true
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();
    }

    /**
     * Begin a database transaction.
     *
     * @BeforeScenario
     */
    public function beginTransaction()
    {
        foreach ($this->getContainer()->get('doctrine')->getConnections() as $connection) {
            $connection->beginTransaction();
        }
    }

    /**
     *
     * Roll it back after the scenario.
     *
     * @AfterScenario
     */
    public function rollback()
    {
        foreach ($this->getContainer()->get('doctrine')->getConnections() as $connection) {
            $connection->rollback();
            $connection->exec("
            DO $$
            DECLARE
            table_name TEXT;
            table_seq_name TEXT;
            BEGIN
             FOR table_name IN (SELECT tb.table_name FROM information_schema.tables AS tb INNER JOIN information_schema.columns AS cols ON tb.table_name = cols.table_name WHERE tb.table_type='BASE TABLE' AND tb.table_schema='public' AND cols.column_name='id') LOOP
                     EXECUTE 'SELECT pg_catalog.pg_get_serial_sequence(''' || table_name || ''',''id'')' into table_seq_name;
                     IF table_seq_name IS NOT NULL THEN
                        EXECUTE 'ALTER SEQUENCE ' || table_seq_name || ' minvalue 0';
                        EXECUTE 'SELECT setval('''|| table_seq_name || ''', COALESCE((SELECT MAX(id) FROM '|| table_name ||'), 0));';
                     END IF;
              END LOOP;
            END $$;
            ");
        }
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->clear();
    }
}
