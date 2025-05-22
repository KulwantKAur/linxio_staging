<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\EntityManager\SlaveEntityManager;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Speeding\SpeedingService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:calculate-speeding')]
class CalculateSpeedingCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use CommandLoggerTrait;

    private $em;
    private $speedingService;
    private $notificationDispatcher;
    private $logger;
    private $params;
    private $slaveEntityManager;

    /**
     * {@inheritDoc}
     */
    private function getAllItems(): array
    {
        return $this->slaveEntityManager->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
    }

    protected function configure(): void
    {
        $this->setDescription('Calculate speeding from new records tracker_history_temp');
        $this->updateConfigWithProcessOptions();
    }

    public function __construct(
        EntityManager $em,
        SpeedingService $speedingService,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        ParameterBagInterface $params,
        SlaveEntityManager $slaveEntityManager
    ) {
        $this->em = $em;
        $this->speedingService = $speedingService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->params = $params;
        $this->slaveEntityManager = $slaveEntityManager;

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
//        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getProcessName($input));

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $notCalculatedDeviceIds = $this->getSlicedItemsByProcess(
            $this->slaveEntityManager->getRepository(Device::class)->getDeviceIds(),
//            $this->slaveEntityManager->getRepository(TrackerHistoryTemp::class)->getNotCalculatedSpeedingDeviceIds(),
            $input,
            $output
        );
        $progressBar = new ProgressBar($output, count($notCalculatedDeviceIds));
        $progressBar->start();

        foreach ($notCalculatedDeviceIds as $deviceId) {
            $output->writeln('Device ID: ' . $deviceId);
            $notCalculatedTrackerHistoriesMaxAndMin = $this->slaveEntityManager->getRepository(TrackerHistoryTemp::class)
                ->getNotCalculatedSpeedingTrackerMaxAndMinRecords($deviceId);
            $minTS = $notCalculatedTrackerHistoriesMaxAndMin['min_ts'];
            $maxTS = $notCalculatedTrackerHistoriesMaxAndMin['max_ts'];

            if (!$minTS || !$maxTS) {
                $progressBar->advance();
                continue;
            }

            try {
                $this->speedingService->calculateSpeeding(
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
        $output->writeln(PHP_EOL . 'Speeding successfully calculated!');
        $this->release();

        return 0;
    }
}
