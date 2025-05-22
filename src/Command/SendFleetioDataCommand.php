<?php

namespace App\Command;

use App\Entity\FleetioVehicle;
use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryDTCVIN;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\Integration\IntegrationService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleRetry\GuzzleRetryMiddleware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:send-fleetio-data')]
class SendFleetioDataCommand extends Command
{
    private const MAIN_URL = 'https://secure.fleetio.com/api/v1/';
    private const VEHICLES_URL = self::MAIN_URL . 'vehicles';
    private const VEHICLE_TYPES_URL = self::MAIN_URL . 'vehicle_types';
    private const VEHICLE_STATUSES_URL = self::MAIN_URL . 'vehicle_statuses';
    private const BULK_URL = self::MAIN_URL . 'bulk_api_jobs';
    private const INTERVAL = 'P1D';

    private $em;
    private $slaveEntityManager;
    private $apiKey = null;
    private $accountToken = null;
    private $httpClient;
    private $defaultVehicleTypeId = null;
    private $defaultVehicleStatusId = null;
    private $output = null;
    private $integrationService;
    private $partnerToken;
    private $headers;

    protected function configure(): void
    {
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('interval', null, InputOption::VALUE_OPTIONAL);
        $this->setDescription('Send fleetion data');
    }

    public function __construct(
        EntityManager $em,
        SlaveEntityManager $slaveEntityManager,
        IntegrationService $integrationService,
        string $partnerToken
    ) {
        $this->em = $em;
        $this->slaveEntityManager = $slaveEntityManager;
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());
        $this->httpClient = new Client(['timeout' => 60, 'connect_timeout' => 60, 'handler' => $stack]);
        $this->integrationService = $integrationService;
        $this->partnerToken = $partnerToken;

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
        $teamIdParam = $input->getOption('teamId');
        $interval = $input->getOption('interval') ?? self::INTERVAL;
        $dateFrom = (new \DateTime())->sub(new \DateInterval($interval));
        $dateTo = (new \DateTime());

        $integration = $this->slaveEntityManager->getRepository(Integration::class)->findOneBy(['name' => Integration::FLEETIO]);
        if (!$teamIdParam) {
            $teamIds = $this->slaveEntityManager->getRepository(Setting::class)->getTeamIdsWithIntegration($integration->getId());
        } else {
            $teamIds = [$teamIdParam];
        }
        foreach ($teamIds as $teamId) {
            try {
                /** @var IntegrationData $integrationData */
                $integrationData = $this->slaveEntityManager->getRepository(IntegrationData::class)
                    ->findByTeamIdAndIntegration($teamId, $integration);
                if (!$integrationData || !$integrationData->isEnabled()) {
                    continue;
                }
                $output->writeln('Team id: ' . $teamId);
                $this->updateCredentials($integrationData);

                $vehicles = $this->integrationService->getScopeEntityIds($integrationData->getScope());

                foreach (array_chunk($vehicles, 90) as $chunk) {
                    sleep(1);
                    $odometerData = $this->getVehiclesOdometerData($chunk);
                    $this->sendOdometer($odometerData);
                    $output->writeln('Odometer data: ' . json_encode($odometerData));
                    sleep(1);
                    $locationData = $this->getVehiclesLocationData($chunk, $dateFrom, $dateTo);
                    $this->sendLocation($locationData);
//                    $output->writeln('Location data: ' . json_encode($locationData));
                    sleep(1);
                    $dtcData = $this->getVehiclesDtcData($chunk, $dateFrom, $dateTo);
                    $this->sendDtc($dtcData);
//                    $output->writeln('Dtc data: ' . json_encode($dtcData));

                    $integrationData->setLastUpdatedAt(new \DateTime());
                }
                $this->em->flush();
            } catch (\Exception $exception) {
                $this->em->flush();
                $output->writeln($exception->getMessage());
                $output->writeln($exception->getTraceAsString());
            }
        }

