<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Speeding\SpeedingService;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/*
 * @todo add indexes to `src/AppBundle/Entity/Tracker/TrackerHistory.php` like from `src/AppBundle/Entity/Tracker/TrackerHistoryTemp.php` if use this command
 */

#[AsCommand(name: 'app:tracker:recalculate-speeding')]
class RecalculateSpeedingCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use DevicebleTrait;

    private $em;
    private $speedingService;
    private $notificationDispatcher;
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
        $this->setDescription('Recalculate speeding from records tracker_history');
        $this->updateConfigWithProcessOptions();
        $this->updateConfigWithDeviceOptions();
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
            new InputOption('resetFlags', null, InputOption::VALUE_OPTIONAL, 'resetFlags', true),
        ]);
    }

    public function __construct(
        EntityManager $em,
        SpeedingService $speedingService,
        NotificationEventDispatcher $notificationDispatcher,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->speedingService = $speedingService;
        $this->notificationDispatcher = $notificationDispatcher;
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
                $this->em->getRepository(TrackerHistory::class)->getNotCalculatedSpeedingDeviceIds(),
                $input,
                $output
            );
        $progressBar = new ProgressBar($output, count($notCalculatedDeviceIds));
        $progressBar->start();

        foreach ($notCalculatedDeviceIds as $deviceId) {
            if ($resetFlags) {
                $this->em->getRepository(TrackerHistory::class)
                    ->updateTrackerHistoryIsCalculatedSpeedingFlag($deviceId, $startedAt, $finishedAt);
            }

            $notCalculatedTrackerHistoriesMaxAndMin = $this->em->getRepository(TrackerHistory::class)
                ->getNotCalculatedSpeedingTrackerMaxAndMinRecords($deviceId);

            try {
                $this->speedingService->recalculateSpeeding(
                    $deviceId,
                    $notCalculatedTrackerHistoriesMaxAndMin['min_ts'],
                    $notCalculatedTrackerHistoriesMaxAndMin['max_ts']
                );
            } catch (\Exception $exception) {
                $output->writeln(PHP_EOL . sprintf(
                    'Error with device: %s with message: %s',
                    $deviceId,
                    $exception->getMessage()
                ));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Speeding successfully recalculated!');
        $this->release();

        return 0;
    }
}
