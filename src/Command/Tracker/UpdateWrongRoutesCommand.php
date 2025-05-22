<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryTemp;
use App\EntityManager\SlaveEntityManager;
use App\Util\ExceptionHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:update-wrong-routes')]
class UpdateWrongRoutesCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use DevicebleTrait;
    use ProcessableTrait;

    public const WRONG_ROUTES_FINISH_DAY_OFFSET = 2;
    public const WRONG_ROUTES_START_DAY_OFFSET = 30;
    private const BATCH_SIZE = 20;

    private $em;
    private $logger;
    private $params;
    private $slaveEntityManager;

    protected function configure(): void
    {
        $this->setDescription('Update routes with wrong end/start (not-crossed) points');
        $this->getDefinition()->addOptions([
            new InputOption('limit', null, InputOption::VALUE_OPTIONAL, 'Records limit', 50),
        ]);
        $this->updateConfigWithDeviceOptions();
    }

    public function __construct(
        EntityManager $em,
        LoggerInterface $logger,
        ParameterBagInterface $params,
        SlaveEntityManager $slaveEntityManager
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->params = $params;
        $this->slaveEntityManager = $slaveEntityManager;

        parent::__construct();
    }

    /**
     * @param array $routesData
     * @return array
     */
    private function ignoreDuplicatedDeviceIds(array $routesData): array
    {
        $uniqueDeviceIds = [];

        return array_filter($routesData, function ($item) use (&$uniqueDeviceIds) {
            if (in_array($item['device_id'], $uniqueDeviceIds)) {
                return false;
            }

            $uniqueDeviceIds[] = $item['device_id'];

            return true;
        });
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
        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getName());
        $limit = intval($input->getOption('limit') ?: 50);

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $deviceIds = $this->getDeviceIdsByInput($input)
                ?: $this->em->getRepository(Device::class)->getDeviceIds();
            $routesData = $this->em->getRepository(Route::class)->getRoutesWithWrongPoints($deviceIds, $limit);
            $routesData = $this->ignoreDuplicatedDeviceIds($routesData);

            $progressBar = new ProgressBar($output, count($routesData));
            $progressBar->start();

            foreach ($routesData as $routesDatum) {
                if (!$this->em->getConnection()->isTransactionActive()) {
                    $this->em->getConnection()->beginTransaction();
                }

                $counter = 0;
                $startDate = Carbon::parse($routesDatum['started_at']);
                $prevStartDate = Carbon::parse($routesDatum['route_prev_started_at']);
                $finishDate = Carbon::parse($routesDatum['finished_at']);
                $nextFinishDate = Carbon::parse($routesDatum['route_next_finished_at']);

                if ($finishDate->getTimestamp() < $startDate->getTimestamp()) {
                    if ($finishDate->getTimestamp() < $prevStartDate->getTimestamp()) {
                        $prevStartDate = $finishDate;
                    }
                    if ($startDate->getTimestamp() > $nextFinishDate->getTimestamp()) {
                        $nextFinishDate = $startDate;
                    }
                }

                $thtCount = $this->em->getRepository(TrackerHistoryTemp::class)
                    ->updateTrackerHistoriesTempAsNotCalculatedForRoutes(
                        $routesDatum['device_id'],
                        $prevStartDate->toDateTimeString(),
                        $nextFinishDate->toDateTimeString()
                    );

                $thRecordsQuery = $this->em->getRepository(TrackerHistory::class)
                    ->getTrackerRecordsByDeviceIdAndDatesQuery(
                        $routesDatum['device_id'],
                        $prevStartDate->toDateTimeString(),
                        $nextFinishDate->toDateTimeString()
                    );

                /** @var TrackerHistory $trackerHistory */
                foreach ($thRecordsQuery->iterate() as $row) {
                    if (!$this->em->getConnection()->isTransactionActive()) {
                        $this->em->getConnection()->beginTransaction();
                    }

                    try {
                        $trackerHistory = array_shift($row);
                        $thtRecord = $this->em->getRepository(TrackerHistoryTemp::class)
                            ->findOneBy(['trackerHistory' => $trackerHistory]);

                        if ($thtRecord) {
                            $thtRecord->setIsCalculated(false);
                            continue;
                        } else {
                            $thtModel = new TrackerHistoryTemp();
                            $thtModel->fromTrackerHistory($trackerHistory);
                            $thtModel->setIsAllCalculated();
                            $thtModel->setIsCalculated(false);
                            $this->em->persist($thtModel);
                        }

                        if (($counter % self::BATCH_SIZE) === 0) {
                            $this->em->flush(); // Executes all updates.
                            $this->em->getConnection()->commit();
                            $this->em->clear(); // Detaches all objects from Doctrine!
                        }
                        ++$counter;
                    } catch (\Exception $e) {
                        $this->em->getConnection()->rollBack();
                        throw $e;
                    }
                }

                $this->em->flush();
                if ($this->em->getConnection()->isTransactionActive()) {
                    $this->em->getConnection()->commit();
                }
                $this->em->clear();
                $progressBar->advance();
            }

            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Routes with wrong end/start points successfully marked for update!');
        } catch (\Exception $exception) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->getConnection()->rollback();
            }

            $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
