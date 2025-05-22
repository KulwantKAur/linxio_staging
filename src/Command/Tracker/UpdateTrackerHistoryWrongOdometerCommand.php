<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\DrivingBehavior;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Service\Tracker\Factory\TrackerFactory;
use App\Service\Tracker\Interfaces\DeviceDataInterface;
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

#[AsCommand(name: 'app:tracker:update-data-with-wrong-odometer')]
class UpdateTrackerHistoryWrongOdometerCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use DevicebleTrait;
    use ProcessableTrait;

    private const BATCH_SIZE = 20;

    private $em;
    private $trackerFactory;
    private $trackerService;
    private $logger;
    private $params;

    /**
     * @param InputInterface $input
     * @return array
     */
    private function getTeamIdsByInput(InputInterface $input): array
    {
        return $input->getOption('teamIds') ? explode(',', $input->getOption('teamIds')): [];
    }

    protected function configure(): void
    {
        $this->setDescription('Update tracker history data with wrong odometer');
        $this->getDefinition()->addOptions([
            new InputOption('tsFrom', null, InputOption::VALUE_REQUIRED, 'tsFrom', null),
            new InputOption('tsTo', null, InputOption::VALUE_REQUIRED, 'tsTo', null),
            new InputOption('createdAtFrom', null, InputOption::VALUE_OPTIONAL, 'createdAtFrom', null),
            new InputOption('createdAtTo', null, InputOption::VALUE_OPTIONAL, 'createdAtTo', null),
            new InputOption('vendor', null, InputOption::VALUE_OPTIONAL, 'vendor', null),
            new InputOption('teamIds', null, InputOption::VALUE_OPTIONAL, 'teamIds', null),
            new InputOption('startDeviceId', null, InputOption::VALUE_OPTIONAL, 'startDeviceId', null),
            new InputOption('finishDeviceId', null, InputOption::VALUE_OPTIONAL, 'finishDeviceId', null),
        ]);
        $this->updateConfigWithDeviceOptions();
    }

    /**
     * @param EntityManager $em
     * @param TrackerFactory $trackerFactory
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(
        EntityManager $em,
        TrackerFactory $trackerFactory,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->trackerFactory = $trackerFactory;
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
        $this->setMemoryLimit($this->params->get('calculating_job_memory'));
//        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
//        $lock = $this->getLock($this->getName());
        $counter = 0;
        $tsFrom = $input->getOption('tsFrom');
        $tsTo = $input->getOption('tsTo');
        $createdAtFrom = $input->getOption('createdAtFrom');
        $createdAtTo = $input->getOption('createdAtTo');
        $vendor = $input->getOption('vendor');
        $startDeviceId = $input->getOption('startDeviceId');
        $finishDeviceId = $input->getOption('finishDeviceId');
        $teamIds = $this->getTeamIdsByInput($input);
        $deviceIds = $this->getDeviceIdsByInput($input) ?: $this->em->getRepository(Device::class)->getDeviceIds();
        $deviceData = $this->em->getRepository(Device::class)
            ->getDeviceIdsWithVendorName($deviceIds, $vendor, $teamIds, $startDeviceId, $finishDeviceId);

//        if (!$lock->acquire()) {
//            $output->writeln('The command is already running in another process.');
//
//            return 0;
//        }

        try {
            $progressBar = new ProgressBar($output, count($deviceData));
            $progressBar->start();

            foreach ($deviceData as $deviceDatum) {
                $deviceId = $deviceDatum['id'];
                $output->writeln('Device ID: ' . $deviceId);
                // @todo replace $device to modelName if issue with EM
                $device = $this->em->getRepository(Device::class)->find($deviceId);
                $this->trackerService = $this->trackerFactory->getInstance($deviceDatum['vendorName'], $device);
                $tsFrom = $this->em->getRepository(Route::class)->getStartedAtByDeviceIdAndTs($deviceId, $tsFrom)
                    ?: $tsFrom;
                $tsTo = $this->em->getRepository(Route::class)->getFinishedAtByDeviceIdAndTs($deviceId, $tsTo)
                    ?: $tsTo;
                $trackerRecordsDataQuery = $this->em->getRepository(TrackerHistory::class)
                    ->getRecordsForUpdatingWrongOdometerQuery(
                        $deviceId,
                        $tsFrom,
                        $tsTo,
                        $createdAtFrom,
                        $createdAtTo
                    );

                foreach ($trackerRecordsDataQuery->toIterable() as $trackerHistoryDatum) {
                    try {
                        $thId = $trackerHistoryDatum['id'];
                        $tcpDecoder = $this->trackerService::getTcpDecoder(
                            $deviceDatum['vendorName'],
                            $trackerHistoryDatum['payload']
                        );

                        if (!$tcpDecoder) {
                            continue;
                        }

                        $rawTrackerData = $tcpDecoder->decodeData($trackerHistoryDatum['payload'], $device);

                        /** @var DeviceDataInterface $rawTrackerDatum */
                        foreach ($rawTrackerData as $rawTrackerDatum) {
                            $odometer = $rawTrackerDatum->getOdometer();

                            if ($odometer) {
                                $this->em->getRepository(TrackerHistory::class)
                                    ->updateTrackerHistoryOdometerById($thId, $odometer);
                                $this->em->getRepository(DrivingBehavior::class)
                                    ->updateOdometerByTrackerHistoryId($thId, $odometer);
                                $this->em->getRepository(Route::class)
                                    ->updateStartOdometerByTrackerHistoryId($thId, $odometer);
                                $this->em->getRepository(Route::class)
                                    ->updateFinishOdometerByTrackerHistoryId($thId, $odometer);
                                $this->em->getRepository(Route::class)
                                    ->updateDistanceByTrackerHistoryId($thId);
                            }
                        }

                        if (($counter % self::BATCH_SIZE) === 0) {
                            $this->em->flush();
                            $this->em->clear();
                            $device = $this->em->getReference(Device::class, $deviceId);
                        }

                        ++$counter;
                    } catch (\Exception $exception) {
                        $output->writeln('Error with tracker_history.id: ' . $thId . ' - ' . $exception->getMessage());
                        continue;
                    }
                }

                $progressBar->advance();
            }

            $this->em->flush();
            $this->em->clear();
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Tracker history data with wrong odometer successfully updated!');
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

//        $this->release();

        return 0;
    }
}
