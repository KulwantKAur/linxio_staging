<?php

namespace App\Command\GenerateVehicle;

use App\Entity\Depot;
use App\Entity\Team;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Fixtures\VechilesGroup\InitVehiclesGroupFixture;
use App\Fixtures\VehiclesDepot\InitVehiclesDepotFixture;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate-vehicle')]
class GenerateVehicleCommand extends Command
{
    private $em;
    private $batch = 500;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    /**
     * Configuration of command
     */
    protected function configure(): void
    {
        $this->setDescription('Generate random vehicles for team');
        $this->addOption('teamId', null, InputOption::VALUE_REQUIRED, 'Team ID is required.');
        $this->addOption('numberVehicles', null, InputOption::VALUE_REQUIRED, 'Number of vehicles is required.');
    }

    /**
     * Execute command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $teamId = $input->getOption('teamId');
        $numberVehicles = $input->getOption('numberVehicles');

        /** @var Team $team */
        $team = $this->em->getRepository(Team::class)->find($teamId);
        if ($team ?? null) {
            $connection = $this->em->getConnection();
            try {
                $connection->beginTransaction();

                $depots = $this->getDepotForTeam($team);

                /* default data generate */
                $typeCar = Vehicle::CAR_TYPES;
                $model = [
                    'Toyota Hilux',
                    'Polaris',
                    'Polaries',
                    'Hino Truck',
                    'VW Caddy',
                    'Mazda 2',
                    'Hilux SR5',
                    'Holden Rodeo',
                    'Toyota Hiace',
                    'Hino',
                    'Toyota',
                    'VW Amarok',
                    'Toyota Bus',
                    'Volkswagon Amarok'
                ];
                $fuelType = ['Diesel', 'Petrol', 'Gas', 'Electric engine'];
                $emissionClass = ['Euro 1', 'Euro 2', 'Euro 3'];
                $status = Vehicle::ALLOWED_STATUSES;

                for ($counter = 0; $counter < $numberVehicles; $counter++) {
                    $data = [
                        'depot' => $depots[rand(0, (count($depots) - 1))],
                        'type' => $typeCar[array_rand($typeCar, 1)],
                        'make' => $model[array_rand($model, 1)],
                        'available' => true,
                        'regNo' => strtoupper(bin2hex(random_bytes(3))),
                        'enginePower' => number_format(mt_rand(0, mt_getrandmax()) / mt_getrandmax(), 2),
                        'engineCapacity' => number_format(mt_rand(0, mt_getrandmax()) / mt_getrandmax(), 2),
                        'fueltype' => $fuelType[array_rand($fuelType, 1)],
                        'emissionClass' => $emissionClass[array_rand($emissionClass, 1)],
                        'co2Emissions' => number_format(mt_rand(0, mt_getrandmax()) / mt_getrandmax(), 2),
                        'grossWeight' => number_format(mt_rand(0, mt_getrandmax()) / mt_getrandmax(), 2),
                        'status' => $status[array_rand($status, 1)],
                        'year' => mt_rand(2015, 2020),
                        'fuelTankCapacity' => mt_rand(60, 90),
                    ];

                    $vehicle = new Vehicle($data);
                    $vehicle->setDefaultLabel($data["regNo"]);
                    $vehicle->setRegDate(new \DateTime());
                    $vehicle->setTeam($team);
                    $this->em->persist($vehicle);

                    $this->getVehicleGroupForTeam($team);

                    if (($counter % $this->batch) === 0) {
                        $this->em->flush();
                        $this->em->clear();

                        $team = $this->em->getRepository(Team::class)->find($teamId);
                        $depots = $this->getDepotForTeam($team);
                    }
                }
                $this->em->flush();

                $output->writeln(sprintf('<comment>Created %s vehicles for the team: %s', $counter, $teamId));
                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
                $connection->rollback();
                throw $e;
            }
        } else {
            $output->writeln(sprintf('<comment>ID team: %s Not found! Check it please!', $teamId));
        }

        return 0;
    }

    /**
     * @param Team $team
     * @return array|object[]
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function getDepotForTeam(Team $team)
    {
        $depotList = $this->em->getRepository(Depot::class)->findBy(['team' => $team]);
        if (!$depotList) {
            foreach (InitVehiclesDepotFixture::VEHICLES_DEPOT as $depots) {
                $vehiclesDepot = new Depot($depots);
                $vehiclesDepot->setTeam($team);
                $this->em->persist($vehiclesDepot);
            }
            $this->em->flush();
            $depotList = $this->em->getRepository(Depot::class)->findBy(['team' => $team]);
        }

        return $depotList;
    }

    /**
     * @param Team $team
     * @return array|object[]
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function getVehicleGroupForTeam(Team $team)
    {
        $vehiclesGroup = $this->em->getRepository(VehicleGroup::class)->findBy(['team' => $team]);
        if (!$vehiclesGroup) {
            $vehicles = $this->em->getRepository(Vehicle::class)->findBy(['team' => $team], [], 10);
            foreach (InitVehiclesGroupFixture::VEHICLES_GROUP as $group) {
                $group = new VehicleGroup($group);
                $group->setTeam($team);
                foreach ($vehicles as $vechile) {
                    $group->addVehicle($vechile);
                    $this->em->persist($group);
                }
            }
            $this->em->flush();
        }

        return $vehiclesGroup;
    }
}