<?php

namespace App\Fixtures\Tracker;

use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class InitSimulatorTracksFixture extends BaseFixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return [FixturesTypes::TESTING];
    }

    /**
     * @param ObjectManager $manager
     * @throws \Doctrine\DBAL\DBALException
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);
        $sqlContent = file_get_contents(__DIR__ . '/sql/simulator_tracks_data.sql');
        $manager->getConnection()->exec($sqlContent);
    }
}