<?php

namespace App\Command;

use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\DrivingBehavior;
use App\Entity\Notification\Event;
use App\Entity\Route;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\VehicleOdometer;
use App\Service\Device\DeviceCommandService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\Notification\EventDispatcher as NotificationEventDispatcher;

#[AsCommand(name: 'app:device:update-data')]
class UpdateDeviceDataCommand extends Command
{
    private const BATCH_SIZE = 50;
    private const ODOMETER_ALLOWED_VALUE_OFFSET = 1000000; // meters
    private const ODOMETER_ALLOWED_TIME_OFFSET = 60 * 60 * 4; // seconds
    private const DEVICE_COMMAND_RESPONSE_OFFSET_TIME = 60 * 60 * 24 * 2; // seconds

    private EntityManager $em;
    private DeviceCommandService $deviceCommandService;
    private NotificationEventDispatcher $notificationDispatcher;

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @param int $DIOdometer
     * @param mixed $dateTo
     */
    private function updateOdometerInRelatedData(
        Device $device,
        DeviceInstallation $deviceInstallation,
        int $DIOdometer,
        $dateTo
    ) {
        // @todo think about handling time if it's long
        $resultTHs = $this->em->getRepository(TrackerHistory::class)->updateTrackerHistoriesOdometer(
            $device,
            $deviceInstallation->getVehicle(),
            $DIOdometer,
            $deviceInstallation->getInstallDate(),
            $dateTo
        );
        $resultRoutes = $this->em->getRepository(Route::class)->updateRoutesDistanceAndOdometer(
            $device,
            $deviceInstallation->getVehicle(),
            $DIOdometer,
            $deviceInstallation->getInstallDate(),
            $dateTo
        );
        $resultDrivBeh = $this->em->getRepository(DrivingBehavior::class)->updateOdometerByRangeAndDevice(
            $device,
            $deviceInstallation->getVehicle(),
            $DIOdometer,
            $deviceInstallation->getInstallDate(),
            $dateTo
        );
        $deviceLastTrackerRecord = $device->getLastTrackerRecord();

        if ($deviceLastTrackerRecord && $deviceLastTrackerRecord->getTs() <= $dateTo) {
            $deviceLastTrackerRecord->setOdometer($DIOdometer);
        }
        // @todo clear redis cache for dashboard
    }

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @param int $DIOdometer
     * @return DeviceInstallation
     */
    private function handleOdometerIfDIIsUninstalled(
        Device $device,
        DeviceInstallation $deviceInstallation,
        int $DIOdometer
    ): DeviceInstallation {
        $this->updateOdometerInRelatedData(
            $device,
            $deviceInstallation,
            $DIOdometer,
            $deviceInstallation->getUninstallDate()
        );
        $deviceInstallation->setIsOdometerSynced(true);

        return $deviceInstallation;
    }

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @param int $DIOdometer
     * @return DeviceInstallation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function handleOdometerIfDIIsNotUninstalled(
        Device $device,
        DeviceInstallation $deviceInstallation,
        int $DIOdometer
    ): DeviceInstallation {
        $commandRecord = $this->deviceCommandService
            ->getOdometerLastRecordByDeviceInstallation($device, $deviceInstallation);

        if ($commandRecord && $commandRecord->isSent()) {
            if ($commandRecord->isResponded()) {
                $this->handleOdometerIfCommandWithResponse($device, $deviceInstallation, $commandRecord, $DIOdometer);
            } else {
                $this->handleOdometerIfCommandWithoutResponse($device, $deviceInstallation, $commandRecord, $DIOdometer);
            }
        } else {
            $this->updateOdometerInRelatedData($device, $deviceInstallation, $DIOdometer, new \DateTime());

            // to avoid infinite update of device's data
            if (!$commandRecord || ($commandRecord->getCreatedAt()
                < (new Carbon())->subRealSeconds(self::DEVICE_COMMAND_RESPONSE_OFFSET_TIME))
            ) {
                $deviceInstallation->setIsOdometerSynced(true);
            }
        }

        return $deviceInstallation;
    }

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @param TrackerCommand $commandRecord
     * @param int $DIOdometer
     * @return DeviceInstallation
     * @throws \Exception
     */
    private function handleOdometerIfCommandWithResponse(
        Device $device,
        DeviceInstallation $deviceInstallation,
        TrackerCommand $commandRecord,
        int $DIOdometer
    ): DeviceInstallation {
        // @todo think if it should have vehicle
        $deviceLastRecord = $device->getLastTrackerRecord();

        if ($deviceLastRecord && $deviceLastRecord->getOdometer()) {
            if (abs($DIOdometer - $deviceLastRecord->getOdometer()) > self::ODOMETER_ALLOWED_VALUE_OFFSET) {
                // @todo check if it's good to compare DateTime
                if ($deviceLastRecord->getTs() > Carbon::parse($commandRecord->getRespondedAt())
                        ->addRealSeconds(self::ODOMETER_ALLOWED_TIME_OFFSET)
                ) {
                    // @todo implement notification logic for not applied command with odometer for device
                    $this->notificationDispatcher->dispatch(Event::DEVICE_COMMAND_IS_NOT_APPLIED, $commandRecord);
                    $this->updateOdometerInRelatedData(
                        $device,
                        $deviceInstallation,
                        $DIOdometer,
                        new \DateTime()
                    );
                    $deviceInstallation->setIsOdometerSynced(true);
                } else { // in this case command with odometer hasn't yet been applied, wait for it
                    $this->updateOdometerInRelatedData(
                        $device,
                        $deviceInstallation,
                        $DIOdometer,
                        new \DateTime()
                    );
                }
            } else {
                $this->updateOdometerInRelatedData(
                    $device,
                    $deviceInstallation,
                    $DIOdometer,
                    $deviceLastRecord->getTs()
                );
                $deviceInstallation->setIsOdometerSynced(true);
                $resultVO = $this->em->getRepository(VehicleOdometer::class)->updateOdometerAndAccuracyByRangeAndDevice(
                    $device,
                    $deviceInstallation->getVehicle(),
                    $DIOdometer,
                    $deviceInstallation->getInstallDate(),
                    $deviceInstallation->getUninstallDate()
                );
            }
        } else { // if no data from device then check DI install time to avoid infinite check DI
            if ($deviceInstallation->getInstallDate()
                < (new Carbon())->subRealSeconds(self::ODOMETER_ALLOWED_TIME_OFFSET)
            ) {
                $deviceInstallation->setIsOdometerSynced(true);
            }
        }

        return $deviceInstallation;
    }

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @param TrackerCommand $commandRecord
     * @param int $DIOdometer
     * @return DeviceInstallation
     * @throws \Exception
     */
    private function handleOdometerIfCommandWithoutResponse(
        Device $device,
        DeviceInstallation $deviceInstallation,
        TrackerCommand $commandRecord,
        int $DIOdometer
    ): DeviceInstallation {
        $this->updateOdometerInRelatedData($device, $deviceInstallation, $DIOdometer, new \DateTime());

        // to avoid infinite update of device's data
        if ($commandRecord->getSentAt() < (new Carbon())->subRealSeconds(self::DEVICE_COMMAND_RESPONSE_OFFSET_TIME)) {
            $deviceInstallation->setIsOdometerSynced(true);
        }

        return $deviceInstallation;
    }