        return 0;
    }

    private function sendOdometer(array $data)
    {
        $data = [
            'resource' => 'meter_entry',
            'operation' => 'create',
            'records' => $data
        ];
        $this->sendData(self::BULK_URL, $data);
    }

    private function sendLocation(array $data)
    {
        foreach (array_chunk($data, 90) as $chunk) {
            $preparedData = [
                'resource' => 'location_entry',
                'operation' => 'create',
                'records' => $chunk
            ];
            $this->sendData(self::BULK_URL, $preparedData);
        }
    }

    private function sendDtc(array $data)
    {
        foreach (array_chunk($data, 90) as $chunk) {
            $preparedData = [
                'resource' => 'fault',
                'operation' => 'create',
                'records' => $chunk
            ];
            $this->sendData(self::BULK_URL, $preparedData);
        }
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
            $this->output->writeln($response->getBody()->getContents());
        } catch (\Exception $exception) {
            $this->output->writeln($exception->getMessage());
        }
    }

    private function findOrCreateVehicle(Vehicle $vehicle)
    {
        try {
            $fleetioVehicleId = $this->em->getRepository(FleetioVehicle::class)->getFleetioVehicleIdByVehicle($vehicle);
            if ($fleetioVehicleId) {
                return $fleetioVehicleId;
            }
            //TODO if problems with api request limit
            sleep(1);
            $vehicleList = $this->getVehicleList([
                'q[name_or_vin_or_license_plate_cont]' => $vehicle->getRegNo()
            ]);
            if (isset($vehicleList[0])) {
                return $vehicleList[0]->id;
            } else {
                sleep(1);
                return $this->createVehicle($vehicle);
            }
        } catch (\Exception $exception) {
            $this->output->writeln($exception->getMessage());
        }
    }

    private function getVehicleList(array $params = [])
    {
        $response = $this->httpClient->get(self::VEHICLES_URL, [
            RequestOptions::HEADERS => $this->headers,
            RequestOptions::QUERY => $params
        ]);

        return json_decode($response->getBody()->getContents());
    }

    private function createVehicle(Vehicle $vehicle)
    {
        $response = $this->httpClient->post(
            self::VEHICLES_URL,
            [
                RequestOptions::HEADERS => $this->headers,
                RequestOptions::JSON => [
                    'fuel_volume_units' => 'liters',
                    'meter_unit' => 'km',
                    'license_plate' => $vehicle->getRegNo(),
                    'name' => $vehicle->getRegNo(),
                    'ownership' => 'owned',
                    'system_of_measurement' => 'metric',
                    'vehicle_status_id' => $this->getDefaultVehicleStatusId(),
                    'vehicle_type_id' => $this->getDefaultVehicleTypeId(),
                ]
            ]
        );
        $fleetioVehicleData = json_decode($response->getBody()->getContents());
        $fleetioVehicle = new FleetioVehicle([
            'vehicle' => $vehicle,
            'fleetioVehicleId' => $fleetioVehicleData->id
        ]);
        $this->em->persist($fleetioVehicle);
        $this->em->flush();
        $this->em->refresh($vehicle);

        return $fleetioVehicle->getFleetioVehicleId();
    }

    private function getDefaultVehicleTypeId()
    {
        if (!$this->defaultVehicleTypeId) {
            $types = $this->getVehicleTypes(['q[name_cont]' => 'car']);
            if (isset($types[0])) {
                $this->defaultVehicleTypeId = $types[0]->id;
            }
        }

        return $this->defaultVehicleTypeId;
    }

    private function getDefaultVehicleStatusId()
    {
        if (!$this->defaultVehicleStatusId) {
            $types = $this->getVehicleStatuses(['q[name_cont]' => 'active']);
            if (isset($types[1])) {
                $this->defaultVehicleStatusId = $types[1]->id;
            }
        }

        return $this->defaultVehicleStatusId;
    }

    private function getVehicleTypes(array $params = [])
    {
        $response = $this->httpClient->get(self::VEHICLE_TYPES_URL, [
            RequestOptions::HEADERS => $this->headers,
            RequestOptions::QUERY => $params
        ]);

        return json_decode($response->getBody()->getContents());
    }

    private function getVehicleStatuses(array $params = [])
    {
        $response = $this->httpClient->get(self::VEHICLE_STATUSES_URL, [
            RequestOptions::HEADERS => $this->headers,
            RequestOptions::QUERY => $params
        ]);

        return json_decode($response->getBody()->getContents());
    }

    private function updateCredentials(?IntegrationData $integrationData)
    {
        if ($integrationData) {
            $this->apiKey = $integrationData->getData()['apiKey'] ?? null;
            $this->accountToken = $integrationData->getData()['accountToken'] ?? null;
        } else {
            $this->apiKey = null;
            $this->accountToken = null;
        }

        $this->headers = [
            'Authorization' => "Token " . $this->apiKey,
            'Account-Token' => $this->accountToken,
            'Content-Type' => 'application/json',
            'HTTP-PARTNER-TOKEN' => $this->partnerToken
        ];
    }

    public function getVehiclesOdometerData($vehicles)
    {
        $data = [];
        /** @var Vehicle $vehicle */
        foreach ($vehicles as $vehicle) {
            sleep(1);
            $vehicleFleetioId = $this->findOrCreateVehicle($vehicle);
            $value = $vehicle->getLastOdometer() ? $vehicle->getLastOdometer() / 1000 : null;
            if (!$value) {
                continue;
            }
            $data[] = [
                'vehicle_id' => $vehicleFleetioId,
                'value' => $value,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
                'gps_provider' => 'Linxio'
            ];
        }

        return $data;
    }

    public function getVehiclesDtcData($vehicles, $dateFrom, $dateTo)
    {
        $data = [];
        /** @var Vehicle $vehicle */
        foreach ($vehicles as $vehicle) {
            $vehicleFleetioId = $this->findOrCreateVehicle($vehicle);
            $ths = $this->em->getRepository(TrackerHistoryDTCVIN::class)
                ->getTrackerHistoryDtcByVehicleAndDate($vehicle, $dateFrom, $dateTo);
            /** @var TrackerHistoryDTCVIN $th */
            foreach ($ths as $th) {
                $data[] = [
                    'code' => $th->getCode(),
                    'status' => 'open',
                    'vehicle_id' => $vehicleFleetioId
                ];
            }
        }

        return $data;
    }

    public function getVehiclesLocationData($vehicles, $dateFrom, $dateTo)
    {
        $data = [];
        /** @var Vehicle $vehicle */
        foreach ($vehicles as $vehicle) {
            $vehicleFleetioId = $this->findOrCreateVehicle($vehicle);
            $ths = $this->em->getRepository(TrackerHistory::class)
                ->getTrackerHistoryByVehicleAndDate($vehicle, $dateFrom, $dateTo);
            /** @var TrackerHistory $th */
            foreach ($ths as $th) {
                $data[] = [
                    'vehicle_id' => $vehicleFleetioId,
                    'date' => $th->getTs()->format('Y-m-d H:i:s'),
                    'latitude' => $th->getLat(),
                    'longitude' => $th->getLng()
                ];
            }
        }

        return $data;
    }
}