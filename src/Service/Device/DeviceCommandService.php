<?php

namespace App\Service\Device;

use App\Entity\Device;
use App\Entity\DeviceInstallation;
use App\Entity\Tracker\TrackerCommand;
use App\Entity\Tracker\TrackerCommand as TrackerCommandEntity;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Service\Tracker\Command\TrackerCommandService;
use App\Service\Tracker\Factory\TrackerCommandFactory;
use App\Service\Tracker\Factory\TrackerFactory;
use App\Service\Tracker\TrackerService;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeviceCommandService extends BaseService
{
    protected $translator;
    private $em;
    private $trackerFactory;
    /** @var TrackerService */
    private $trackerService;
    private $trackerCommandFactory;
    /** @var TrackerCommandService */
    private $trackerCommandService;
    private $validator;
    private $logger;

    /**
     * @param Device $device
     * @throws \Exception
     */
    private function initTrackerServicesByVendorName(Device $device)
    {
        $this->trackerService = null;
        $this->trackerCommandService = null;
        $this->trackerService = $this->trackerFactory->getInstance($device->getVendorName(), $device);
        $this->trackerCommandService = $this->trackerCommandFactory->getInstance($device->getVendorName(), $device);
    }

    /**
     * DeviceService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param TrackerFactory $trackerFactory
     * @param TrackerCommandFactory $trackerCommandFactory
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManager $em,
        TrackerFactory $trackerFactory,
        TrackerCommandFactory $trackerCommandFactory,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->trackerFactory = $trackerFactory;
        $this->trackerCommandFactory = $trackerCommandFactory;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @param string $driverSensorId
     * @throws \Exception
     */
    public function updateDeviceWithNewDriverSensorId(Device $device, User $currentUser, string $driverSensorId)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            if ($this->trackerCommandService->isEnabled()) {
                $commandModel = $this->trackerCommandService->getIButtonCommand(
                    $driverSensorId,
                    $device,
                    TrackerCommandService::ADD_ACTION_TYPE
                );

                if ($commandModel) {
                    $this->trackerService->saveCommandForTracker($device, $currentUser, $commandModel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @param string $driverSensorId
     * @throws \Exception
     */
    public function removeDriverSensorIdFromDevice(Device $device, User $currentUser, string $driverSensorId)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            if ($this->trackerCommandService->isEnabled()) {
                $commandModel = $this->trackerCommandService->getIButtonCommand(
                    $driverSensorId,
                    $device,
                    TrackerCommandService::DELETE_ACTION_TYPE
                );

                if ($commandModel) {
                    $this->trackerService->saveCommandForTracker($device, $currentUser, $commandModel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @param string $sensorId
     * @throws \Exception
     */
    public function updateDeviceWithNewTempAndHumiditySensorId(Device $device, User $currentUser, string $sensorId)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            if ($this->trackerCommandService->isEnabled()) {
                $commandModel = $this->trackerCommandService->getTemperatureAndHumidityCommand(
                    $sensorId,
                    $device,
                    TrackerCommandService::ADD_ACTION_TYPE
                );

                if ($commandModel) {
                    $this->trackerService->saveCommandForTracker($device, $currentUser, $commandModel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @param string $sensorId
     * @throws \Exception
     */
    public function removeDeviceWithNewTempAndHumiditySensorId(Device $device, User $currentUser, string $sensorId)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            if ($this->trackerCommandService->isEnabled()) {
                $commandModel = $this->trackerCommandService->getTemperatureAndHumidityCommand(
                    $sensorId,
                    $device,
                    TrackerCommandService::DELETE_ACTION_TYPE
                );

                if ($commandModel) {
                    $this->trackerService->saveCommandForTracker($device, $currentUser, $commandModel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @param string $sensorId
     * @throws \Exception
     */
    public function updateDeviceWithNewTrackingBeaconSensorId(Device $device, User $currentUser, string $sensorId)
    {
        $this->updateDeviceWithNewTempAndHumiditySensorId($device, $currentUser, $sensorId);
    }

    /**
     * @param Device $device
     * @param User $currentUser
     * @param string $sensorId
     * @throws \Exception
     */
    public function removeDeviceWithNewTrackingBeaconSensorId(Device $device, User $currentUser, string $sensorId)
    {
        $this->removeDeviceWithNewTempAndHumiditySensorId($device, $currentUser, $sensorId);
    }

    public function updateDeviceOdometer(Device $device, User $currentUser, ?int $value = null): void
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            if ($this->trackerCommandService->isEnabled()) {
                $commandModel = $this->trackerCommandService->getOdometerCommand(
                    $device,
                    TrackerCommandService::SET_ACTION_TYPE,
                    $value
                );

                if ($commandModel) {
                    $this->trackerService->saveCommandForTracker($device, $currentUser, $commandModel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     * @param Vehicle|null $vehicle
     * @param mixed|null $dateFrom
     * @param mixed|null $dateTo
     * @return void
     */
    public function clearDeviceOdometerCommands(Device $device, ?Vehicle $vehicle, $dateFrom = null, $dateTo = null)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            if ($this->trackerCommandService->isEnabled()) {
                $this->em->getRepository(TrackerCommandEntity::class)
                    ->removeDeviceOdometerCommandsByPeriod($device, $vehicle, $dateFrom, $dateTo);
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     * @return bool
     * @throws \Exception
     */
    public function isEnabled(Device $device)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            return $this->trackerCommandService->isEnabled();
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    /**
     * @param Device $device
     */
    public function getOdometerRecords(Device $device)
    {
        return $this->trackerCommandService->getOdometerRecords($device);
    }

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @return TrackerCommand[]|array|null
     */
    public function getOdometerRecordsByDeviceInstallation(Device $device, DeviceInstallation $deviceInstallation)
    {
        return $this->trackerCommandService->getOdometerRecords(
            $device,
            $deviceInstallation->getInstallDate(),
            $deviceInstallation->getUninstallDate()
        );
    }

    /**
     * @param Device $device
     * @param DeviceInstallation $deviceInstallation
     * @return TrackerCommand|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOdometerLastRecordByDeviceInstallation(
        Device $device,
        DeviceInstallation $deviceInstallation
    ): ?TrackerCommand {
        return $this->trackerCommandService->getOdometerLastRecord(
            $device,
            $deviceInstallation->getInstallDate(),
            $deviceInstallation->getUninstallDate()
        );
    }

    /**
     * @param Device $device
     * @return bool
     * @throws \Exception
     */
    public function wakeupDevice(Device $device)
    {
        try {
            $this->initTrackerServicesByVendorName($device);

            return $this->trackerCommandService->wakeupDevice();
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    public function topflytechRelayDevice(Device $device): void
    {
        try {
            $this->initTrackerServicesByVendorName($device);
            $commandModel = $this->trackerCommandService->getRelayCommand($device);

            if ($commandModel) {
                $this->trackerService->saveCommandForTracker($device, null, $commandModel);
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }

    public function triggerOverSpeedingAlarm(Device $device): void
    {
        try {
            $this->initTrackerServicesByVendorName($device);
            if ($this->trackerCommandService->isEnabled()) {
                $commandModel = $this->trackerCommandService->getOverSpeedingAlarmCommand(
                    $device,
                );
                if ($commandModel) {
                    $this->trackerService->saveCommandForTracker($device, null, $commandModel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
        }
    }
}
