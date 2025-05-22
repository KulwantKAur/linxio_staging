<?php

namespace App\Command\Traits;

use App\Util\Doctrine\DoctrineHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait DBTimeoutTrait
{
    private $em;

    /**
     * Call this function in configure()
     */
    protected function updateConfigWithDBTimeoutOptions()
    {
        /** @var Command $this */
        $this->getDefinition()->addOptions([
            new InputOption('useDBTimeout', null, InputOption::VALUE_OPTIONAL, 'useDBTimeout', false),
        ]);
    }

    public function disableDBTimeout(?EntityManagerInterface $em = null)
    {
        $sql = 'SET statement_timeout = 0';
        $result = $em
            ? $em->getConnection()->executeQuery($sql)
            : $this->em->getConnection()->executeQuery($sql);
    }

    public function disableDBTimeoutByInput(InputInterface $input, ?EntityManagerInterface $em = null)
    {
        $useDBTimeout = $input->getOption('useDBTimeout');

        if ($useDBTimeout) {
            $this->disableDBTimeout($em);
        }
    }

    public function enableDBTimeout(?EntityManagerInterface $em = null)
    {
        $sql = 'SET statement_timeout TO \'' . DoctrineHelper::STATEMENT_TIMEOUT . '\'';
        $result = $em
            ? $em->getConnection()->executeQuery($sql)
            : $this->em->getConnection()->executeQuery($sql);
    }

    public function enableDBTimeoutByInput(InputInterface $input, ?EntityManagerInterface $em = null)
    {
        $useDBTimeout = $input->getOption('useDBTimeout');

        if ($useDBTimeout) {
            $this->enableDBTimeout($em);
        }
    }
}