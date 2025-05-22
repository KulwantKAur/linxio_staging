<?php

namespace App\Fixtures\VechilesGroup;

use App\Entity\Team;
use App\Entity\Vehicle;
use App\Entity\VehicleGroup;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use App\Fixtures\Vehicles\InitVehiclesFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitVehiclesGroupFixture extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitDemoUsersFixtures::class,
            InitTeamsFixture::class,
            InitVehiclesFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    const VEHICLES_GROUP = [
        ['name' => 'VIP'],
        ['name' => 'QLD Frenchams'],
        ['name' => 'NSW Frenchams'],
        ['name' => 'TransPlant'],
    ];

    const VEHICLES_GROUPS = [
        self::VEHICLES_GROUP[1]['name'] => [
            InitVehiclesFixture::VEHICLES[0]['regNo'],
            InitVehiclesFixture::VEHICLES[6]['regNo'],
            InitVehiclesFixture::VEHICLES[8]['regNo'],
            InitVehiclesFixture::VEHICLES[9]['regNo'],
            InitVehiclesFixture::VEHICLES[10]['regNo'],
            InitVehiclesFixture::VEHICLES[14]['regNo'],
            InitVehiclesFixture::VEHICLES[16]['regNo'],
            InitVehiclesFixture::VEHICLES[40]['regNo'],
        ],
        self::VEHICLES_GROUP[2]['name'] => [
            InitVehiclesFixture::VEHICLES[11]['regNo'],
            InitVehiclesFixture::VEHICLES[15]['regNo'],
            InitVehiclesFixture::VEHICLES[17]['regNo'],
            InitVehiclesFixture::VEHICLES[18]['regNo'],
            InitVehiclesFixture::VEHICLES[19]['regNo'],
            InitVehiclesFixture::VEHICLES[20]['regNo'],
            InitVehiclesFixture::VEHICLES[21]['regNo'],
            InitVehiclesFixture::VEHICLES[22]['regNo'],
            InitVehiclesFixture::VEHICLES[23]['regNo'],
            InitVehiclesFixture::VEHICLES[24]['regNo'],
            InitVehiclesFixture::VEHICLES[25]['regNo'],
            InitVehiclesFixture::VEHICLES[26]['regNo'],
            InitVehiclesFixture::VEHICLES[28]['regNo'],
            InitVehiclesFixture::VEHICLES[29]['regNo'],
            InitVehiclesFixture::VEHICLES[30]['regNo'],
            InitVehiclesFixture::VEHICLES[33]['regNo'],
            InitVehiclesFixture::VEHICLES[34]['regNo'],
            InitVehiclesFixture::VEHICLES[35]['regNo'],
            InitVehiclesFixture::VEHICLES[36]['regNo'],
            InitVehiclesFixture::VEHICLES[37]['regNo'],
            InitVehiclesFixture::VEHICLES[38]['regNo'],
            InitVehiclesFixture::VEHICLES[39]['regNo'],
            InitVehiclesFixture::VEHICLES[41]['regNo'],
            InitVehiclesFixture::VEHICLES[42]['regNo'],
            InitVehiclesFixture::VEHICLES[45]['regNo'],
        ],
        self::VEHICLES_GROUP[3]['name'] => [
            InitVehiclesFixture::VEHICLES[3]['regNo'],
            InitVehiclesFixture::VEHICLES[5]['regNo'],
            InitVehiclesFixture::VEHICLES[7]['regNo'],
            InitVehiclesFixture::VEHICLES[8]['regNo'],
            InitVehiclesFixture::VEHICLES[12]['regNo'],
            InitVehiclesFixture::VEHICLES[31]['regNo'],
            InitVehiclesFixture::VEHICLES[32]['regNo'],
        ],
    ];

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $clientTeam = $manager->getRepository(Team::class)->findOneBy(
            ['type' => Team::TEAM_CLIENT],
            ['id' => 'DESC']
        );
        foreach (self::VEHICLES_GROUP as $groups) {
            $vehicleGroup = $manager->getRepository(VehicleGroup::class)->findOneBy([
                'name' => $groups['name'],
            ]);
            if (!$vehicleGroup) {
                $vehiclesGroup = new VehicleGroup($groups);
                $vehiclesGroup->setTeam($clientTeam);
                if (isset(self::VEHICLES_GROUPS[$groups['name']])) {
                    foreach (self::VEHICLES_GROUPS[$groups['name']] as $vechileGroups) {
                        $vechilesEntity = $manager->getRepository(Vehicle::class)->findOneBy([
                            'regNo' => $vechileGroups
                        ]);
                        $vehiclesGroup->addVehicle($vechilesEntity);
                        $manager->persist($vehiclesGroup);
                    }
                }
            }
        }
        $manager->flush();
    }
}
