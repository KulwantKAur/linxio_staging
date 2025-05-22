<?php

namespace App\Command;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\EntityManager\SlaveEntityManager;
use App\Service\EngineOnTime\EngineOnTimeService;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
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

#[AsCommand(name: 'app:device:update-engine-on-time')]
class UpdateEngineOnTimeCommand extends Command
{
    use RedisLockTrait;
    use MemorableTrait;
    use DevicebleTrait;
    use ProcessableTrait;

    private const BATCH_SIZE = 20;

    private $em;
    private EngineOnTimeService $engineOnTimeService;
    private $logger;
    private $params;
    private $memoryDb;

    /**
     * {@inheritDoc}
     */
    private function getAllItems(): array
    {
        return $this->em->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
    }

    protected function configure(): void
    {
        $this->setDescription('Update engine on time for devices');
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
        ]);
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithProcessOptions();
    }

    public function __construct(
        EntityManager $em,
        EngineOnTimeService $engineOnTimeService,
        LoggerInterface $logger,
        ParameterBagInterface $params,
        MemoryDbService $memoryDb
    ) {
        $this->em = $em;
        $this->engineOnTimeService = $engineOnTimeService;
        $this->logger = $logger;
        $this->params = $params;
        $this->memoryDb = $memoryDb;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->getLock($this->getProcessName($input));
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $counter = 0;

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

//            return 0;
        }

        try {
            $deviceIds = $this->getDeviceIdsByInput($input)
                ?: $this->getSlicedItemsByProcess(
                    $this->getAllItems(),
                    $input,
                    $output
                );

            $progressBar = new ProgressBar($output, count($deviceIds));
            $progressBar->start();

            foreach ($deviceIds as $deviceId) {
                $data = [];
                $device = $this->em->getRepository(Device::class)->find($deviceId);

                if (!$device || !$device->getVehicle()) {
                    continue;
                }

                try {
                    $lastTH = null;
                    $thRecordsQuery = $this->em->getRepository(TrackerHistory::class)
                        ->getTrackerRecordsForUpdateEngineOnTimeQuery($deviceId, $startedAt, $finishedAt);

                    foreach ($thRecordsQuery->toIterable() as $thRecordData) {
                        if (!$lastTH) {
                            if ($thRecordData['engineOnTime']) {
                                $engineOnTime = $thRecordData['engineOnTime'];
                                $this->memoryDb->set(
                                    VehicleRedisModel::getEngineOnTimeKey($device->getVehicle()), $engineOnTime
                                );
                                $lastTH = $thRecordData;
                            } else {
                                continue;
                            }
                        }

                        $data['data'] = [['th' => $thRecordData]];
                        $this->engineOnTimeService->updateEngineOnTime($device, $data, true, $lastTH);
                        $lastTH = $thRecordData;

                        if (($counter % self::BATCH_SIZE) === 0) {
                            $this->em->clear();
                            $device = $this->em->getRepository(Device::class)->find($deviceId);
                        }

                        $counter++;
                    }

                    $this->em->clear();
                } catch (\Exception $exception) {
                    $message = sprintf(
                        'Error with device: %s with message: %s',
                        $deviceId,
                        $exception->getMessage()
                    );
                    $this->logger->error($message, [$this->getName()]);
                    $output->writeln(PHP_EOL . $message);
                }

                $this->em->clear();
                $progressBar->advance();
                unset($thRecordsQuery);
                unset($device);
                gc_collect_cycles();
            }

            $this->em->clear();
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Update engine on time for devices successful!');
        } catch (\Exception $exception) {
            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}