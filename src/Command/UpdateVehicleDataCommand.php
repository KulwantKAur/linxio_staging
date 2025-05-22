<?php

namespace App\Command;

use App\Command\Traits\ProcessableTrait;
use App\Entity\Device;
use App\Entity\DriverHistory;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\Vehicle;
use App\Events\Vehicle\VehicleStatusChangedEvent;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use App\Service\Setting\SettingService;
use App\Service\Vehicle\VehicleService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'app:vehicle:update-data')]
class UpdateVehicleDataCommand extends Command
{
    use ProcessableTrait;

    private const BATCH_SIZE = 50;

    private $em;
    private $settingService;
    private $vehicleService;
    private $memoryDb;
    private EventDispatcherInterface $eventDispatcher;

    protected function configure(): void
    {
        $this->setDescription('Update vehicle data: driver, status');
        $this->updateConfigWithProcessOptions();
    }

    public function __construct(
        EntityManager $em,
        SettingService $settingService,
        VehicleService $vehicleService,
        MemoryDbService $memoryDb,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->em = $em;
        $this->settingService = $settingService;
        $this->vehicleService = $vehicleService;
        $this->memoryDb = $memoryDb;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counter = 0;
        $teams = $this->em->getRepository(Team::class)->findAll();
        $deviceIds = $this->getSlicedItemsByProcess(
            $this->em->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations(),
            $input,
            $output
        );
        $progressBar = new ProgressBar($output, count($teams));
        $progressBar->start();

        /** @var Team $team */
        foreach ($teams as $team) {
            $vehiclesData = $this->em->getRepository(Vehicle::class)->getVehiclesWithDeviceData($team, $deviceIds);

            if (!$vehiclesData) {
                $progressBar->advance();
                continue;
            }

            $gpsStatusDurationSetting = $this->settingService
                ->getTeamSettingValueByKey($team, Setting::GPS_STATUS_DURATION);
            $vehicleEngineOffSetting = $this->settingService
                ->getTeamSettingValueByKey($team, Setting::VEHICLE_ENGINE_OFF);

            foreach ($vehiclesData as $vehicleDatum) {
                try {
                    /** @var Vehicle $vehicle */
                    $vehicle = $vehicleDatum['vehicle'];
                    $device = $vehicle->getDevice();
                    $dtDiffSec = (new \DateTime())->getTimestamp()
                        - $vehicleDatum['lastDataReceivedAt']->getTimestamp();
                    $deviceStatus = TrackerHistory::getDeviceStatusByIgnitionAndMovement(
                        $vehicleDatum['ignition'], $vehicleDatum['movement']
                    );
                    $output->writeln('vehicle id: ' . $vehicle->getId());

                    if ($vehicleEngineOffSetting['enable'] && $dtDiffSec > $vehicleEngineOffSetting['value'] &&
                        $deviceStatus === Device::STATUS_STOPPED && $vehicle->getDriver()
                    ) {
                        $lastDriverHistory = $this->em->getRepository(DriverHistory::class)
                            ->findByUnfinishedHistory($vehicle, $vehicle->getDriver());
                        if ($lastDriverHistory) {
                            $sFromStart = Carbon::instance($lastDriverHistory->getStartDate())
                                ->diffInSeconds(new Carbon(), false);
                            if (isset($sFromStart) && $sFromStart > $vehicleEngineOffSetting['value']) {
                                $this->vehicleService->unsetVehicleDriver($vehicle, $vehicle->getDriver());
                            }
                        }
                    }

                    if ($gpsStatusDurationSetting
                        && $gpsStatusDurationSetting['enable']
                        && $dtDiffSec > $gpsStatusDurationSetting['value']
                    ) {
                        if ($vehicle->getStatus() != Vehicle::STATUS_OFFLINE) {
                            $vehicle->setStatus(Vehicle::STATUS_OFFLINE);
//                            $output->writeln('gpsStatusDurationSetting: ' . $gpsStatusDurationSetting['value']);
                            $this->eventDispatcher->dispatch(new VehicleStatusChangedEvent($vehicle,
                                ['gpsStatusDurationSetting' => $gpsStatusDurationSetting['value']]),
                                VehicleStatusChangedEvent::NAME);
                        }
                        if ($device->getStatus() != Device::STATUS_OFFLINE) {
                            $device->setStatus(Device::STATUS_OFFLINE);
                            $device->setStatusExt(Device::STATUS_EXT_OFFLINE);
                        }
                    }

                    if ($vehicleEngineOnTime = $this->memoryDb->get(
                        VehicleRedisModel::getEngineOnTimeKey($vehicle))
                    ) {
                        $vehicle->setEngineOnTime($vehicleEngineOnTime);
                    }

                    if (($counter % self::BATCH_SIZE) === 0) {
                        $this->em->flush();
//                        $this->em->clear();
                    }

                    ++$counter;
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    $output->writeln('Team: ' . $team->getId());
                    $output->writeln($e->getTraceAsString());
                }
            }

            $this->em->flush();
            $this->em->clear();

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Vehicle data successfully updated!');
        $this->em->flush();
        $this->em->clear();

        return 0;
    }
}