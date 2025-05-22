<?php

namespace App\Command;

use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\EntityManager\SlaveEntityManager;
use App\Service\Integration\IntegrationService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleRetry\GuzzleRetryMiddleware;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:send-vwork-data')]
class SendVWorkDataCommand extends Command
{
    private const MAIN_URL = 'https://telemetry.vworkapp.com:7215';
    private const INTERVAL = 'P1D';
    private const DEVICE_PREFIX = 'linxio_';

    private $em;
    private $slaveEntityManager;
    private $httpClient;
    private $output = null;
    private $integrationService;
    private $headers;

    protected function configure(): void
    {
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('interval', null, InputOption::VALUE_OPTIONAL);
        $this->setDescription('Send vWork data');
    }

    public function __construct(
        EntityManager $em,
        SlaveEntityManager $slaveEntityManager,
        IntegrationService $integrationService
    ) {
        $this->em = $em;
        $this->slaveEntityManager = $slaveEntityManager;
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());
        $this->httpClient = new Client(['timeout' => 60, 'connect_timeout' => 60, 'handler' => $stack]);
        $this->integrationService = $integrationService;

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
        $this->output = $output;
        try {
            $teamIdParam = $input->getOption('teamId');
            $interval = $input->getOption('interval') ?? self::INTERVAL;
            $dateFrom = (new \DateTime())->sub(new \DateInterval($interval));
            $dateTo = (new \DateTime());

            $integration = $this->slaveEntityManager->getRepository(Integration::class)->findOneBy(['name' => Integration::VWORK]);
            if (!$teamIdParam) {
                $teamIds = $this->slaveEntityManager->getRepository(Setting::class)->getTeamIdsWithIntegration($integration->getId());
            } else {
                $teamIds = [$teamIdParam];
            }

            foreach ($teamIds as $teamId) {
                /** @var IntegrationData $integrationData */
                $integrationData = $this->slaveEntityManager->getRepository(IntegrationData::class)
                    ->findByTeamIdAndIntegration($teamId, $integration);
                if (!$integrationData || !$integrationData->isEnabled()) {
                    continue;
                }
                $vehicles = $this->integrationService->getScopeEntityIds($integrationData->getScope());

                foreach ($vehicles as $vehicle) {
//                    $output->writeln('Vehicle id: ' . $vehicle->getId());
                    $output->writeln('Vehicle id: ' . $vehicle->getRegNo());
                    $locationData = $this->getVehiclesLocationData($vehicle, $dateFrom, $dateTo);
                    foreach ($locationData as $deviceId => $item) {
                        $output->writeln('Device id: ' . $deviceId);
//                        $output->writeln('Device data: ' . json_encode($item));
                        $this->sendLocation($item, $deviceId);
                        $integrationData->setLastUpdatedAt(new \DateTime());
                    }
                }
                $this->em->flush();
            }
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln($exception->getTraceAsString());
        }

        return 0;
    }

    private function sendLocation(array $data, $deviceId)
    {
        $preparedData = [
            'header' => [
                'version' => '2.0',
                'device_id' => self::DEVICE_PREFIX . $deviceId
            ],
            'beacons' => $data
        ];
        $this->sendData(self::MAIN_URL, $preparedData);
    }

    private function sendData(string $url, array $data)
    {
        try {
            $response = $this->httpClient->post(
                $url,
                [
                    RequestOptions::HEADERS => $this->headers,
                    RequestOptions::JSON => $data
                ]
            );
            $this->output->writeln('response: ' . $response->getBody()->getContents());
            $this->output->writeln('status code' . $response->getStatusCode());
        } catch (\Exception $exception) {
            $this->output->writeln($exception->getMessage());
        }
    }

    public function getVehiclesLocationData($vehicle, $dateFrom, $dateTo)
    {
        $data = [];
        $ths = $this->em->getRepository(TrackerHistory::class)
            ->getTrackerHistoryByVehicleAndDate($vehicle, $dateFrom, $dateTo);
        /** @var TrackerHistory $th */
        foreach ($ths as $th) {
            if (!isset($data[$th->getDeviceId()])) {
                $data[$th->getDeviceId()] = [];
            }
            $data[$th->getDeviceId()][] = $this->formatTHtoVWorkObject($th);
        }

        return $data;
    }

    private function formatTHtoVWorkObject(TrackerHistory $trackerHistory)
    {
        return [
            "effective_at" => $trackerHistory->getTs()->format(\DateTime::ATOM),
            "lat" => $trackerHistory->getLat(),
            "lng" => $trackerHistory->getLng(),
            "speed" => $trackerHistory->getSpeed(),
            "altitude" => $trackerHistory->getAlt(),
            "ignition" => is_null($trackerHistory->getIgnition()) ? (bool)$trackerHistory->getSpeed() : (bool)$trackerHistory->getIgnition()
        ];
    }
}