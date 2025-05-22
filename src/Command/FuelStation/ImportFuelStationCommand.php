<?php

namespace App\Command\FuelStation;

use App\Command\Traits\RedisLockTrait;
use App\Entity\Area;
use App\Entity\AreaGroup;
use App\Entity\FuelStation;
use App\Entity\Team;
use App\Service\Area\AreaService;
use App\Service\Redis\MemoryDbService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

#[AsCommand(name: 'app:fuel:station')]
class ImportFuelStationCommand extends Command
{
    use RedisLockTrait;

    private $params;
    private $memoryDb;

    private const REGIONS = [
        1234 => 'ph-en',
        2719 => 'th-th',
        2720 => 'my-en',
        2722 => 'hk-en',
        2721 => 'sg-en'
    ];

    private const BATCH_SIZE = 50;

    public function __construct(
        ParameterBagInterface $params,
        MemoryDbService $memoryDbService,
        private readonly EntityManager $em,
        private readonly AreaService $areaService
    ) {
        $this->params = $params;
        $this->memoryDb = $memoryDbService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Import fuel station from Caltex');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $encoders = new XmlEncoder();

        try {
            $guzzleClient = new Client();
            foreach (self::REGIONS as $teamId => $REGION) {
                $fuelStations = [];
                $counter = 0;
                $team = $this->em->getRepository(Team::class)->find($teamId);

                if (!$team) {
                    continue;
                }

                $response = $guzzleClient->get(
                    'https://forms.caltex.com/stations/handler/GetSite.ashx',
                    [
                        'query' => ['c' => $REGION, 'm' => 'all']
                    ]
                );
                $data = $encoders->decode($response->getBody(), 'xml');
                foreach ($data['Marker'] as $item) {
                    if ($item['@Site_type'] !== 'Station') {
                        continue;
                    }

                    $fuelStation = $this->em->getRepository(FuelStation::class)->findOneBy([
                        'stationId' => $item['@StationID'],
                        'team' => $team
                    ]);
                    if (is_null($item['@Latitude']) || is_null($item['@Longitude'])
                        || !is_float($item['@Latitude']) || !is_float($item['@Longitude'])) {
                        continue;
                    }
                    if (!$fuelStation) {
                        $fuelStation = new FuelStation([]);
                        $this->em->persist($fuelStation);
                    }
                    $fuelStation->setStationId($item['@StationID']);
                    if ($item['@Site_ID']) {
                        $fuelStation->setSiteId($item['@Site_ID']);
                    }
                    $fuelStation->setAddress($this->makeAddress($item));
                    $fuelStation->setStationName($item['@StationName']);
                    $fuelStation->setLat($item['@Latitude']);
                    $fuelStation->setLng($item['@Longitude']);
                    $fuelStation->setTeam($team);

                    if (($counter % self::BATCH_SIZE) === 0) {
                        try {
                            $this->em->flush();
                        } catch (\Exception $exception) {
                            $output->writeln($exception->getMessage());
//                            $output->writeln(json_encode($item));
                        }

                    }
                    ++$counter;
                    $fuelStations[] = $fuelStation;
                }
                $this->em->flush();
                $this->em->clear();
                $output->writeln('teamId - ' . $team->getId());

                $clients = $this->em->getRepository(\App\Entity\Client::class)->findBy(['ownerTeam' => $team]);

                /** @var \App\Entity\Client $client */
                foreach ($clients as $client) {
                    $areaGroup = $this->em->getRepository(AreaGroup::class)->findOneBy([
                        'type' => AreaGroup::TYPE_FUEL_STATION,
                        'team' => $client->getTeam()
                    ]);

                    if (!$areaGroup) {
                        $areaGroup = new AreaGroup(['name' => AreaGroup::CHEVRON_DEFAULT_GROUP, 'color' => 'red']);
                        $areaGroup->setType(AreaGroup::TYPE_FUEL_STATION);
                        $areaGroup->setTeam($client->getTeam());
                        $this->em->persist($areaGroup);
                        $this->em->flush();
                    }

                    foreach ($fuelStations as $fuelStation) {
                        $area = $this->em->getRepository(Area::class)->findOneBy([
                            'type' => Area::TYPE_FUEL_STATION,
                            'externalId' => $fuelStation->getId(),
                            'team' => $client->getTeam()
                        ]);

                        if (!$area) {
                            $area = $this->areaService->createFuelStationArea($fuelStation, $client->getTeam());
                        }
                        $areaGroup->addArea($area);
                        $this->em->flush();
                    }
                }
            }

            //remove fuel areas without station
            $fuelAreas = $this->em->getRepository(Area::class)->findBy(['type' => Area::TYPE_FUEL_STATION]);
            foreach ($fuelAreas as $fuelArea) {
                if ($fuelArea->getExternalId()) {
                    $fuelStation = $this->em->getRepository(FuelStation::class)->find($fuelArea->getExternalId());
                    if (!$fuelStation) {
                        $this->em->remove($fuelArea);
                        $this->em->flush();
                    }
                }
            }

            $process = new Process(
                ['php', 'bin/console', 'fos:elastica:populate', '--index=fuelCard', '--no-reset']
            );
            $process->setTimeout(600);
            $process->setWorkingDirectory(getcwd());

            $process->run(function ($type, $buffer) use ($output) {
                if (Process::ERR === $type) {
                    $output->writeln('ERR > ' . $buffer);
                }
            });

        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
//            $output->writeln(json_encode($item));
//            $output->writeln($exception->getTraceAsString());
        }

        return 0;
    }

    private function makeAddress(array $station): string
    {
        $address = '';
        if ($station['@Street']) {
            $address .= $station['@Street'] . ' ';
        }
        if ($station['@City']) {
            $address .= $station['@City'] . ' ';
        }
        if ($station['@State']) {
            $address .= $station['@State'] . ' ';
        }

        return rtrim($address);
    }
}
