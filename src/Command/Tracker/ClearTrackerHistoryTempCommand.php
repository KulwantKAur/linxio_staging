<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DBTimeoutTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Tracker\TrackerHistoryTemp;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:clear-temp-history')]
class ClearTrackerHistoryTempCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use DBTimeoutTrait;

    private $em;
    private $params;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Clear temp tracker histories');
    }

    /**
     * ClearTrackerHistoryTempCommand constructor.
     * @param EntityManager $em
     * @param ParameterBagInterface $params
     */
    public function __construct(EntityManager $em, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->params = $params;

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
        // we don't need it since we have partitions in table `tracker_history_temp_part`
        return 0;

        $lock = $this->getLock($this->getName());

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $progressBar = new ProgressBar($output, 1);
        $progressBar->start();

        try {
            $this->disableDBTimeout();
            $result = $this->em->getRepository(TrackerHistoryTemp::class)->removeOldCalculatedRecords();
            $this->enableDBTimeout();
        } catch (\Exception $exception) {
            $output->writeln(
                PHP_EOL . sprintf(
                    'Error with message: %s',
                    $exception->getMessage()
                )
            );
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Temp tracker histories successfully cleared!');
        $this->release();

        return 0;
    }
}