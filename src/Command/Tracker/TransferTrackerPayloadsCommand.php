<?php

namespace App\Command\Tracker;

use App\Command\Traits\DBTimeoutTrait;
use App\Entity\Device;
use App\Entity\DeviceVendor;
use App\Entity\Tracker\TrackerPayload;
use App\EntityManager\AuroraEntityManager;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tracker:transfer-payloads')]
class TransferTrackerPayloadsCommand extends Command
{
    use DBTimeoutTrait;

    private function initBEHttpClient()
    {
        $this->BEHttpClient = new Client([
            'headers' => [
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ],
            'base_uri' => $this->BEApiUrl,
        ]);
    }

    private function getUrlByDevice(Device $device): string
    {
        return match ($device->getVendorName()) {
            DeviceVendor::VENDOR_TOPFLYTECH => '/api/tracker/topflytech/tcp',
            DeviceVendor::VENDOR_TELTONIKA => '/api/tracker/teltonika/tcp',
            DeviceVendor::VENDOR_ULBOTECH => '/api/tracker/ulbotech/tcp',
            DeviceVendor::VENDOR_PIVOTEL => '/api/tracker/pivotel/tcp',
            DeviceVendor::VENDOR_TRACCAR => '/api/traccar/hook/positions',
            DeviceVendor::VENDOR_STREAMAX => '/api/streamax/inbound/direct',
            default => throw new \Exception('Unknown vendor'),
        };
    }

    private function isPayloadValid(Device $device, TrackerPayload $payload): bool
    {
        return match ($device->getVendorName()) {
            DeviceVendor::VENDOR_TRACCAR => $this->isPayloadValidTraccar($payload),
            DeviceVendor::VENDOR_STREAMAX => $this->isPayloadValidStreamax($payload),
            default => true
        };
    }

    private function preparePayload(Device $device, string $payload): array
    {
        return match ($device->getVendorName()) {
            DeviceVendor::VENDOR_TRACCAR,
            DeviceVendor::VENDOR_STREAMAX => json_decode($payload, true),
            default => ['payload' => $payload]
        };
    }

    private function isPayloadValidTraccar(TrackerPayload $payload): bool
    {
        return str_starts_with($payload->getPayload(), '{"position"');
    }

    private function isPayloadValidStreamax(TrackerPayload $payload): bool
    {
        return str_contains($payload->getPayload(), '"type":"GPS"');
    }

    protected function configure(): void
    {
        $this->setDescription('Transfer trackers payloads from another db');
        $this->getDefinition()->addOptions([
            new InputOption('startDeviceId', null, InputOption::VALUE_REQUIRED, 'startDeviceId', null),
            new InputOption('finishDeviceId', null, InputOption::VALUE_REQUIRED, 'finishDeviceId', null),
            new InputOption('startedAt', null, InputOption::VALUE_REQUIRED, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_REQUIRED, 'finishedAt', null),
            new InputOption('sleep', null, InputOption::VALUE_OPTIONAL, 'finishedAt', 0),
        ]);
        $this->updateConfigWithDBTimeoutOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startDeviceId = $input->getOption('startDeviceId');
        $finishDeviceId = $input->getOption('finishDeviceId');
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $sleep = $input->getOption('sleep');
        $deviceIds = $this->emSource->getRepository(Device::class)
            ->getDeviceIds(startDeviceId: $startDeviceId, finishDeviceId: $finishDeviceId);
        $progressBar = new ProgressBar($output, count($deviceIds));
        $progressBar->start();

        try {
            $this->disableDBTimeoutByInput($input, $this->emSource);
            $this->disableDBTimeoutByInput($input, $this->em);

            foreach ($deviceIds as $deviceId) {
                $output->writeln('Device ID: ' . $deviceId);
                $sql = 'SELECT * FROM device WHERE id = ?';
                $deviceSourceData = $this->emSource->getConnection()->executeQuery($sql, [$deviceId])->fetchAssociative();
                $deviceSource = $deviceSourceData ? new Device($deviceSourceData) : null;
                $deviceDestiny = $this->em->getRepository(Device::class)->find($deviceId);

                if (!$deviceSource || !$deviceDestiny) {
                    continue;
                }
                if ($deviceSource->getImei() != $deviceDestiny->getImei()) {
                    $output->writeln('Devices imeis are not equal - source: ' . $deviceSource->getImei()
                        . ', destiny: ' . $deviceDestiny->getImei());
                    continue;
                }

                $device = $deviceDestiny;
                $url = $this->getUrlByDevice($device);
                $payloadsQuery = $this->emSource->getRepository(TrackerPayload::class)
                    ->getByDeviceAndRangeQuery($device, $startedAt, $finishedAt);

                /** @var TrackerPayload $payloadEntity */
                foreach ($payloadsQuery->toIterable() as $payloadEntity) {
                    if (!$this->isPayloadValid($device, $payloadEntity)) {
                        continue;
                    }

                    $payload = $payloadEntity->getPayload();
                    $output->writeln(
                        'Payload ID: ' . $payloadEntity->getId() . ', created_at: ' . $payloadEntity->getCreatedAt()->format('Y-m-d H:i:s')
                    );

                    try {
                        $data = $this->preparePayload($device, $payload);
                        $response = $this->BEHttpClient->request('POST', $url, [
                            'json' => $data
                        ]);
                    } catch (\Exception $exception) {
                        $message = sprintf(
                            'Error with Payload ID: %s with message: %s',
                            $payloadEntity->getId(),
                            $exception->getMessage()
                        );
                        $output->writeln(PHP_EOL . $message);
                    }
                }

                $progressBar->advance();

                if ($sleep) {
                    sleep($sleep);
                }
            }

            $this->enableDBTimeoutByInput($input, $this->em);
            $this->enableDBTimeoutByInput($input, $this->emSource);
            $progressBar->finish();
            $output->writeln(PHP_EOL . 'Payloads successfully transferred!');
        } catch (\Exception $exception) {
            $output->writeln(PHP_EOL . $exception->getMessage());
        }

        return 0;
    }

    public function __construct(
        private AuroraEntityManager $emSource,
        EntityManager               $em,
        private string              $BEApiUrl,
        private ?Client             $BEHttpClient
    ) {
        parent::__construct();
        $this->em = $em;
        $this->initBEHttpClient();
    }
}
