<?php

namespace App\Command\Integration;

use App\Command\Traits\RedisLockTrait;
use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Vehicle;
use App\Service\Integration\IntegrationService;
use App\Service\Redis\MemoryDbService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:gearbox:data')]
class GearBoxCommand extends Command
{
    use RedisLockTrait;

    private $params;
    private $memoryDb;
    private const GEARBOX_ODOMETER_URL = 'https://api.gearbox.com.au/public/v1/odometers';
    private const GEARBOX_TOKEN_URL = 'https://api.gearbox.com.au/oauth/token';
    private LoggerInterface $logger;
    private $httpClient;
    private string $clientId;
    private string $clientSecret;

    public function __construct(
        ParameterBagInterface $params,
        MemoryDbService $memoryDbService,
        private readonly EntityManager $em,
        private readonly IntegrationService $integrationService,
        LoggerInterface $logger,
        string $clientId,
        string $clientSecret,
    ) {
        $this->params = $params;
        $this->memoryDb = $memoryDbService;
        $this->logger = $logger;
        $this->httpClient = new Client([
            'timeout' => 60,
            'connect_timeout' => 60
        ]);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL, '');
        $this->setDescription('Gearbox command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $teamIdParam = $input->getOption('teamId');
        $integration = $this->em->getRepository(Integration::class)->findOneBy(['name' => Integration::GEARBOX]);
        if (!$teamIdParam) {
            $teamIds = $this->em->getRepository(Setting::class)->getTeamIdsWithIntegration($integration->getId());
        } else {
            $teamIds = [$teamIdParam];
        }

        if (!$teamIds) {
            return 0;
        }

        foreach ($teamIds as $teamId) {
            try {
                /** @var IntegrationData $integrationData */
                $integrationData = $this->em->getRepository(IntegrationData::class)
                    ->findByTeamIdAndIntegration($teamId, $integration);
                if (!$integrationData || !$integrationData->isEnabled() || !($integrationData->getData()['businessToken'] ?? null)) {
                    continue;
                }

                $response = $this->httpClient->post(
                    self::GEARBOX_TOKEN_URL,
                    [
                        RequestOptions::FORM_PARAMS => [
                            "grant_type" => 'client_credentials',
                            "client_id" => $this->clientId ?? null,
                            "client_secret" => $this->clientSecret ?? null,
                            "business_token" => $integrationData->getData()['businessToken'] ?? null,
                        ]
                    ]
                );
                $accessToken = json_decode($response->getBody())?->access_token ?? null;

                $team = $this->em->getRepository(Team::class)->find($teamId);
                /** @var Client $client */
                $client = $this->em->getRepository(\App\Entity\Client::class)->findOneBy(['team' => $team]);
                $vehicles = $this->integrationService->getScopeEntityIds($integrationData->getScope());

                $timeZone = new \DateTimeZone($client->getTimeZoneName());
                /** @var Vehicle $vehicle */
                foreach ($vehicles as $vehicle) {
                    $odometer = $vehicle->getLastOdometer();
                    $date = $vehicle->getLastTrackerRecordWithOdometer()?->getTs()?->setTimezone($timeZone)->format('Y-m-d');
                    $engineHours = $vehicle->getEngineOnTime();
                    if (!$odometer || !$date) {
                        continue;
                    }

                    try {
                        sleep(2);
                        $data = [
                            "registration" => $vehicle->getRegNo(),
                            "date" => $date,
                            "odometer" => $odometer ? (int)round($odometer / 1000) : null
                        ];

                        if ($engineHours) {
                            $data['hours'] = (int)round($engineHours / 3600);
                        }

                        $response = $this->httpClient->post(
                            self::GEARBOX_ODOMETER_URL,
                            [
                                RequestOptions::HEADERS => [
                                    'Authorization' => 'Bearer ' . $accessToken,
                                ],
                                RequestOptions::JSON => $data
                            ]
                        );
                    } catch (\Exception $exception) {
                        $errorData = [
                            'error' => $exception->getMessage(),
                            'data' => $data
                        ];
                        $output->writeln(json_encode($errorData));
                    }
                }
                $this->em->clear();
            } catch (\Exception $exception) {
                $this->logger->error(ExceptionHelper::convertToJson($exception), [$this->getName()]);
                $output->writeln($exception->getMessage());
            }
        }

        return 0;
    }
}
