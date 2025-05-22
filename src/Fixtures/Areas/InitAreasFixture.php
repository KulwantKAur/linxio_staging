<?php

namespace App\Fixtures\Areas;

use App\Entity\Area;
use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use App\Service\Area\AreaService;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitAreasFixture extends BaseFixture implements DependentFixtureInterface
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

    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    const AREAS = [
        [
            'name' => 'Asia Geozone',
            "coordinates" => [
                [
                    "lat" => 58.87513407467052,
                    "lng" => 77.49618087499994
                ],
                [
                    "lat" => 49.45956321199721,
                    "lng" => 111.94930587499994
                ],
                [
                    "lat" => 45.66627785218868,
                    "lng" => 75.38680587499994
                ],
                [
                    "lat" => 58.87513407467052,
                    "lng" => 77.49618087499994
                ]
            ],
        ],
        [
            'name' => 'Europe Geozone',
            "coordinates" => [
                [
                    "lat" => 58.87513407467052,
                    "lng" => 77.49618087499994
                ],
                [
                    "lat" => 49.45956321199721,
                    "lng" => 111.94930587499994
                ],
                [
                    "lat" => 45.66627785218868,
                    "lng" => 75.38680587499994
                ],
                [
                    "lat" => 58.87513407467052,
                    "lng" => 77.49618087499994
                ]
            ],
        ]
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
        foreach (self::AREAS as $areaData) {
            $area = $manager->getRepository(Area::class)->findOneBy([
                'name' => $areaData['name'],
            ]);
            if (!$area) {
                $area = new Area($areaData);
                $area->setTeam($clientTeam);
                $area->setPolygon(AreaService::convertCoordinates($areaData['coordinates']));
                $manager->persist($area);
            }
        }
        $manager->flush();
    }
}
