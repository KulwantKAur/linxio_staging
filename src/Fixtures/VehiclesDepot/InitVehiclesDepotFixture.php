<?php

namespace App\Fixtures\VehiclesDepot;

use App\Entity\Depot;
use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitVehiclesDepotFixture extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            InitPermissionsFixture::class,
            InitRolesFixture::class,
            InitDemoUsersFixtures::class,
            InitTeamsFixture::class
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    const VEHICLES_DEPOT = [
        ['name' => 'Frenchams QLD'],
        ['name' => 'Frenchams TP'],
        ['name' => 'Frenchams NSW'],
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
        foreach (self::VEHICLES_DEPOT as $depotData) {
            $depot = $manager->getRepository(Depot::class)->findOneBy([
                'name' => $depotData['name']
            ]);
            if (!$depot) {
                $vehiclesDepot = new Depot($depotData);
                $vehiclesDepot->setTeam($clientTeam);
                $manager->persist($vehiclesDepot);
            }
        }
        $manager->flush();
    }
}
