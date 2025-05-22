<?php

namespace App\Command\Tracker;

use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\TeamTrait;
use App\Entity\Device;
use App\Entity\Tracker\TrackerPayload;
use App\Entity\Tracker\TrackerPayloadTemp;
use App\Service\Tracker\Factory\TrackerFactory;
use App\Service\Tracker\TrackerService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tracker:create-th-from-payload')]
class CreateTrackerHistoryFromPayloadCommand extends Command
{
    use DevicebleTrait;
    use TeamTrait;

    private const BATCH_SIZE = 50;

    protected function configure(): void
    {
        $this->setDescription('Create tracker histories from payload data');
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
            new InputOption('startDeviceId', null, InputOption::VALUE_OPTIONAL, 'startDeviceId', null),
            new InputOption('finishDeviceId', null, InputOption::VALUE_OPTIONAL, 'finishDeviceId', null),
            new InputOption('useTempTable', null, InputOption::VALUE_OPTIONAL, 'useTempTable', true),
        ]);
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithTeamOptions();
    }

    /**
     * @param EntityManager $em
     * @param TrackerFactory $trackerFactory
     * @param TrackerService|null $trackerService
     */
    public function __construct(
        private EntityManager $em,
        private TrackerFactory $trackerFactory,
        private ?TrackerService $trackerService = null
    ) {
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
        $counter = 0;
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $startDeviceId = $input->getOption('startDeviceId');
        $finishDeviceId = $input->getOption('finishDeviceId');
        $useTempTable = $input->getOption('useTempTable');
        $teamIds = $this->getTeamIdsByInput($input);
        $deviceIds = $this->getDeviceIdsByInput($input) ?: $this->em->getRepository(Device::class)
            ->getDeviceIds(startDeviceId: $startDeviceId, finishDeviceId: $finishDeviceId, teamIds: $teamIds);
        $progressBar = new ProgressBar($output, count($deviceIds));
        $progressBar->start();

        try {
            foreach ($deviceIds as $deviceId) {
                $output->writeln('Device ID: ' . $deviceId);
                $device = $this->em->getRepository(Device::class)->find($deviceId);
                $this->trackerService = $this->trackerFactory->getInstance($device->getVendorName());
                $payloadQuery = $useTempTable
                    ? $this->em->getRepository(TrackerPayloadTemp::class)
                        ->getByDeviceAndRangeQuery($device, $startedAt, $finishedAt)
                    : $this->em->getRepository(TrackerPayload::class)
                        ->getByDeviceAndRangeQuery($device, $startedAt, $finishedAt);

                /** @var TrackerPayload|TrackerPayloadTemp $payload */
                foreach ($payloadQuery->toIterable() as $payload) {
                    $output->writeln('Payload ID: ' . $payload->getId());
                    $result = $this->trackerService
                        ->runDBPayloadProcess($payload->getPayload(), $payload->getSocketId());

                    if ($useTempTable) {
                        $payload->setIsProcessed(true);
                    }

                    if (($counter % self::BATCH_SIZE) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }

                    ++$counter;
                }

                $progressBar->advance();
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln('Device ID: ' . $deviceId);
            $output->writeln($e->getTraceAsString());
        }

        $progressBar->finish();
        $this->em->flush();
        $this->em->clear();
        $output->writeln(PHP_EOL . 'Tracker histories from payload data successfully created!');

        return 0;
    }
}