<?php

namespace App\Command\Tracker;

use App\Command\Traits\BreakableTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\MemorableTrait;
use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryTemp;
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

#[AsCommand(name: 'app:tracker:update-wrong-speeding')]
class UpdateWrongSpeedingCommand extends Command
{
    use BreakableTrait;
    use RedisLockTrait;
    use MemorableTrait;
    use DevicebleTrait;
    use ProcessableTrait;

    private $em;
    private $logger;
    private $params;

    protected function configure(): void
    {
        $this->setDescription('Update wrong speeding');
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
        ]);
        $this->updateConfigWithDeviceOptions();
    }

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(EntityManager $em, LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->em = $em;
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
        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $lock = $this->getLock($this->getName());
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $deviceIds = $this->getDeviceIdsByInput($input)
                ?: $this->em->getRepository(Device::class)->getDeviceIds();
            $progressBar = new ProgressBar($output, count($deviceIds));
            $progressBar->start();

            foreach ($deviceIds as $deviceId) {
                $this->em->getConnection()->beginTransaction();
                $thtCount = $this->em->getRepository(TrackerHistoryTemp::class)
                    ->updateTrackerHistoriesTempAsNotCalculatedForSpeeding(
                        $deviceId,
                        $startedAt,
                        $finishedAt
                    );

                $thRecordsQuery = $this->em->getRepository(TrackerHistory::class)
                    ->getTrackerRecordsByDeviceIdAndDatesQuery(
                        $deviceId,
                        $startedAt,
                        $finishedAt
                    );

                /** @var TrackerHistory $trackerHistory */
                foreach ($thRecordsQuery->iterate() as $row) {
                    $trackerHistory = array_shift($row);
                    $thtRecord = $this->em->getRepository(TrackerHistoryTemp::class)
                        ->findOneBy(['trackerHistory' => $trackerHistory]);

                    if ($thtRecord) {
                        $thtRecord->setIsCalculatedSpeeding(false);
                        continue;
                    } else {
                        $thtModel = new TrackerHistoryTemp();
                        $thtModel->fromTrackerHistory($trackerHistory);
                        $thtModel->setIsAllCalculated();
                        $thtModel->setIsCalculatedSpeeding(false);
                        $this->em->persist($thtModel);
                    }
                }
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
            $this->em->clear();
            $progressBar->advance();

            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Wrong speeding successfully updated!');
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
