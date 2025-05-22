<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\Service\Integration\IntegrationService;
use App\Service\Redis\MemoryDbService;
use Carbon\Carbon;
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

#[AsCommand(name: 'app:fuse:data')]
class FuseDataCommand extends Command
{
    use RedisLockTrait, CommandLoggerTrait;

    private const INTERVAL = 'P1DT12H';
    private const TRIP_DATA_URL = 'https://api.enerfyglobal.com/v1/tripdata';

    public function __construct(
        private ParameterBagInterface $params,
        private MemoryDbService $memoryDb,
        private LoggerInterface $logger,
        private ?Client $httpClient,
        private readonly EntityManager $em,
        private readonly IntegrationService $integrationService
    ) {
        $this->httpClient = new Client([
            'timeout' => 60,
            'connect_timeout' => 60
        ]);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('interval', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('dateTo', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('ignoreLastRouteId', null, InputOption::VALUE_OPTIONAL, 'ignoreLastRouteId', false);
        $this->setDescription('Test command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $teamIdParam = $input->getOption('teamId');
            $interval = $input->getOption('interval') ?? self::INTERVAL;
            $dateFrom = (new \DateTime())->sub(new \DateInterval($interval));
            $dateTo = $input->getOption('dateTo') ? Carbon::parse($input->getOption('dateTo')) : (new \DateTime());
            $ignoreLastRouteId = boolval($input->getOption('ignoreLastRouteId'));
            $integration = $this->em->getRepository(Integration::class)->findOneBy(['name' => Integration::FUSE]);

            if (!$teamIdParam) {
                $teamIds = $this->em->getRepository(Setting::class)->getTeamIdsWithIntegration($integration->getId());
            } else {
                $teamIds = [$teamIdParam];
            }

            foreach ($teamIds as $teamId) {
                $integrationData = $this->em->getRepository(IntegrationData::class)
                    ->findByTeamIdAndIntegration($teamId, $integration);

                if (!$integrationData || !$integrationData->isEnabled()) {
                    continue;
                }

                $lastRouteId = $this->memoryDb->get('fuse_team_' . $teamId);
                $newLastRouteId = null;
                $vehicles = $this->integrationService->getScopeEntityIds($integrationData->getScope());

                /** @var Vehicle $vehicle */
                foreach ($vehicles as $vehicle) {
                    $rQuery = $this->em->getRepository(Route::class)
                        ->getRouteIterableByVehicleId($vehicle->getId(), $dateFrom, $dateTo);

                    foreach ($rQuery->toIterable() as $route) {
                        if ($lastRouteId && ($route->getId() <= $lastRouteId) && !$ignoreLastRouteId) {
                            continue;
                        } else {
                            $newLastRouteId = $newLastRouteId < $route->getId() ? $route->getId() : $newLastRouteId;
                        }

                        $ths = $this->em->getRepository(TrackerHistory::class)
                            ->getThDataByRoute($route, 'th.ts, th.speed, th.lng, th.lat, th.alt, th.angle');
                        if (!$ths) {
                            continue;
                        }
                        $routeItem = $this->makeRouteItem($route, $ths);

                        try {
                            $output->writeln('Route id: ' . $route->getId());
                            $response = $this->httpClient->post(
                                self::TRIP_DATA_URL,
                                [
                                    RequestOptions::HEADERS => [
                                        'LicenseKey' => $this->params->get('fuse_license_key') ?? null,
                                        'x-functions-key' => $this->params->get('fuse_x_functions_key') ?? null,
                                    ],
                                    RequestOptions::JSON => $routeItem
                                ]
                            );
                            $output->writeln($response->getStatusCode());
                        } catch (\Exception $exception) {
                            $this->logException($exception);
                            $output->writeln($exception->getMessage());
                            $output->writeln(json_encode($routeItem ?? []));
                        }
                    }

                }
                $this->em->clear();

                if ($newLastRouteId) {
                    $this->memoryDb->set('fuse_team_' . $teamId, $newLastRouteId);
                }
            }
        } catch (\Exception $exception) {
            $this->logException($exception);
            $output->writeln($exception->getMessage());
        }

        return 0;
    }

    private function makeRouteItem(Route $route, array $ths)
    {
        $samples = array_map(function ($th) {
            return [
                't' => $th['ts']->format('YmdHisv'),
                's' => $th['speed'],
                'lt' => floatval($th['lat']),
                'ln' => floatval($th['lng']),
                'a' => $th['alt'],
                'c' => $th['angle'],
            ];
        }, $ths);

        return [
            'nodeId' => $route->getDevice()->getClient(),
            'vehicleId' => $route->getVehicle()->getRegNo(),
            'driverId' => ($route->getDriver()?->getDriverId()) ?: '',
            'vehicleRegNo' => $route->getVehicle()->getRegNo(),
            'driverName' => $route->getDriver()?->getFullName(),
            'tripId' => $route->getId(),
            'startTime' => $route->getStartedAt()->format('YmdHisv'),
            'endTime' => $route->getFinishedAt()->format('YmdHisv'),
            'samples' => $samples
        ];
    }
}
