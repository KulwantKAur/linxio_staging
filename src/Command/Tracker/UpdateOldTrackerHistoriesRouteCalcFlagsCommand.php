<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\RedisLockTrait;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:update-old-tracker-histories-route-calc-flags')]
class UpdateOldTrackerHistoriesRouteCalcFlagsCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;

    private $em;
    private $params;

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Update old tracker histories route calc flags');
        $this->getDefinition()->addOptions([
            new InputOption('date', null, InputOption::VALUE_OPTIONAL, 'Until date, YYYY-MM-DD HH:MM:SS',
                (new Carbon())->subMonth()->toDateTimeString()),
            new InputOption('limit', null, InputOption::VALUE_OPTIONAL, 'Records limit', 10000),
            new InputOption('loops', null, InputOption::VALUE_OPTIONAL, 'Iterations loops', 10),
        ]);
    }

    /**
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
        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getName());
        $recordsLimit = intval($input->getOption('limit') ?: 10000);
        $loops = intval($input->getOption('loops') ?: 10);
        $date = $input->getOption('date') ?: (new Carbon())->subMonth()->toDateTimeString();

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $progressBar = new ProgressBar($output, 1);
        $progressBar->start();

        try {
            $sql = 'SELECT update_old_tracker_histories_route_calc_flags(?, ?, ?)';
            $result = $this->em->getConnection()
                ->executeQuery($sql, [$date, $recordsLimit, $loops])
                ->fetchAllAssociative();
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Tracker histories route calc flags successfully updated!');
        $this->release();

        return 0;
    }
}