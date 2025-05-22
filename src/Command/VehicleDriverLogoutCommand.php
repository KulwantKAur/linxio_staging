<?php

namespace App\Command;

use App\Command\Traits\ProcessableTrait;
use App\Command\Traits\RedisLockTrait;
use App\Entity\Notification\Message;
use App\Entity\Notification\NotificationMobileDevice;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\Firebase\FCMService;
use App\Service\Redis\MemoryDbService;
use App\Service\Redis\Models\VehicleRedisModel;
use App\Service\Setting\SettingService;
use App\Service\Vehicle\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:vehicle:driver-logout')]
class VehicleDriverLogoutCommand extends Command
{
    use RedisLockTrait;
    use ProcessableTrait;

    private const BATCH_SIZE = 50;

    /**
     * @param User $user
     * @return void
     */
    private function sendLogoutNotification(User $user)
    {
        /** @var NotificationMobileDevice $devices */
        $device = $this->em->getRepository(NotificationMobileDevice::class)
            ->getLastLoggedDeviceByUserBy($user->getId());

        if ($device) {
            $title = 'User logout';
            $body = '';
            $additionalData = [
                'type' => Message::PUSH_TYPE_USER_LOGOUT,
                'userId' => $user->getId(),
            ];
            $this->fcmService->sendNotification(
                $device,
                $title,
                $body,
                $additionalData
            );
        }
    }

    protected function configure(): void
    {
        $this->setDescription('Logout drivers from vehicles when engine is off');
        $this->updateConfigWithProcessOptions();
    }

    /**
     * @param EntityManagerInterface $em
     * @param SettingService $settingService
     * @param VehicleService $vehicleService
     * @param MemoryDbService $memoryDbService
     * @param FCMService $fcmService
     * @param ParameterBagInterface $params
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService,
        private readonly VehicleService $vehicleService,
        private readonly MemoryDbService $memoryDbService,
        private readonly FCMService $fcmService,
        private readonly ParameterBagInterface $params
    ) {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->getLock($this->getProcessName($input));

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

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
            $driverAutoLogoutByVehicleSetting = $this->settingService
                ->getTeamSettingValueByKey($team, Setting::DRIVER_AUTO_LOGOUT_BY_VEHICLE);

            if (!$driverAutoLogoutByVehicleSetting || !$driverAutoLogoutByVehicleSetting['enable']) {
                $progressBar->advance();
                continue;
            }

            $vehiclesQuery = $this->em->getRepository(Vehicle::class)
                ->getVehiclesForDriverAutoLogoutQuery($team, $deviceIds);

            /** @var Vehicle $vehicle */
            foreach ($vehiclesQuery->toIterable() as $vehicle) {
                try {
                    $cacheKey = VehicleRedisModel::getEngineHistoryKey($vehicle->getId());
                    $cacheData = $this->memoryDbService->getFromJson($cacheKey);
                    $driver = $vehicle->getDriver();

                    if (!$cacheData || !$driver) {
                        continue;
                    }

                    $engineOffDuration = $cacheData['duration'];
//                    $isTriggerred = $cacheData['isTriggerred'] ?? false;

                    if ($engineOffDuration > $driverAutoLogoutByVehicleSetting['value']
//                        && !$isTriggerred
                    ) {
                        $output->writeln('Logout driver ID: ' . $driver->getId());
                        $this->sendLogoutNotification($driver);
                        $cacheData['engineOffStartedTs'] = null;
                        $cacheData['engineOffFinishedTs'] = null;
                        $cacheData['duration'] = 0;
//                        $cacheData['isTriggerred'] = true;
                        $this->memoryDbService->setToJson($cacheKey, $cacheData);
                    }

                    if (($counter % self::BATCH_SIZE) === 0) {
                        $this->em->flush();
                        $this->em->clear();
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
        $output->writeln(PHP_EOL . 'Auto logout drivers from vehicles successfully updated!');
        $this->em->flush();
        $this->em->clear();

        return 0;
    }
}