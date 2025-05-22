<?php

namespace App\Command;

use App\Command\Traits\TeamTrait;
use App\Entity\Device;
use App\Entity\DrivingBehavior;
use App\Entity\Route;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Tracker\TrackerHistoryLast;
use App\Service\Tracker\Parser\Topflytech\Data;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Command\Traits\DevicebleTrait;

#[AsCommand(name: 'app:device:update-odometer')]
class UpdateDeviceOdometerHistoryCommand extends Command
{
    use DevicebleTrait;
    use TeamTrait;

    private const BATCH_SIZE = 50;

    private EntityManager $em;

    /**
     * @param Device $device
     * @param int $odometer
     * @param $dateFrom
     * @param mixed $dateTo
     */
    private function updateOdometerInRelatedData(
        Device $device,
        int $odometer,
        $dateFrom,
        $dateTo
    ) {
        $resultRoutes = $this->em->getRepository(Route::class)->updateRoutesDistanceAndOdometer(
            $device,
            null,
            $odometer,
            $dateFrom,
            $dateTo
        );
        $resultDrivBeh = $this->em->getRepository(DrivingBehavior::class)
            ->updateOdometerByRangeAndDevice($device, null, $odometer, $dateFrom, $dateTo);
    }

    protected function configure(): void
    {
        $this->setDescription('Update device odometer history with wrong started values');
        $this->updateConfigWithDeviceOptions();
        $this->updateConfigWithTeamOptions();
    }

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        $teamIds = $this->getTeamIdsByInput($input);
        $deviceIds = $this->getDeviceIdsByInput($input)
            ?: $this->em->getRepository(TrackerHistoryLast::class)->getDeviceIdsForCalculations();
        $devicesQuery = $this->em->getRepository(Device::class)->getDevicesQuery($deviceIds, $teamIds);
        $devicesCount = $this->em->getRepository(Device::class)->getDevicesCount($deviceIds, $teamIds);
        $progressBar = new ProgressBar($output, $devicesCount);
        $progressBar->start();

        /** @var Device $device */
        foreach ($devicesQuery->toIterable() as $device) {
            try {
                $output->writeln('Device ID: ' . $device->getId());
                $firstTrackerRecordWithMinOdo = $this->em->getRepository(TrackerHistory::class)
                    ->getFirstRecordWithMinOdometerByDevice($device);

                if ($firstTrackerRecordWithMinOdo) {
                    $firstCorrectOdometer = $firstTrackerRecordWithMinOdo['odometer'];
                    $prevTrackerRecord = $this->em->getRepository(TrackerHistory::class)
                        ->getPrevTrackerHistoryWithOdometer($device, $firstTrackerRecordWithMinOdo['ts']);

                    if ($prevTrackerRecord) {
                        $lastWrongOdometer = $prevTrackerRecord['odometer'];
                        $firstTsWithWrongOdometer = $prevTrackerRecord['ts'];
                        $lastTsWithWrongOdometer = $prevTrackerRecord['ts'];

                        if ($lastWrongOdometer > Data::ODOMETER_LIMIT_MAX) {
                            $firstTrackerRecordWithMaxWrongOdo = $this->em->getRepository(TrackerHistory::class)
                                ->getFirstTrackerRecordForOdometerUpdateByDevice($device, $prevTrackerRecord['ts']);

                            if ($firstTrackerRecordWithMaxWrongOdo) {
                                $firstTsWithWrongOdometer = $firstTrackerRecordWithMaxWrongOdo['ts'];
                            }

                            $result = $this->em->getRepository(TrackerHistory::class)
                                ->updateTrackerHistoryOdometerByRange(
                                    $device,
                                    $firstCorrectOdometer,
                                    $firstTsWithWrongOdometer,
                                    $lastTsWithWrongOdometer
                                );

                            $this->updateOdometerInRelatedData(
                                $device,
                                $firstCorrectOdometer,
                                $firstTsWithWrongOdometer,
                                $lastTsWithWrongOdometer
                            );
                        }
                    }
                }

                if (($counter % self::BATCH_SIZE) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }

                ++$counter;
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln('Device: ' . $device->getId());
                $output->writeln($e->getTraceAsString());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->em->flush();
        $this->em->clear();
        $output->writeln(PHP_EOL . 'Data successfully updated!');

        return 0;
    }
}