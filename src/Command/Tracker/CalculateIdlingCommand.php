<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\EntityManager\SlaveEntityManager;
use App\Service\Idling\IdlingService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(name: 'app:tracker:calculate-idling')]
class CalculateIdlingCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use CommandLoggerTrait;

    private $em;
    private $idlingService;
    private $stopwatch;
    private $params;
    private $slaveEntityManager;
    private LoggerInterface $logger;

    /**
     * {@inheritDoc}
     */
    private function getAllItems(): array
    {
        return $this->slaveEntityManager->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Calculate idling from new records tracker_history_temp');
        $this->updateConfigWithProcessOptions();
    }

    /**
     * @param EntityManager $em
     * @param IdlingService $idlingService
     * @param Stopwatch $stopwatch
     * @param ParameterBagInterface $params
     */
    public function __construct(
        EntityManager $em,
        IdlingService $idlingService,
        Stopwatch $stopwatch,
        ParameterBagInterface $params,
        SlaveEntityManager $slaveEntityManager,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->stopwatch = $stopwatch;
        $this->idlingService = $idlingService;
        $this->params = $params;
        $this->slaveEntityManager = $slaveEntityManager;
        $this->logger = $logger;

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
        try {
//            $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
            $lock = $this->getLock($this->getProcessName($input));

            if (!$lock->acquire()) {
                $output->writeln('The command is already running in another process.');

                return 0;
            }

            $notCalculatedDeviceIds = $this->getSlicedItemsByProcess(
                $this->slaveEntityManager->getRepository(TrackerHistoryTemp::class)->getNotCalculatedIdlingDeviceIds(),
                $input,
                $output
            );
            $progressBar = new ProgressBar($output, count($notCalculatedDeviceIds));
            $progressBar->start();

            foreach ($notCalculatedDeviceIds as $deviceId) {
                $output->writeln('Device ID: ' . $deviceId);
                $notCalculatedTrackerHistoriesMaxAndMin = $this->slaveEntityManager->getRepository(TrackerHistoryTemp::class)
                    ->getNotCalculatedIdlingTrackerMaxAndMinRecords($deviceId);

                try {
                    $this->idlingService->calculateIdling(
                        $deviceId,
                        $notCalculatedTrackerHistoriesMaxAndMin['min_ts'],
                        $notCalculatedTrackerHistoriesMaxAndMin['max_ts']
                    );

                    $this->idlingService->excessingIdlingEvent(
                        $deviceId,
                        $notCalculatedTrackerHistoriesMaxAndMin['min_ts'],
                        $notCalculatedTrackerHistoriesMaxAndMin['max_ts']
                    );
                } catch (\Exception $exception) {
                    $message = sprintf(
                        'Error with device: %s with message: %s',
                        $deviceId,
                        $exception->getMessage()
                    );
                    $this->logException($exception, $message);
                    $output->writeln(PHP_EOL . $message);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Idling successfully calculated!');
            $this->release();

            return 0;
        } catch (\Throwable $e) {
            $this->logException($e);
            $output->writeln(PHP_EOL . $e->getMessage());

            return 0;
        }
    }
}