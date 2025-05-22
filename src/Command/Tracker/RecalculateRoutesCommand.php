<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DBTimeoutTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\EntityManager\SlaveEntityManager;
use App\Service\Route\RouteService;
use App\Service\Tracker\Factory\TrackerFactory;
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

#[AsCommand(name: 'app:tracker:recalculate-routes')]
class RecalculateRoutesCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use ProcessableTrait;
    use DevicebleTrait;
    use DBTimeoutTrait;

    private $em;
    private $emSlave;
    private $routeService;
    private $logger;
    private $trackerFactory;
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
        $this->setDescription('Recalculate routes from records tracker_history');
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
            new InputOption('resetFlags', null, InputOption::VALUE_OPTIONAL, 'resetFlags', true),
            new InputOption('addIgnitionFix', null, InputOption::VALUE_OPTIONAL, 'addIgnitionFix', false),
            new InputOption('addZeroIgnitionFix', null, InputOption::VALUE_OPTIONAL, 'addZeroIgnitionFix', false),
            new InputOption('isLocationChecked', null, InputOption::VALUE_OPTIONAL, 'isLocationChecked', false),
        ]);
        $this->updateConfigWithProcessOptions();
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithDBTimeoutOptions();
    }

    public function __construct(
        EntityManager         $em,
        SlaveEntityManager    $emSlave,
        RouteService          $routeService,
        LoggerInterface       $logger,
        TrackerFactory        $trackerFactory,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->emSlave = $emSlave;
        $this->routeService = $routeService;
        $this->logger = $logger;
        $this->trackerFactory = $trackerFactory;
        $this->params = $params;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $this->setMemoryLimit($this->params->get('calculating_job_memory'));
//        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getProcessName($input));
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $resetFlags = $input->getOption('resetFlags');
        $addIgnitionFix = $input->getOption('addIgnitionFix');
        $addZeroIgnitionFix = $input->getOption('addZeroIgnitionFix');
        $isLocationChecked = $input->getOption('isLocationChecked');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $this->disableDBTimeoutByInput($input);
            $notCalculatedDeviceIds = $this->getDeviceIdsByInput($input)
                ?: $this->getSlicedItemsByProcess(
                    $this->em->getRepository(TrackerHistory::class)->getNotCalculatedRoutesDeviceIds(),
                    $input,
                    $output
                );
            $deviceData = $this->em->getRepository(Device::class)->getDeviceIdsWithVendorName($notCalculatedDeviceIds);
            $progressBar = new ProgressBar($output, count($deviceData));
            $progressBar->start();

            foreach ($deviceData as $deviceDatum) {
                $deviceId = $deviceDatum['id'];
                $deviceVendorName = $deviceDatum['vendorName'];
                $output->writeln('Device ID: ' . $deviceId);

                if ($resetFlags) {
                    $this->em->getRepository(TrackerHistory::class)
                        ->updateTrackerHistoryIsCalculatedRouteFlag($deviceId, $startedAt, $finishedAt);
                }
                if ($addIgnitionFix) {
                    $trackerService = $this->trackerFactory->getInstance($deviceVendorName);
                    $trackerService->updateIgnitionBySpeedFixFlag($deviceId, $startedAt, $finishedAt, $addZeroIgnitionFix);
                }

                $notCalculatedTrackerHistoriesMaxAndMin = $this->em->getRepository(TrackerHistory::class)
                    ->getNotCalculatedRoutesTrackerMaxAndMinRecords($deviceId, $startedAt, $finishedAt);
                $minTS = $notCalculatedTrackerHistoriesMaxAndMin['min_ts'];
                $maxTS = $notCalculatedTrackerHistoriesMaxAndMin['max_ts'];

                if (!$minTS || !$maxTS) {
                    continue;
                }

                try {
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

                $progressBar->advance();
            }

            $this->enableDBTimeoutByInput($input);
            $this->em->getRepository(Route::class)->removeRouteDuplicates($startedAt, $finishedAt);
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Routes successfully recalculated!');
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
