<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Service\Idling\IdlingService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/*
 * @todo add indexes to `src/AppBundle/Entity/Tracker/TrackerHistory.php` like from `src/AppBundle/Entity/Tracker/TrackerHistoryTemp.php` if use this command
 */
#[AsCommand(name: 'app:tracker:recalculate-idling')]
class RecalculateIdlingCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use DevicebleTrait;

    private $em;
    private $idlingService;
    private $stopwatch;
    private $params;

    /**
     * {@inheritDoc}
     */
    private function getAllItems(): array
    {
        return $this->em->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Recalculate idling from records tracker_history');
        $this->updateConfigWithProcessOptions();
        $this->updateConfigWithDeviceOptions();
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
            new InputOption('resetFlags', null, InputOption::VALUE_OPTIONAL, 'resetFlags', true),
        ]);
    }

    /**
     * RecalculateIdlingCommand constructor.
     * @param EntityManager $em
     * @param IdlingService $idlingService
     * @param Stopwatch $stopwatch
     * @param ParameterBagInterface $params
     */
    public function __construct(EntityManager $em, IdlingService $idlingService, Stopwatch $stopwatch, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->stopwatch = $stopwatch;
        $this->idlingService = $idlingService;
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
        $lock = $this->getLock($this->getProcessName($input));
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $resetFlags = $input->getOption('resetFlags');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $notCalculatedDeviceIds = $this->getDeviceIdsByInput($input)
            ?: $this->getSlicedItemsByProcess(
                $this->em->getRepository(TrackerHistory::class)->getNotCalculatedIdlingDeviceIds(),
                $input,
                $output
            );
        $progressBar = new ProgressBar($output, count($notCalculatedDeviceIds));
        $progressBar->start();

        foreach ($notCalculatedDeviceIds as $deviceId) {
            if ($resetFlags) {
                $this->em->getRepository(TrackerHistory::class)
                    ->updateTrackerHistoryIsCalculatedIdlingFlag($deviceId, $startedAt, $finishedAt);
            }

            $notCalculatedTrackerHistoriesMaxAndMin = $this->em->getRepository(TrackerHistory::class)
                ->getNotCalculatedIdlingTrackerMaxAndMinRecords($deviceId);

            try {
                $this->idlingService->recalculateIdling(
                    $deviceId,
                    $notCalculatedTrackerHistoriesMaxAndMin['min_ts'],
                    $notCalculatedTrackerHistoriesMaxAndMin['max_ts']
                );
                // @todo: add events handle?
            } catch (\Exception $exception) {
                $output->writeln(
                    PHP_EOL . sprintf(
                        'Error with device: %s with message: %s',
                        $deviceId,
                        $exception->getMessage()
                    )
                );
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Idling successfully recalculated!');
        $this->release();

        return 0;
    }
}