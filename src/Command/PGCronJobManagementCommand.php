<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\RedisLockTrait;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'db:pg-cron-job-management')]
class PGCronJobManagementCommand extends Command
{
    use RedisLockTrait;
    use CommandLoggerTrait;

    private function removeOldJobRunDetails()
    {
        $sql = 'DELETE FROM cron.job_run_details WHERE start_time < NOW() - INTERVAL \'7\' DAY';
        $result = $this->em->getConnection()->executeQuery($sql);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Manage all pg_cron jobs');
    }

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(
        private EntityManager $em,
        private LoggerInterface $logger,
        private ParameterBagInterface $params
    ) {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->getLock($this->getName());

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $progressBar = new ProgressBar($output, 1);
        $progressBar->start();

        try {
            // @todo remove below only for test vv
//            $sqlTest = 'UPDATE cron.job_run_details SET status = \'failed\' WHERE status = \'failed_sent\'';
//            $result = $this->em->getConnection()->executeQuery($sqlTest);
            // @todo remove above only for test ^^

            $sql3 = 'SELECT j.jobname, jrd.jobid, jrd.runid, jrd.return_message FROM cron.job_run_details jrd LEFT JOIN cron.job j ON jrd.jobid = j.jobid WHERE jrd.status = \'failed\'';
            $failedJobs = $this->em->getConnection()->executeQuery($sql3)->fetchAllAssociative();

            if ($failedJobs) {
                $this->logger->critical(json_encode($failedJobs));
                $sql4 = 'UPDATE cron.job_run_details SET status = \'failed_sent\' WHERE status = \'failed\'';
                $result = $this->em->getConnection()->executeQuery($sql4);
            }

            $this->removeOldJobRunDetails();
        } catch (\Exception $exception) {
            $this->logException($exception);
            $output->writeln(
                PHP_EOL . sprintf(
                    'Error with message: %s',
                    $exception->getMessage()
                )
            );
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'All pg_cron jobs successfully managed!');
        $this->release();

        return 0;
    }
}