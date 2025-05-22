<?php

namespace App\Command\Tracker;

use App\Command\Traits\DBTimeoutTrait;
use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Device;
use App\Entity\Tracker\TrackerPayloadStreamax;
use App\Service\Streamax\StreamaxService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:handle-streamax-payload')]
class HandleStreamaxPayloadCommand extends Command
{
    use RedisLockTrait;
    use DevicebleTrait;
    use DBTimeoutTrait;

    private $em;

    protected function configure(): void
    {
        $this->setDescription('Handle Streamax payloads from table `tracker_payload_streamax`');
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
        ]);
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithDBTimeoutOptions();
    }

    public function __construct(
        EntityManager         $em,
        private StreamaxService       $streamaxService,
        private ParameterBagInterface $params
    ) {
        $this->em = $em;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->getLock($this->getName());
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $this->disableDBTimeoutByInput($input);
            $deviceIds = $this->getDeviceIdsByInput($input);
            $payloadsQuery = $this->em->getRepository(TrackerPayloadStreamax::class)
                ->getByRangeQuery($startedAt, $finishedAt);
            $payloadsCount = $this->em->getRepository(TrackerPayloadStreamax::class)
                ->getByRangeCount($startedAt, $finishedAt);
            $progressBar = new ProgressBar($output, $payloadsCount);
            $progressBar->start();

            /** @var TrackerPayloadStreamax $payloadEntity */
            foreach ($payloadsQuery->toIterable() as $payloadEntity) {
                $payload = $payloadEntity->getPayload();
                $output->writeln('Payload: ' . $payload);

                try {
                    $dataByDevice = $this->streamaxService->handleDataFromJob(json_decode($payload, true));

                    foreach ($dataByDevice as $imei => $datumByDevice) {
                        $device = $this->em->getRepository(Device::class)->getDeviceByImei($imei);

                        if ($device && (($deviceIds && in_array($device->getId(), $deviceIds)) || (!$deviceIds))) {
                            $output->writeln('Handle device ID: ' . $device->getId());
                            $this->streamaxService->parseFromTcpDirect($datumByDevice);
                        } else {
                            $output->writeln('Skipped device ID: ' . $device->getId());
                        }
                    }
                } catch (\Exception $exception) {
                    $message = sprintf(
                        'Error with payload: %s with message: %s',
                        $datumByDevice ?? $payload,
                        $exception->getMessage()
                    );
                    $output->writeln(PHP_EOL . $message);
                }

                $payloadEntity->setIsProcessed(true);
                $this->em->flush();
                $progressBar->advance();
            }

            $this->enableDBTimeoutByInput($input);
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Streamax payloads successfully handled!');
        } catch (\Exception $exception) {
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        $this->release();

        return 0;
    }
}
