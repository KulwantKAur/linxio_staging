<?php

namespace App\Migrations;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFixturesAwareMigration extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    private $fixtures;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function addFixture(FixtureInterface $fixture)
    {
        if(null === $this->fixtures) {
            $this->fixtures = new ContainerAwareLoader($this->getContainer());
        }

        $this->fixtures->addFixture($fixture);

        return $this;
    }

    protected function executeFixtures($em = null, $append = true, $purgeMode = ORMPurger::PURGE_MODE_DELETE)
    {
        $em = $this->getContainer()->get('doctrine')->getManager($em);
        $purger = new ORMPurger($em);
        $purger->setPurgeMode($purgeMode);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($this->fixtures->getFixtures(), $append);
        $this->fixtures = null;

        return $this;
    }
}