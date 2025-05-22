<?php

namespace App\Command\Tracker;

use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Asset;
use App\Entity\Device;
use App\Entity\Route;
use App\Entity\Setting;
use App\Service\MapService\MapServiceResolver;
use App\Service\Route\RouteService;
use App\Service\Setting\SettingService;
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Command\Traits\BreakableTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:tracker:update-route-location')]
class UpdateRouteLocationCommand extends Command
{
    private const BATCH_SIZE = 50;

    use RedisLockTrait;
    use BreakableTrait;
    use ProcessableTrait;

    private $em;
    private $routeService;
    private $mapService;
    private $settingService;
    private $assetPersister;
    private $params;

    protected function configure(): void
    {
        $this->setDescription('Update location for new routes');
    }

    public function __construct(
        EntityManager $em,
        RouteService $routeService,
        MapServiceResolver $mapServiceResolver,
        SettingService $settingService,
        ObjectPersister $assetPersister,
        ParameterBagInterface $params
    ) {
        $this->em = $em;
        $this->routeService = $routeService;
        $this->mapService = $mapServiceResolver->getInstance();
        $this->settingService = $settingService;
        $this->assetPersister = $assetPersister;
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
        $this->breakScriptByTTL($this->params->get('calculating_job_ttl'));
        $routeTimeAgo = $this->params->get('route_time_ago_for_location');
        $lock = $this->getLock($this->getName());

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $counter = 0;
        $dateTo = new \DateTime();
        $dateTo->sub(new \DateInterval($routeTimeAgo));
        $routesWithoutLocation = $this->em->getRepository(Route::class)->getRoutesByTimeAgoWithoutLocation($dateTo);

//        $output->writeln('Routes count: ' . count($routesWithoutLocation));
//        $progressBar = new ProgressBar($output, count($routesWithoutLocation));
//        $progressBar->start();

        $teamSettingsById = [];
        $geolocationRouteStopSettings = [];
        $deviceIds = [];

        /** @var Route $route */
        foreach ($routesWithoutLocation->toIterable() as $route) {
            $deviceIds[] = $route->getDevice()->getId();
            $teamId = $this->em->getRepository(Device::class)->getTeamIdByDeviceId($route->getDevice()->getId());

            if (!isset($teamSettingsById[$teamId])) {
                $teamSettingsById[$teamId] = $this->settingService->getTeamSettings($teamId);
            }

            if (!isset($geolocationRouteStopSettings[$teamId])) {
                $geolocationRouteStopSettings[$teamId] = $this->settingService->getSettingValueFromList(
                    $teamSettingsById[$teamId],
                    Setting::GEOLOCATION_ROUTE_STOP
                );
            }

            try {
                if ($this->settingService->isAllowGeolocationForStoppedRoutes(
                    $route->getDevice(),
                    $geolocationRouteStopSettings[$teamId]
                )) {
                    $lastPoint = $route->getLastPoint()->getDevice()->getValidPreviousPoint($route->getLastPoint());

                    if ($lastPoint) {
                        //TODO timeout and rate limit handling
                        //sleep 0,25 sec
                        usleep(250000);
                        $location = $this->mapService->getLocationByCoordinates(
                            $lastPoint->getLat(),
                            $lastPoint->getLng()
                        );

                        if ($location) {
                            $route->setAddress($location);
                            $route->setIsLocationChecked(true);
                        }
                    }

                    if (is_null($route->getAddress())) {
                        $output->writeln('route id: ' . $route->getId());
                        $output->writeln('last point id: ' . (isset($lastPoint) ? $lastPoint->getId() : '---'));
                    }
                }
            } catch (\Exception $exception) {
                $output->writeln(
                    PHP_EOL . sprintf(
                        'Error with routeId: %s with message: %s',
                        $route->getId(),
                        $exception->getMessage()
                    )
                );
            } finally {
                $route->setIsLocationChecked(true);
            }

            if (($counter % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }

            ++$counter;

//            $progressBar->advance();
        }

        $this->em->flush();

        $this->updateAssetsByDeviceId(array_unique($deviceIds));

        $this->em->clear();

//        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Routes location successfully updated!');

        $this->release();

        return 0;
    }

    protected function updateAssetsByDeviceId(array $deviceIds)
    {
        foreach ($deviceIds as $deviceId) {
            /** @var Device $device */
            $device = $this->em->getRepository(Device::class)->find($deviceId);
            if ($device && count($device->getAssets())) {
                foreach ($device->getAssets() as $asset) {
                    $this->updateAssetElasticSearch($asset);
                }
            }
        }
    }

    protected function updateAssetElasticSearch(Asset $asset)
    {
        $this->assetPersister->replaceOne($asset);
    }
}
