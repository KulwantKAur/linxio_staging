<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\EntityManager\SlaveEntityManager;
use App\Service\Route\RouteService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:calculate-routes')]
class CalculateRoutesCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use CommandLoggerTrait;

    private $em;
    private $emSlave;
    private $routeService;
    private $logger;
    private $params;

    /**
     * {@inheritDoc}
     */
    private function getAllItems(): array
    {
        return $this->em->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
    }

    protected function configure(): void
    {
        $this->setDescription('Calculate routes from new records tracker_history_temp');
        $this->updateConfigWithProcessOptions();
    }

    /**
     * RecalculateRoutesCommand constructor.
     * @param EntityManager $em
     * @param SlaveEntityManager $emSlave
     * @param RouteService $routeService
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(
        EntityManager $em,
        SlaveEntityManager $emSlave,
        RouteService $routeService,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->emSlave = $emSlave;
        $this->routeService = $routeService;
        $this->logger = $logger;
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
//        $this->setMemoryLimit($this->params->get('calculating_job_memory'));
//        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getProcessName($input));

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $notCalculatedDeviceIds = $this->getSlicedItemsByProcess(
                $this->em->getRepository(TrackerHistoryTemp::class)->getNotCalculatedRoutesDeviceIds(true),
                $input,
                $output
            );

            $progressBar = new ProgressBar($output, count($notCalculatedDeviceIds));
            $progressBar->start();

            foreach ($notCalculatedDeviceIds as $deviceId) {
                try {
                    $output->writeln('Device ID: ' . $deviceId);
                    $notCalculatedTrackerHistoriesMaxAndMin = $this->em->getRepository(TrackerHistoryTemp::class)
                        ->getNotCalculatedRoutesTrackerMaxAndMinRecords($deviceId, true);
                    $minTS = $notCalculatedTrackerHistoriesMaxAndMin['min_ts'];
                    $maxTS = $notCalculatedTrackerHistoriesMaxAndMin['max_ts'];

                    if (!$minTS || !$maxTS) {
                        continue;
                    }

                    $this->routeService->calculateRoutes($deviceId, $minTS, $maxTS);
                    $this->routeService->handleEventsForCalculatedRoutesOfDevice($deviceId, $minTS, $maxTS);
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

            // @todo moved to cron.job Version20231103154234
//            $this->em->getRepository(Route::class)->removeRouteDuplicates();
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Routes successfully calculated!');
        } catch (\Exception $exception) {
            $this->logException($exception);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
