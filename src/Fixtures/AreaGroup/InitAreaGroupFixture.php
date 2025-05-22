<?php

namespace App\Fixtures\AreaGroup;

use App\Entity\AreaGroup;
use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Permissions\InitPermissionsFixture;
use App\Fixtures\Roles\InitRolesFixture;
use App\Fixtures\Teams\InitTeamsFixture;
use App\Fixtures\Users\InitDemoUsersFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitAreaGroupFixture extends BaseFixture implements DependentFixtureInterface
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

    public const AREA_GROUPS = [
        ['name' => 'Asia Geo Group'],
        ['name' => 'Europe Geo Group']
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
        foreach (self::AREA_GROUPS as $groups) {
            $areaGroup = $manager->getRepository(AreaGroup::class)->findOneBy([
                'name' => $groups['name'],
            ]);
            if (!$areaGroup) {
                $areaGroup = new AreaGroup($groups);
                $areaGroup->setTeam($clientTeam);
                $manager->persist($areaGroup);
            }
        }
        $manager->flush();
    }
}
