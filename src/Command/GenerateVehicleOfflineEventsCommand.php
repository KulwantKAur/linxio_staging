<?php

namespace App\Command;

use App\Command\Traits\DevicebleTrait;
use App\Command\Traits\TeamTrait;
use App\Entity\Device;
use App\Entity\EventLog\EventLog;
use App\Entity\Notification\Event;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;
use App\Service\Redis\MemoryDbService;
use App\Service\Setting\SettingService;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'app:vehicle:generate-offline-events')]
class GenerateVehicleOfflineEventsCommand extends Command
{
    use DevicebleTrait, TeamTrait;

    private const BATCH_SIZE = 50;

    protected function configure(): void
    {
        $this->setDescription('Generate vehicle offline events');
        $this->getDefinition()->addOptions([
            new InputOption('startedAt', null, InputOption::VALUE_OPTIONAL, 'startedAt', null),
            new InputOption('finishedAt', null, InputOption::VALUE_OPTIONAL, 'finishedAt', null),
        ]);
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithTeamOptions();
    }

    public function __construct(
        private readonly EntityManager $em,
        private readonly SettingService $settingService,
        private readonly VehicleService $vehicleService,
        private readonly MemoryDbService $memoryDb,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NotificationEventDispatcher $notificationDispatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counter = 0;
        $startedAt = $input->getOption('startedAt');
        $finishedAt = $input->getOption('finishedAt');
        $teamIds = $this->getTeamIdsByInput($input);
        $deviceIds = $this->getDeviceIdsByInput($input)
            ?: $this->em->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
        $devicesQuery = $this->em->getRepository(Device::class)->getDevicesQuery($deviceIds, $teamIds, true);
        $devicesCount = $this->em->getRepository(Device::class)->getDevicesCount($deviceIds, $teamIds, true);
        $progressBar = new ProgressBar($output, $devicesCount);
        $progressBar->start();

        /** @var Device $device */
        foreach ($devicesQuery->toIterable() as $device) {
            $team = $this->em->getRepository(Team::class)->find($device->getTeamId());
            $gpsStatusDurationSetting = $this->settingService
                ->getTeamSettingValueByKey($team, Setting::GPS_STATUS_DURATION);
            $thData = $this->em->getRepository(TrackerHistory::class)
                ->getTrackerRecordsByDeviceInRangeQuery($device->getId(), $startedAt, $finishedAt);
            $lastTHTS = null;

            try {
                /** @var TrackerHistory $thDatum */
                foreach ($thData->toIterable() as $thDatum) {
                    $vehicle = $device->getVehicle();
                    $dtDiffSec = $lastTHTS ? $thDatum->getTs()->getTimestamp() - $lastTHTS : 0;
                    $lastTHTS = $thDatum->getTs()->getTimestamp();

                    if ($gpsStatusDurationSetting
                        && $gpsStatusDurationSetting['enable']
                        && $dtDiffSec > $gpsStatusDurationSetting['value']
                    ) {
                        $address = $this->em->getRepository(Route::class)->getClosestAddressByDate($thDatum);
                        $context = [
                            'lastCoordinates' => $thDatum->toArrayCoordinates(),
                            'address' => $address,
                            'duration' => $dtDiffSec,
                            'gpsStatusDurationSetting' => $gpsStatusDurationSetting['value'] ?? 0
                        ];

                        $event = $this->em->getRepository(Event::class)->getEventByName(Event::VEHICLE_OFFLINE);
                        $eventLogOffline = $this->em->getRepository(EventLog::class)
                            ->findEventLogByEventAndVehicleIdAndEventDate($event, $vehicle->getId(), $thDatum->getTs());

                        if (!$eventLogOffline) {
                            $this->notificationDispatcher->dispatch(
                                Event::VEHICLE_OFFLINE, $vehicle, $thDatum->getTs(), $context
                            );
                            $eventLogOffline = $this->em->getRepository(EventLog::class)
                                ->findEventLogByEventAndVehicleIdAndEventDate(
                                    $event, $vehicle->getId(), $thDatum->getTs()
                                );
                            $output->writeln(
                                'Event: ' . $eventLogOffline?->getId() . ', Vehicle: ' . $vehicle->getId()
                            );
                        }
                    }

                    if (($counter % self::BATCH_SIZE) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }

                    ++$counter;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln('Team: ' . $team->getId() . ', device: ' . $device->getId());
                $output->writeln($e->getTraceAsString());
            }
        }


        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Vehicle offline events successfully generated!');
        $this->em->flush();
        $this->em->clear();

        return 0;
    }
}