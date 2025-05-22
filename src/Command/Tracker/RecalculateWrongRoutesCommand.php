<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\Service\Route\RouteService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:recalculate-wrong-routes')]
class RecalculateWrongRoutesCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use DevicebleTrait;

    private const BATCH_SIZE = 20;
    private $em;
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
        $this->setDescription('Recalculate wrong routes from records tracker_history_temp');
        $this->getDefinition()->addOptions([
            new InputOption('isLocationChecked', null, InputOption::VALUE_OPTIONAL, 'isLocationChecked', false),
        ]);
        $this->updateConfigWithProcessOptions();
        $this->updateConfigWithDeviceOptions();
    }

    public function __construct(
        EntityManager $em,
        RouteService $routeService,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
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
        $isLocationChecked = $input->getOption('isLocationChecked');
        $lock = $this->getLock($this->getProcessName($input));
        $startedAt = null;
        $finishedAt = null;
        $counter = 0;

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $notCalculatedDeviceIds = $this->getDeviceIdsByInput($input)
                ?: $this->getSlicedItemsByProcess(
                    $this->em->getRepository(TrackerHistoryTemp::class)->getNotCalculatedRoutesDeviceIds(false, true),
                    $input,
                    $output
                );
            $notCalculatedDeviceIds = $this->getDeviceIdsWithoutIgnored($input, $output, $notCalculatedDeviceIds);
            $progressBar = new ProgressBar($output, count($notCalculatedDeviceIds));
            $progressBar->start();

            foreach ($notCalculatedDeviceIds as $deviceId) {
                $output->writeln('Device ID: ' . $deviceId);
                $notCalculatedTrackerHistoriesMaxAndMin = $this->em->getRepository(TrackerHistoryTemp::class)
                    ->getNotCalculatedRoutesTrackerMaxAndMinRecords($deviceId, false, true);
                $minTS = $notCalculatedTrackerHistoriesMaxAndMin['min_ts'];
                $maxTS = $notCalculatedTrackerHistoriesMaxAndMin['max_ts'];

                if (!$minTS || !$maxTS) {
                    continue;
                }

                try {
                    $startedAt = min($minTS, $startedAt);
                    $finishedAt = max($maxTS, $finishedAt);
                    $this->routeService->setIsLocationChecked($isLocationChecked);
                    $this->routeService->recalculateRoutes($deviceId, $minTS, $maxTS);
                    $this->routeService->handleEventsForRecalculatedRoutesOfDevice($deviceId, $minTS, $maxTS);
                } catch (\Exception $exception) {
                    $message = sprintf(
                        'Error with device: %s with message: %s',
                        $deviceId,
                        $exception->getMessage()
                    );
                    $this->logger->error($message, [$this->getName()]);
                    $output->writeln(PHP_EOL . $message);
                }

                if (($counter % self::BATCH_SIZE) === 0) {
                    $this->em->flush(); // Executes all updates.
                    $this->em->clear(); // Detaches all objects from Doctrine!
                }
                ++$counter;

                $progressBar->advance();
            }

            $this->em->flush();
            $this->em->clear();
            $this->em->getRepository(Route::class)->removeRouteDuplicates($startedAt, $finishedAt);
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Wrong routes successfully recalculated!');
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
