<?php

namespace App\Fixtures\Teams;


use App\Entity\Team;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use App\Fixtures\Themes\InitThemesFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InitTeamsFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const ADMIN_TEAM_REFERENCE_ALIAS = 'TEAM_1';
    public const ADMIN_TEAM = ['id' => 1, 'type' => 'admin'];

    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function getDependencies(): array
    {
        return [
            InitThemesFixture::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $manager = $this->prepareEntityManager($manager);
        $adminTeam = $manager->getRepository(Team::class)->findOneBy([
            'type' => Team::TEAM_ADMIN
        ]);
        if (!$adminTeam) {
            $adminTeam = new Team(self::ADMIN_TEAM);
            $manager->persist($adminTeam);
            $manager->flush();
        }
        $this->setReference(self::ADMIN_TEAM_REFERENCE_ALIAS, $adminTeam);
    }

}