    /**
     * @param Device $device
     * @throws \Exception
     */
    private function fixDeviceOdometerHistory(Device $device)
    {
        $deviceInstallationsCount = $this->em->getRepository(DeviceInstallation::class)
            ->getDeviceInstallationsNotSyncedCount($device);

        if ($deviceInstallationsCount > 0) {
            $deviceInstallations = $this->em->getRepository(DeviceInstallation::class)
                ->getDeviceInstallationsWithOdometerNotSynced($device);

            foreach ($deviceInstallations as $key => $deviceInstallation) {
                // @todo think if there is no odo-commands for vendor then skip without update THs?
                if (!$this->deviceCommandService->isEnabled($device)) {
                    $deviceInstallation->setIsOdometerSynced(true);
                    continue;
                }

                $DIOdometer = $deviceInstallation->getOdometer();

                if ($deviceInstallation->isUninstalled()) {
                    $this->handleOdometerIfDIIsUninstalled($device, $deviceInstallation, $DIOdometer);
                } else {
                    $this->handleOdometerIfDIIsNotUninstalled($device, $deviceInstallation, $DIOdometer);
                }
            }

            $this->em->flush();
        }
    }

    protected function configure(): void
    {
        $this->setDescription('Update device data');
    }

    /**
     * @param EntityManager $em
     * @param DeviceCommandService $deviceCommandService
     * @param NotificationEventDispatcher $notificationDispatcher
     */
    public function __construct(
        EntityManager $em,
        DeviceCommandService $deviceCommandService,
        NotificationEventDispatcher $notificationDispatcher
    ) {
        $this->em = $em;
        $this->deviceCommandService = $deviceCommandService;
        $this->notificationDispatcher = $notificationDispatcher;
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
        $counter = 0;
        $devicesQuery = $this->em->getRepository(Device::class)->getDevicesQuery();
        $devicesCount = $this->em->getRepository(Device::class)->getDevicesCount();
        $progressBar = new ProgressBar($output, $devicesCount);
        $progressBar->start();

        /** @var Device $device */
        foreach ($devicesQuery->toIterable() as $device) {
            try {
                $output->writeln('Device: ' . $device->getId());

                if (!$device->getLastDataReceivedAt()) {
                    $lastTrackerHistory = $device->getLastTrackerHistory();

                    if ($lastTrackerHistory) {
                        $device->setLastDataReceivedAt($lastTrackerHistory->getCreatedAt());
                    }
                }
                if (
                    (!$device->getLastDataReceivedAt() || Carbon::now()->diffInSeconds(
                            $device->getLastDataReceivedAt()
                        ) > Device::STATUS_EXT_OFFLINE_LIMIT)
                    && $device->getStatusExt() != Device::STATUS_EXT_OFFLINE
                ) {
                    $device->setStatusExt(Device::STATUS_EXT_OFFLINE);
                }

                $this->fixDeviceOdometerHistory($device);

                if (($counter % self::BATCH_SIZE) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }

                ++$counter;
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln($e->getTraceAsString());
            }

            $progressBar->advance();

        }

        $progressBar->finish();
        $this->em->flush();
        $this->em->clear();
        $output->writeln(PHP_EOL . 'Device data successfully updated!');

        return 0;
    }
}