<?php

namespace App\Command;

use App\Command\Traits\CommandLoggerTrait;
use App\Entity\Client;
use App\Entity\Integration;
use App\Entity\IntegrationData;
use App\Entity\Route;
use App\Entity\Setting;
use App\Entity\Tracker\TrackerHistory;
use App\Entity\Vehicle;
use App\EntityManager\SlaveEntityManager;
use App\Service\Integration\IntegrationService;
use App\Service\Redis\MemoryDbService;
use App\Util\AwsS3ForPrism;
use App\Util\FileHelper;
use App\Util\StringHelper;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:export-vehicle-data')]
class ExportVehicleData extends Command
{
    use CommandLoggerTrait;

    private const INTERVAL = 'P1DT12H';

    protected function configure(): void
    {
        $this->setDescription('Prism export vehicle data');
        $this->addOption('teamId', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('interval', null, InputOption::VALUE_OPTIONAL);
        $this->addOption('dateFrom', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('dateTo', null, InputOption::VALUE_OPTIONAL, '');
        $this->addOption('ignoreLastTHId', null, InputOption::VALUE_OPTIONAL, 'ignoreLastTHId', false);
    }

    public function __construct(
        private readonly AwsS3ForPrism $s3,
        private readonly SlaveEntityManager $emSlave,
        private readonly IntegrationService $integrationService,
        private readonly EntityManager $em,
        private readonly MemoryDbService $memoryDbService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $teamIdParam = $input->getOption('teamId');
        $interval = $input->getOption('interval') ?? self::INTERVAL;
        $dateFromOption = $input->getOption('dateFrom') ? Carbon::parse($input->getOption('dateFrom')) : null;
        $dateFrom = $dateFromOption ?: (new \DateTime())->sub(new \DateInterval($interval));
        $dateTo = $input->getOption('dateTo') ? Carbon::parse($input->getOption('dateTo')) : (new \DateTime());
        $ignoreLastTHId = boolval($input->getOption('ignoreLastTHId'));

        if (!$teamIdParam) {
            $integration = $this->emSlave->getRepository(Integration::class)->findOneBy(['name' => Integration::PRISM]);
            $teamIds = $this->emSlave->getRepository(Setting::class)->getTeamIdsWithIntegration($integration->getId());
        } else {
            $teamIds = [$teamIdParam];
        }

        foreach ($teamIds as $teamId) {
            try {
                if ($this->uploadFiles($teamId, $dateFrom, $dateTo, $ignoreLastTHId)) {
                    $output->writeln('TeamId uploaded: ' . $teamId);
                }
            } catch (\Exception $exception) {
                $this->logException($exception, ['teamId' => $teamId]);
                $output->writeln($exception->getMessage());
                $output->writeln($exception->getTraceAsString());
            }
        }

        return 0;
    }

    private function uploadFiles(int $teamId, \DateTime $dateFrom, \DateTime $dateTo, bool $ignoreLastTHId): bool
    {
        $lastThId = $this->memoryDbService->get('prism_team_' . $teamId);
        $newLastThId = null;
        $integration = $this->emSlave->getRepository(Integration::class)->findOneBy(['name' => Integration::PRISM]);
        $clientName = $this->emSlave->getRepository(Client::class)->getClientNameByTeamId($teamId);
        $clientName = StringHelper::replaceSpecialChars($clientName);
        $localPath = 'export/' . $teamId . '/';
        $awsTeamPath = $clientName . ' team ' . $teamId . '/';
        $awsVehiclesPath = $awsTeamPath . 'vehicles/';
        $awsGpsPath = $awsTeamPath . 'gps/';
        $integrationData = $this->emSlave->getRepository(IntegrationData::class)
            ->findByTeamIdAndIntegration($teamId, $integration);

        if (!$integrationData || !$integrationData->isEnabled()) {
            return false;
        }

        $vehicles = $this->integrationService->getScopeEntityIds($integrationData->getScope());
        $dateTime = (new \DateTime())->format('YmdHis');

        if (count($vehicles)) {
            $vehicles = $this->makeExportArray($vehicles);
            $this->s3->putFolder($awsTeamPath);
            $this->s3->putFolder($awsVehiclesPath);
            $this->s3->putFolder($awsGpsPath);
            $filename = 'vehicles_team_' . $teamId . ' ' . $dateTime . '.csv';
            $csv = FileHelper::createCsvFile($localPath, $filename, array_values($vehicles), false);
            $this->s3->putObject($localPath . $filename, $awsVehiclesPath . $filename);

            $vehicleIds = array_column($vehicles, 'client_vehicle_id');

            $zipPath = 'export/' . $teamId . '/gps.zip';
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE);

            foreach ($vehicleIds as $vehicleId) {
                $trackerHistories = [];
                $q = $this->emSlave->getRepository(TrackerHistory::class)->getTrackerHistoryByVehicleQuery(
                    [$vehicleId],
                    $dateFrom,
                    $dateTo
                );
                $iterableResult = $q->iterate();

                foreach ($iterableResult as $row) {
                    $item = array_shift($row);
                    if ($lastThId && ($item['id'] <= $lastThId) && !$ignoreLastTHId) {
                        continue;
                    } else {
                        $newLastThId = $newLastThId < $item['id'] ? $item['id'] : $newLastThId;
                    }
                    unset($item['id']);

                    $item['timestamp'] = $item['timestamp']->format('c');
                    $item['ignition_on'] = $item['ignition_on'] ? 'true' : 'false';
                    $item['odometer_km'] = (int)($item['odometer_km'] / 1000);
                    $item['trip_id'] = $this->em->getRepository(Route::class)
                        ->getRouteIdByDeviceIdAndTs($item['device_id'], $item['timestamp']);
                    $trackerHistories[] = $item;
                }
                if (count($trackerHistories)) {
                    $filename = 'vehicle_' . $vehicleId . '.csv';
                    $csv = FileHelper::createCsvFile($localPath, $filename, $trackerHistories, false);
                    $zip->addFile($localPath . '/' . $filename, 'vehicle_' . $vehicleId . '.csv');
                }
            }
            $zip->close();
            if (file_exists($zipPath)) {
                $result = $this->s3->putObject($zipPath, $awsGpsPath . 'gps ' . $dateTime . '.zip');
            }

            $integrationData->setLastUpdatedAt(new \DateTime());
            $this->em->flush();
        }
        FileHelper::deletePath($localPath);

        if ($newLastThId) {
            $this->memoryDbService->set('prism_team_' . $teamId, $newLastThId);
        }

        return (bool)($result ?? null);
    }

    private function makeExportArray(array $vehicles)
    {
        return array_map(function (Vehicle $vehicle) {
            return [
                'client_vehicle_id' => $vehicle->getId(),
                'fuel_type' => $vehicle->getFuelType() ? $vehicle->getFuelType()->getName() : null,
                'registration' => $vehicle->getRegNo(),
                'vin' => $vehicle->getVin(),
                'make' => $vehicle->getMake(),
                'makeModel' => $vehicle->getMakeModel(),
            ];
        }, $vehicles);
    }
}