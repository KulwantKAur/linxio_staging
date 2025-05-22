<?php

namespace App\Fixtures\Vehicles;

use App\Entity\Depot;
use App\Entity\FuelType\FuelType;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use App\Fixtures\VehiclesDepot\InitVehiclesDepotFixture;
use App\Fixtures\FuelType\IntFuelTypeFixture;
use App\Fixtures\VehicleTypes\InitVehicleTypesFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitVehiclesFixture extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitDemoUsersFixtures::class,
            InitTeamsFixture::class,
            InitVehiclesDepotFixture::class,
            IntFuelTypeFixture::class,
            InitVehicleTypesFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    public const VEHICLES = [
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[0]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hilux',
            'available' => true,
            'defaultLabel' => 'Jeff',
            'regNo' => '015RSJ',
        ],
        [
            'type' => 'Car',
            'model' => 'Polaris',
            'available' => true,
            'defaultLabel' => 'Janey',
            'regNo' => '1102301317',
            'year' => '2019',
        ],
        [
            'type' => 'Car',
            'model' => 'Polaris',
            'available' => true,
            'defaultLabel' => 'Jackson',
            'regNo' => '1102301826',
            'year' => '2018',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[0]['name'],
            'type' => 'Truck',
            'model' => 'Hino Truck',
            'available' => true,
            'defaultLabel' => 'Truck',
            'regNo' => '132XRZ',
            'fuelTankCapacity' => 0,
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Spare Tracker not Used',
            'regNo' => '202HBP',
            'fuelType' => IntFuelTypeFixture::FUEL_TYPE[1]['name'],
            'fuelTankCapacity' => 70,
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[1]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Matt',
            'regNo' => '221SVP',
            'fuelType' => IntFuelTypeFixture::FUEL_TYPE[0]['name'],
            'fuelTankCapacity' => 70,
        ],
        [
            'type' => 'Car',
            'model' => 'VW Caddy',
            'available' => true,
            'defaultLabel' => 'Cameron',
            'regNo' => '693RXD'
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[1]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Ben',
            'regNo' => '711WHZ'
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hilux',
            'available' => true,
            'defaultLabel' => 'Andy',
            'regNo' => '759SIA'
        ],
        [
            'type' => 'Car',
            'model' => 'Mazda 2',
            'available' => true,
            'defaultLabel' => 'Bob',
            'regNo' => '861107034118555',
            'vin' => '281JHU'
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[0]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Nikki',
            'regNo' => '867XFH',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[0],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Hilux SR5',
            'available' => true,
            'defaultLabel' => 'GB Lux',
            'regNo' => '868323026527959',
            'vin' => 'MR0HA3CD400407949',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[1]['name'],
            'type' => 'Car',
            'model' => 'VW Caddy',
            'available' => true,
            'defaultLabel' => 'Rhonda',
            'regNo' => '871WIL',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[0]['name'],
            'type' => 'Car',
            'model' => 'Holden Rodeo',
            'available' => false,
            'defaultLabel' => 'Grow Centre Ute',
            'regNo' => '899KJZ',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[0]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hilux',
            'available' => false,
            'defaultLabel' => 'Spare Hilux',
            'regNo' => '903MTO',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Yasu',
            'regNo' => 'AI35WR',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[0]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Sue',
            'regNo' => 'BC42VO',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Guy',
            'regNo' => 'BE91HK',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Dennis P',
            'regNo' => 'BI38NL',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[1],
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Andrew',
            'regNo' => 'BI78NM',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Ian',
            'regNo' => 'BJ02FC',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[2],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Josh EG',
            'regNo' => 'BJ78WS',
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Robert',
            'regNo' => 'BKP51N',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[3],
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Celeste',
            'regNo' => 'BPF61Q',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[4],
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Dave',
            'regNo' => 'BQ98QG',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[5],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Hino',
            'available' => true,
            'defaultLabel' => 'Truck',
            'regNo' => 'BS03MJ',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Tim',
            'regNo' => 'BV33PR',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[6],
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hilux',
            'available' => true,
            'defaultLabel' => 'Wayne_Nursery',
            'regNo' => 'BY95GQ',
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Spare 2',
            'regNo' => 'CA42NU',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota',
            'available' => true,
            'defaultLabel' => 'Graeme',
            'regNo' => 'CC40EU',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[7],
        ],
        [
            'type' => 'Car',
            'model' => 'VW Amarok',
            'available' => true,
            'defaultLabel' => 'Mark Ute',
            'regNo' => 'CIF30J',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[1]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'HiTop Van',
            'regNo' => 'CJ12CW',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[1]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Spare Van',
            'regNo' => 'CMZ60M',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Bus',
            'available' => true,
            'defaultLabel' => 'Spare',
            'regNo' => 'CSI51A',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Narelle',
            'regNo' => 'DAS24T',
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Sue',
            'regNo' => 'DCA74W',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[8],
        ],
        [
            'type' => 'Car',
            'model' => 'VW Amarok',
            'available' => true,
            'defaultLabel' => 'Gerard',
            'regNo' => 'DCC29X',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[9],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Narrelle',
            'regNo' => 'DDM84W',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[10],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Sharon',
            'regNo' => 'DDW62T',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[11],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Lyndon',
            'regNo' => 'DHZ54M',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[12],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Volkswagon Amarok',
            'available' => true,
            'defaultLabel' => 'Shane',
            'regNo' => 'DNE23B',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[13],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Mark Van',
            'regNo' => 'DTM84S',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[14],
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hillux',
            'available' => true,
            'defaultLabel' => 'Alan',
            'regNo' => 'EAF81R',
            'driver' => InitDemoUsersFixtures::DRIVER_USERS[15],
        ],
        [
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Jesse',
            'regNo' => 'EBC06M',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Tony',
            'regNo' => 'EBZ51C',
        ],
        [
            'depot' => InitVehiclesDepotFixture::VEHICLES_DEPOT[2]['name'],
            'type' => 'Car',
            'model' => 'Toyota Hiace',
            'available' => true,
            'defaultLabel' => 'Bomb',
            'regNo' => 'ZJR580',
        ],
    ];

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);
        $clientTeam = $manager->getRepository(Team::class)->findOneBy(
            ['type' => Team::TEAM_CLIENT],
            ['id' => 'DESC']
        );
        foreach (self::VEHICLES as $vehicle) {
            $type = $manager->getRepository(VehicleType::class)->findOneBy(['name' => $vehicle['type']]);
            if (!$type) {
                continue;
            }
            unset($vehicle['type']);
            $newVehicle = $manager->getRepository(Vehicle::class)->findOneBy(
                [
                    'type' => $type,
                    'regNo' => $vehicle['regNo']
                ]
            );
            if (!$newVehicle) {
                $driver = ($vehicle['driver'] ?? null)
                    ? $manager->getRepository(User::class)->findOneBy(
                        ['name' => $vehicle['driver']['name'], 'surname' => $vehicle['driver']['surname']]
                    )
                    : null;

                $fuelType = ($vehicle['fuelType'] ?? null)
                    ? $manager->getRepository(FuelType::class)->findOneBy(['name' => $vehicle['fuelType']])
                    : null;

                $newVehicle = new Vehicle($vehicle);
                $newVehicle->setDriver($driver);
                $newVehicle->setFuelType($fuelType);
                $newVehicle->setType($type);

                if (!empty($vehicle['depot'])) {
                    $depotEntity = $manager->getRepository(Depot::class)->findOneBy(
                        [
                            'name' => $vehicle['depot']
                        ]
                    );
                    $newVehicle->setDepot($depotEntity);
                }
                $newVehicle->setTeam($clientTeam);
                $manager->persist($newVehicle);
            } else {
                $fuelType = ($vehicle['fuelType'] ?? null)
                    ? $manager->getRepository(FuelType::class)->findOneBy(['name' => $vehicle['fuelType']])
                    : null;
                $fuelTankCapacity = ($vehicle['fuelTankCapacity'] ?? null)
                    ? $vehicle['fuelTankCapacity']
                    : null;

                $newVehicle->setFuelType($fuelType);
                $newVehicle->setFuelTankCapacity($fuelTankCapacity);
                $newVehicle->setType($type);
            }
            $this->setReference($vehicle['regNo'], $newVehicle);
        }
        $manager->flush();
    }
}
