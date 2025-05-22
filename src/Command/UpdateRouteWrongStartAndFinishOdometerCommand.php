<?php

namespace App\Command;

use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\TeamTrait;
use App\Entity\Device;
use App\Entity\DrivingBehavior;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:route:update-odometer')]
class UpdateRouteWrongStartAndFinishOdometerCommand extends Command
{
    use DevicebleTrait, TeamTrait;

    private function updateOdometerInRelatedData(
        Device             $device,
        Route              $route,
        int                $odometer,
        \DateTimeInterface $validOdometerStartedAt
    ) {
        // @todo think about device_installation
        $resultTHs = $this->em->getRepository(TrackerHistory::class)->updateTrackerHistoriesOdometer(
            $route->getDevice(),
            $route->getVehicle(),
            $odometer,
            $route->getStartedAt(),
            $validOdometerStartedAt,
        );
        $resultDrivBeh = $this->em->getRepository(DrivingBehavior::class)->updateOdometerByRangeAndDevice(
            $device,
            $route->getVehicle(),
            $odometer,
            $route->getStartedAt(),
            $validOdometerStartedAt
        );
        $route->setStartOdometer($odometer);
        $route->setDistance($route->getFinishOdometer() - $route->getStartOdometer());
    }

    protected function configure(): void
    {
        $this->setDescription('Update route wrong start and finish odometer data');
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithTeamOptions();
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
            new InputOption('odoDiff', null, InputOption::VALUE_OPTIONAL, 'odoDiff', 1000000),
            new InputOption('dayDiff', null, InputOption::VALUE_OPTIONAL, 'dayDiff', null),
            new InputOption('ignoreSave', null, InputOption::VALUE_OPTIONAL, 'ignoreSave', false),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $odoDiff = $input->getOption('odoDiff');
        $dayDiff = $input->getOption('dayDiff');
        $ignoreSave = $input->getOption('ignoreSave');
        $deviceIds = $this->getDeviceIdsByInput($input);
        $teamIds = $this->getTeamIdsByInput($input);
        $devicesQuery = $this->em->getRepository(Device::class)->getDevicesQuery($deviceIds, $teamIds);
        $devicesCount = $this->em->getRepository(Device::class)->getDevicesCount($deviceIds, $teamIds);
        $progressBar = new ProgressBar($output, $devicesCount);
        $progressBar->start();

        /** @var Device $device */
        foreach ($devicesQuery->toIterable() as $device) {
            try {
                $output->writeln('Device ID: ' . $device->getId());
                $routesQuery = $this->em->getRepository(Route::class)
                    ->getRoutesWithWrongStartAndFinishOdometerQuery($device, $odoDiff, $dayDiff, $startedAt, $finishedAt);

                /** @var Route $route */
                foreach ($routesQuery->toIterable() as $route) {
                    $output->writeln('Route ID: ' . $route->getId());
                    $odometerData = $this->em->getRepository(TrackerHistory::class)
                        ->getDataWithMinValidOdometerForRoute($route, $odoDiff);

                    if (!$odometerData) {
                        continue;
                    }

                    $validOdometer = $odometerData['min_odo'];
                    $validOdometerStartedAt = $odometerData['ts'];
                    $outputData = [
                        'odometerToSet' => $validOdometer,
                        'updateStartedAt' => $route->getStartedAt()?->format('Y-m-d H:i:s'),
                        'updateFinishedAt' => $validOdometerStartedAt?->format('Y-m-d H:i:s'),
                        'routeOld' => $route->toArray(['distance', 'startOdometer', 'finishOdometer']),
                    ];

                    if (!$ignoreSave) {
                        $this->updateOdometerInRelatedData($device, $route, $validOdometer, $validOdometerStartedAt);
                        $this->em->flush();
                        $outputData['routeNew'] = $route->toArray(['distance', 'startOdometer', 'finishOdometer']);
                    }

                    $output->writeln(json_encode($outputData));
                }

                $this->em->clear();
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln('Device: ' . $device->getId());
                $output->writeln($e->getTraceAsString());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Route odometer and related entities successfully updated!');

        return 0;
    }

    public function __construct(private EntityManager $em)
    {
        parent::__construct();
    }
}